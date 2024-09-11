<?php

namespace App\Http\Controllers;

use App\DTO\GetProductsByWarehouseDTO;
use App\Enums\ProductType;
use App\Exports\StockExport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\CountStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\Unit;
use App\Models\UserWarehouse;
use App\Models\Warehouse;
use App\Services\products\actions\CreateProductAction;
use App\Services\products\actions\DeleteProductAction;
use App\Services\products\actions\UpdateProductAction;
use App\Services\products\GetProductDetail;
use App\Services\products\GetProductsByWarehouse;
use App\utils\Helper;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

class ProductsController extends BaseController
{

    //------------ Get ALL Products --------------\\

    public function index(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'view', Product::class);
        $perPage = $request->get('limit', 15);
        $pageStart = $request->get('page', 1);
        $order = $request->get('SortField', 'id');
        $dir = $request->get('SortType', 'desc') !== 'none' ? $request->get('SortType', 'desc') : 'desc';

        $offSet = ($pageStart * $perPage) - $perPage;
        $helpers = new Helper();
        $data = [];

        $products = Product::with('unit', 'category', 'brand', 'variants')
            ->search($request->get('search'));

        $filtered = $helpers->filter(
            $products,
            ['name', 'category_id', 'brand_id', 'code'],
            ['like', '=', '=', 'like'],
            $request
        );

        $totalRows = $filtered->count();

        if ($perPage == "-1") {
            $perPage = $totalRows;
        }
        $products = $filtered->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        foreach ($products as $product) {
            $item['id'] = $product->id;
            $item['code'] = $product->code;
            $item['category'] = $product['category']->name;
            $item['brand'] = $product['brand'] ? $product['brand']->name : 'N/D';

            $firstImage = explode(',', $product->image);
            $item['image'] = $firstImage[0];

            if ($product->type == 'is_single') {
                $item['type'] = 'Single';
                $item['name'] = $product->name;
                $item['cost'] = $product->cost;
                $item['price'] = $product->price;
                $item['unit'] = $product['unit']->short_name;

                $product_warehouse_total_qty =
                    ProductWarehouse::where('product_id', $product->id)
                        ->sum('qty');

                $item['qty'] = $product_warehouse_total_qty . ' ' . $product['unit']->short_name;

            } elseif ($product->type == 'is_variant') {

                $item['type'] = 'Variable';
                $product_variant_data = $product->variants;
                $item['cost'] = '';
                $item['price'] = '';
                $item['name'] = '';
                $item['unit'] = $product['unit']->short_name;

                foreach ($product_variant_data as $product_variant) {
                    $item['cost'] .= number_format($product_variant->cost, 2, '.', ',');
                    $item['cost'] .= '<br>';
                    $item['price'] .= number_format($product_variant->price, 2, '.', ',');
                    $item['price'] .= '<br>';
                    $item['name'] .= $product_variant->name . '-' . $product->name;
                    $item['name'] .= '<br>';
                }

                $product_warehouse_total_qty = ProductWarehouse::where('product_id', $product->id)
                    ->sum('qty');

                $item['quantity'] = $product_warehouse_total_qty . ' ' . $product['unit']->short_name;

            } else {
                $item['type'] = 'Service';
                $item['name'] = $product->name;
                $item['cost'] = '----';
                $item['quantity'] = '----';
                $item['unit'] = '----';

                $item['price'] = number_format($product->price, 2, '.', ',');
            }


            $data[] = $item;
        }

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $categories = Category::whereNull('deleted_at')->get(['id', 'name']);
        $brands = Brand::whereNull('deleted_at')->get(['id', 'name']);

        return response()->json([
            'warehouses' => $warehouses,
            'categories' => $categories,
            'brands' => $brands,
            'products' => $data,
            'totalRows' => $totalRows,
        ]);
    }

    //-------------- Store new  Product  ---------------\\

    public function store(Request $request, CreateProductAction $action)
    {
        $this->authorizeForUser($request->user('api'), 'create', Product::class);

        try {

            // define validation rules for product
            $productRules = [
                'code' => [
                    'required',
                    Rule::unique('products'),
                    Rule::unique('product_variants'),
                ],
                'name' => 'required',
                'type_barcode' => 'required',
                'category_id' => 'required',
                'type' => ['required', 'in:' . implode(',', ProductType::values())],
                'tax_method' => ['required'],
                'unit_id' => Rule::requiredIf($request->get('type', null) !== 'is_service'),
                'cost' => Rule::requiredIf($request->get('type', null) === 'is_single'),
                'price' => Rule::requiredIf($request->get('type', null) !== 'is_variant'),
            ];

            // if type is not is_variant, add validation for variants array
            if ($request->get('type', null) == 'is_variant') {
                $productRules['variants'] = [
                    'required',
                    function ($attribute, $value, $fail) use ($request) {
                        // check if array is not empty
                        if (empty($value)) {
                            $fail('The variants array is required.');
                            return;
                        }

                        // check for duplicate codes in variants array
                        $variants = json_decode($request->variants, true);

                        if ($variants) {
                            foreach ($variants as $variant) {
                                if (!array_key_exists('text', $variant) || empty($variant['text'])) {
                                    $fail('Variant Name cannot be empty.');
                                    return;
                                } else if (!array_key_exists('code', $variant) || empty($variant['code'])) {
                                    $fail('Variant code cannot be empty.');
                                    return;
                                } else if (!array_key_exists('cost', $variant) || empty($variant['cost'])) {
                                    $fail('Variant cost cannot be empty.');
                                    return;
                                } else if (!array_key_exists('price', $variant) || empty($variant['price'])) {
                                    $fail('Variant price cannot be empty.');
                                    return;
                                }
                            }
                        } else {
                            $fail('The variants data is invalid.');
                            return;
                        }


                        //check if variant name empty
                        $names = array_column($variants, 'text');
                        if ($names) {
                            foreach ($names as $name) {
                                if (empty($name)) {
                                    $fail('Variant Name cannot be empty.');
                                    return;
                                }
                            }
                        } else {
                            $fail('Variant Name cannot be empty.');
                            return;
                        }

                        //check if variant cost empty
                        $all_cost = array_column($variants, 'cost');
                        if ($all_cost) {
                            foreach ($all_cost as $cost) {
                                if (empty($cost)) {
                                    $fail('Variant Cost cannot be empty.');
                                    return;
                                }
                            }
                        } else {
                            $fail('Variant Cost cannot be empty.');
                            return;
                        }

                        //check if variant price empty
                        $all_price = array_column($variants, 'price');
                        if ($all_price) {
                            foreach ($all_price as $price) {
                                if (empty($price)) {
                                    $fail('Variant Price cannot be empty.');
                                    return;
                                }
                            }
                        } else {
                            $fail('Variant Price cannot be empty.');
                            return;
                        }

                        //check if code empty
                        $codes = array_column($variants, 'code');
                        if ($codes) {
                            foreach ($codes as $code) {
                                if (empty($code)) {
                                    $fail('Variant code cannot be empty.');
                                    return;
                                }
                            }
                        } else {
                            $fail('Variant code cannot be empty.');
                            return;
                        }

                        //check if code Duplicate
                        if (count(array_unique($codes)) !== count($codes)) {
                            $fail('Duplicate codes found in variants array.');
                            return;
                        }

                        // check for duplicate codes in product_variants table
                        $duplicateCodes = DB::table('product_variants')
                            ->whereIn('code', $codes)
                            ->whereNull('deleted_at')
                            ->pluck('code')
                            ->toArray();
                        if (!empty($duplicateCodes)) {
                            $fail('This code : ' . implode(', ', $duplicateCodes) . ' already used');
                        }

                        // check for duplicate codes in products table
                        $duplicateCodes_products = DB::table('products')
                            ->whereIn('code', $codes)
                            ->whereNull('deleted_at')
                            ->pluck('code')
                            ->toArray();
                        if (!empty($duplicateCodes_products)) {
                            $fail('This code : ' . implode(', ', $duplicateCodes_products) . ' already used');
                        }
                    },
                ];
            }

            // validate the request data
            $validatedData = $request->validate($productRules, [
                'code.unique' => 'Product code already used.',
                'code.required' => 'This field is required',
            ]);

            $action->execute($request->all());

            return response()->json(['success' => true]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 422,
                'msg' => 'error',
                'errors' => $e->errors(),
            ], 422);
        }

    }

    //-------------- Update Product  ---------------\\
    //-----------------------------------------------\\

    public function update(Request $request, $id, UpdateProductAction $action)
    {

        $this->authorizeForUser($request->user('api'), 'update', Product::class);

        // define validation rules for product
        $productRules = [
            'code' => [
                'required',
                Rule::unique('products')
                    ->ignore($id)
                    ->where(fn($query) => $query->whereNull('deleted_at')),

                Rule::unique('product_variants')
                    ->ignore($id, 'product_id')
                    ->where(fn($query) => $query->whereNull('deleted_at')),
            ],
            'name' => 'required',
            'category_id' => 'required',
            'tax_method' => 'required',
            'type' => ['required', 'in:is_service,is_single,is_variant'],
            'unit_id' => Rule::requiredIf($request->get('type', null) != 'is_service'),
            'cost' => Rule::requiredIf($request->get('type', null) == 'is_single'),
            'price' => Rule::requiredIf($request->get('type', null) != 'is_variant'),
        ];

        // if type is not is_variant, add validation for variants array
        if ($request->get('type', null) == 'is_variant') {
            $productRules['variants'] = [
                'required',
                function ($attribute, $value, $fail) use ($request, $id) {
                    // check if array is not empty
                    if (empty($value)) {
                        $fail('The variants array is required.');
                        return;
                    }
                    // check for duplicate codes in variants array
                    $variants = $request->variants;

                    if ($variants) {
                        foreach ($variants as $variant) {
                            if (!array_key_exists('text', $variant) || empty($variant['text'])) {
                                $fail('Variant Name cannot be empty.');
                                return;
                            } else if (!array_key_exists('code', $variant) || empty($variant['code'])) {
                                $fail('Variant code cannot be empty.');
                                return;
                            } else if (!array_key_exists('cost', $variant) || empty($variant['cost'])) {
                                $fail('Variant cost cannot be empty.');
                                return;
                            } else if (!array_key_exists('price', $variant) || empty($variant['price'])) {
                                $fail('Variant price cannot be empty.');
                                return;
                            }
                        }
                    } else {
                        $fail('The variants data is invalid.');
                        return;
                    }

                    //check if variant name empty
                    $names = array_column($variants, 'text');
                    if ($names) {
                        foreach ($names as $name) {
                            if (empty($name)) {
                                $fail('Variant Name cannot be empty.');
                                return;
                            }
                        }
                    } else {
                        $fail('Variant Name cannot be empty.');
                        return;
                    }

                    //check if variant cost empty
                    $all_cost = array_column($variants, 'cost');
                    if ($all_cost) {
                        foreach ($all_cost as $cost) {
                            if (empty($cost)) {
                                $fail('Variant Cost cannot be empty.');
                                return;
                            }
                        }
                    } else {
                        $fail('Variant Cost cannot be empty.');
                        return;
                    }

                    //check if variant price empty
                    $all_price = array_column($variants, 'price');
                    if ($all_price) {
                        foreach ($all_price as $price) {
                            if (empty($price)) {
                                $fail('Variant Price cannot be empty.');
                                return;
                            }
                        }
                    } else {
                        $fail('Variant Price cannot be empty.');
                        return;
                    }

                    //check if code empty
                    $codes = array_column($variants, 'code');
                    if ($codes) {
                        foreach ($codes as $code) {
                            if (empty($code)) {
                                $fail('Variant code cannot be empty.');
                                return;
                            }
                        }
                    } else {
                        $fail('Variant code cannot be empty.');
                        return;
                    }

                    //check if code Duplicate
                    if (count(array_unique($codes)) !== count($codes)) {
                        $fail('Duplicate codes found in variants array.');
                        return;
                    }


                    // check for duplicate codes in product_variants table
                    $duplicateCodes = DB::table('product_variants')
                        ->where(fn($query) => $query->where('product_id', '<>', $id))
                        ->whereIn('code', $codes)
                        ->whereNull('deleted_at')
                        ->pluck('code')
                        ->toArray();
                    if (!empty($duplicateCodes)) {
                        $fail('This code : ' . implode(', ', $duplicateCodes) . ' already used');
                    }

                    // check for duplicate codes in products table
                    $duplicateCodes_products = DB::table('products')
                        ->where('id', '!=', $id)
                        ->whereIn('code', $codes)
                        ->whereNull('deleted_at')
                        ->pluck('code')
                        ->toArray();
                    if (!empty($duplicateCodes_products)) {
                        $fail('This code : ' . implode(', ', $duplicateCodes_products) . ' already used');
                    }
                },
            ];
        }

        // validate the request data
        $request->validate($productRules, [
            'code.unique' => 'Product code already used.',
            'code.required' => 'This field is required',
        ]);

        $product = Product::where('id', $id)
            ->whereNull('deleted_at')
            ->firstOrFail();


        $action->execute($product, $request->all());

        return response()->json(['success' => true]);

    }

    //-------------- Remove Product  ---------------\\
    //-----------------------------------------------\\

    public function destroy(Request $request, $id, DeleteProductAction $action)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Product::class);

        $action->execute($id);

        return response()->json(['success' => true]);

    }

    //-------------- Delete by selection  ---------------\\

    public function delete_by_selection(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'delete', Product::class);

        DB::transaction(function () use ($request) {
            $selectedIds = $request->selectedIds;
            foreach ($selectedIds as $product_id) {

                $product = Product::findOrFail($product_id);
                $product->deleted_at = Carbon::now();
                $product->save();
                $product->removeImages();

                ProductWarehouse::where('product_id', $product_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);

                ProductVariant::where('product_id', $product_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }

        }, 10);

        return response()->json(['success' => true]);

    }


    //--------------  Show Product Details ---------------\\

    public function Get_Products_Details(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'view', Product::class);

        $Product = Product::whereNull('deleted_at')->findOrFail($id);
        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        $item['id'] = $Product->id;
        $item['type'] = $Product->type;
        $item['code'] = $Product->code;
        $item['type_barcode'] = $Product->type_barcode;
        $item['name'] = $Product->name;
        $item['note'] = $Product->note;
        $item['category'] = $Product['category']->name;
        $item['brand'] = $Product['brand'] ? $Product['brand']->name : 'N/D';
        $item['price'] = $Product->price;
        $item['cost'] = $Product->cost;
        $item['stock_alert'] = $Product->stock_alert;
        $item['tax'] = $Product->tax_net;
        $item['tax_method'] = $Product->tax_method == '1' ? 'Exclusive' : 'Inclusive';

        if ($Product->type == 'is_single') {
            $item['type_name'] = 'Single';
            $item['unit'] = $Product['unit']->short_name;

        } elseif ($Product->type == 'is_variant') {
            $item['type_name'] = 'Variable';
            $item['unit'] = $Product['unit']->short_name;

        } else {
            $item['type_name'] = 'Service';
            $item['unit'] = '----';

        }

        if ($Product->is_variant) {
            $item['is_variant'] = 'yes';
            $productsVariants = ProductVariant::where('product_id', $id)
                ->whereNull('deleted_at')
                ->get();
            foreach ($productsVariants as $variant) {
                $ProductVariant['code'] = $variant->code;
                $ProductVariant['name'] = $variant->name;
                $ProductVariant['cost'] = number_format($variant->cost, 2, '.', ',');
                $ProductVariant['price'] = number_format($variant->price, 2, '.', ',');

                $item['products_variants_data'][] = $ProductVariant;

                foreach ($warehouses as $warehouse) {
                    $product_warehouse = ProductWarehouse::where('product_id', $id)
                        ->where('warehouse_id', $warehouse->id)
                        ->where('product_variant_id', $variant->id)
                        ->select(DB::raw('SUM(product_warehouse.qty) AS sum'))
                        ->first();

                    $war_var['mag'] = $warehouse->name;
                    $war_var['variant'] = $variant->name;
                    $war_var['qty'] = $product_warehouse->sum;
                    $item['CountQTY_variants'][] = $war_var;
                }

            }
        } else {
            $item['is_variant'] = 'no';
            $item['CountQTY_variants'] = [];
        }

        foreach ($warehouses as $warehouse) {
            $product_warehouse_data = ProductWarehouse::where('product_id', $id)
                ->where('warehouse_id', $warehouse->id)
                ->select(DB::raw('SUM(product_warehouse.qty) AS sum'))
                ->first();

            $war['mag'] = $warehouse->name;
            $war['qty'] = $product_warehouse_data->sum;
            $item['CountQTY'][] = $war;
        }

        if ($Product->image != '') {
            foreach (explode(',', $Product->image) as $img) {
                $item['images'][] = $img;
            }
        }

        $data[] = $item;

        return response()->json($data[0]);

    }

    //------------ Get products By Warehouse -----------------\\

    public function products_by_warehouse(request $request, $id, GetProductsByWarehouse $service)
    {
        $data = $service->execute(new GetProductsByWarehouseDTO(
            warehouse_id: $id,
            is_sale: $request->is_sale == '1',
            stock: $request->stock,
            product_service: $request->product_service,
            included_empty_stock: $request->included_empty_stock || false
        ));

        return response()->json($data);
    }


    //------------ Get product By ID -----------------\\
    public function show_product_data($id, $variant_id, GetProductDetail $service)
    {

        $data = $service->execute($id, $variant_id);

        return response()->json($data);
    }

    //--------------  Product Quantity Alerts ---------------\\

    public function Products_Alert(request $request)
    {
        $this->authorizeForUser($request->user('api'), 'Stock_Alerts', Product::class);

        $product_warehouse_data = ProductWarehouse::with('warehouse', 'product', 'productVariant')
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('manage_stock', true)
            ->whereRaw('qty <= stock_alert')
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('warehouse'), function ($query) use ($request) {
                    return $query->where('warehouse_id', $request->warehouse);
                });
            })->whereNull('product_warehouse.deleted_at')->get();

        $data = [];

        if ($product_warehouse_data->isNotEmpty()) {

            foreach ($product_warehouse_data as $product_warehouse) {
                if ($product_warehouse->qty <= $product_warehouse['product']->stock_alert) {
                    if ($product_warehouse->product_variant_id !== null) {
                        $item['code'] = $product_warehouse['productVariant']->code;
                        $item['name'] = '[' . $product_warehouse['productVariant']->name . ']' . $product_warehouse['product']->name;
                    } else {
                        $item['code'] = $product_warehouse['product']->code;
                        $item['name'] = $product_warehouse['product']->name;
                    }
                    $item['quantity'] = $product_warehouse->qty;
                    $item['warehouse'] = $product_warehouse['warehouse']->name;
                    $item['stock_alert'] = $product_warehouse['product']->stock_alert;
                    $data[] = $item;
                }
            }
        }

        $perPage = $request->limit; // How many items do you want to display.
        $pageStart = Request::get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $collection = collect($data);
        // Get only the items you need using array_slice
        $data_collection = $collection->slice($offSet, $perPage)->values();

        $products = new LengthAwarePaginator($data_collection, count($data), $perPage, Paginator::resolveCurrentPage(), array('path' => Paginator::resolveCurrentPath()));

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        return response()->json([
            'products' => $products,
            'warehouses' => $warehouses,
        ]);
    }

    //---------------- Show Form Create Product ---------------\\

    public function create(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'create', Product::class);

        $categories = Category::whereNull('deleted_at')->get(['id', 'name']);
        $brands = Brand::whereNull('deleted_at')->get(['id', 'name']);
        $units = Unit::whereNull('deleted_at')->whereNull('base_unit')->get();
        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
        ]);

    }

    //---------------- Show Elements Barcode ---------------\\

    public function Get_element_barcode(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'barcode', Product::class);

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        return response()->json(['warehouses' => $warehouses]);

    }

    //---------------- Show Form Edit Product ---------------\\

    public function edit(Request $request, $id)
    {

        $this->authorizeForUser($request->user('api'), 'update', Product::class);

        $product = Product::whereNull('deleted_at')->findOrFail($id);

        $item['id'] = $product->id;
        $item['type'] = $product->type;
        $item['code'] = $product->code;
        $item['type_barcode'] = $product->type_barcode;
        $item['name'] = $product->name;
        $item['category_id'] = Category::where('id', $product->category_id)
            ->whereNull('deleted_at')
            ->exists() ? $product->category_id : '';
        $item['brand_id'] = Brand::where('id', $product->brand_id ?? null)
            ->whereNull('deleted_at')
            ->exists() ? $product->brand_id : '';

        if ($product->unit_id) {
            $item['unit_id'] = Unit::where('id', $product->unit_id)
                ->whereNull('deleted_at')
                ->exists() ? $product->unit_id : '';

            $item['unit_sale_id'] = Unit::where('id', $product->unit_sale_id)
                ->whereNull('deleted_at')
                ->exists() ? $product->unit_sale_id : '';

            $item['unit_purchase_id'] = Unit::where('id', $product->unit_purchase_id)
                ->whereNull('deleted_at')
                ->exists() ? $product->unit_purchase_id : '';

        } else {
            $item['unit_id'] = '';
        }

        $item['tax_method'] = $product->tax_method;
        $item['price'] = $product->price;
        $item['cost'] = $product->cost;
        $item['stock_alert'] = $product->stock_alert;
        $item['tax_net'] = $product->tax_net;
        $item['note'] = $product->note ?? '';
        $item['images'] = [];
        $item['discounted_price'] = $product->discountedPrices()->latest()->valid()->first();
        if ($product->image != '' && $product->image != 'no-image.png') {
            $product->removeImages();
        }

        if ($product->type == 'is_variant') {
            $item['is_variant'] = true;
            $productsVariants = ProductVariant::with(['discountedPrices' => function ($query) {
                $query->latest();
            }])
                ->where('product_id', $id)
                ->whereNull('deleted_at')
                ->get();
            $var_id = 0;
            foreach ($productsVariants as $variant) {
                $variant_item['var_id'] = $var_id += 1;
                $variant_item['id'] = $variant->id;
                $variant_item['text'] = $variant->name;
                $variant_item['code'] = $variant->code;
                $variant_item['price'] = $variant->price;
                $variant_item['cost'] = $variant->cost;
                $variant_item['product_id'] = $variant->product_id;
                $variant_item['discounted_price'] = $variant->discountedPrices->first();
                $item['ProductVariant'][] = $variant_item;
            }
        } else {
            $item['is_variant'] = false;
            $item['ProductVariant'] = [];
        }

        $item['is_imei'] = $product->is_imei ? true : false;
        $item['not_selling'] = $product->not_selling ? true : false;

        $categories = Category::whereNull('deleted_at')->get(['id', 'name']);
        $brands = Brand::whereNull('deleted_at')->get(['id', 'name']);

        $product_units = Unit::where('id', $product->unit_id)
            ->orWhere('base_unit', $product->unit_id)
            ->whereNull('deleted_at')
            ->get();


        $units = Unit::whereNull('deleted_at')
            ->where('base_unit', null)
            ->get();

        return response()->json([
            'product' => $item,
            'categories' => $categories,
            'brands' => $brands,
            'units' => $units,
            'units_sub' => $product_units,
        ]);

    }

    // import Products
    public function import_products(Request $request)
    {
        ini_set('max_execution_time', 600); //600 seconds = 10 minutes

        $file = $request->file('products');
        $ext = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv') {
            return response()->json([
                'msg' => 'must be in csv format',
                'status' => false,
            ]);
        } else {
            // Read the CSV file
            $data = [];
            $rowcount = 0;
            if (($handle = fopen($file->getPathname(), "r")) !== false) {
                $max_line_length = 10000;
                $header = fgetcsv($handle, $max_line_length); // Use semicolon as the delimiter


                $header_colcount = count($header);
                while (($row = fgetcsv($handle, $max_line_length)) !== false) { // Use semicolon as the delimiter
                    $row_colcount = count($row);
                    if ($row_colcount == $header_colcount) {
                        $entry = array_combine($header, $row);
                        $data[] = $entry;
                    } else {
                        return null;
                    }
                    $rowcount++;
                }
                fclose($handle);
            } else {
                return null;
            }


            $warehouses = Warehouse::whereNull('deleted_at')->pluck('id')->toArray();

            // Create a new instance of Illuminate\Http\Request and pass the imported data to it.

            $cleanedData = [];

            foreach ($data as $row) {
                $cleanedRow = [];
                foreach ($row as $key => $value) {
                    $cleanedKey = trim($key);
                    $cleanedRow[$cleanedKey] = $value;
                }
                $cleanedData[] = $cleanedRow;
            }

            $rules = [];
            foreach ($cleanedData as $index => $row) {
                $rules[$index . '.name'] = 'required';
                $rules[$index . '.code'] = [
                    'required',
                    Rule::unique('products', 'code')
                        ->where(fn($query) => $query->whereNull('deleted_at')),
                ];
            }

            $validator = validator()->make($cleanedData, $rules);

            if ($validator->fails()) {
                // Validation failed
                return response()->json([
                    'msg' => 'Validation failed',
                    'errors' => $validator->errors(),
                    'status' => false,
                ]);
            }

            try {
                DB::transaction(function () use ($cleanedData, $warehouses) {


                    //-- Create New Product
                    foreach ($cleanedData as $key => $value) {

                        $category = Category::whereNull('deleted_at')
                            ->firstOrCreate(['name' => $value['category']], ['code' => Str::ascii($value['category'])]);

                        $sale_unit = Unit::where(['short_name' => $value['unit_sale']])
                            ->orWhere(['name' => $value['unit_sale']])
                            ->whereNull('deleted_at')
                            ->first();

                        $purchase_unit = Unit::where(['short_name' => $value['unit_purchase']])
                            ->orWhere(['name' => $value['unit_purchase']])
                            ->whereNull('deleted_at')
                            ->first();

                        if ($value['brand'] != 'N/A' && $value['brand'] != '') {
                            $brand = Brand::whereNull('deleted_at')
                                ->firstOrCreate(['name' => $value['brand']]);
                            $brand_id = $brand->id;
                        } else {
                            $brand_id = NULL;
                        }


                        $product = new Product;
                        $product->name = htmlspecialchars(trim($value['name']));;
                        $product->code = $product->check_code_exist($value['code']);
                        $product->type_barcode = $value['type_barcode'] ?? 'CODE128';
                        $product->type = 'is_single';
                        $product->price = str_replace(",", "", $value['price']);
                        $product->cost = str_replace(",", "", $value['cost']);
                        $product->category_id = $category->id;
                        $product->brand_id = $brand_id;
                        $product->tax_net = 0;
                        $product->tax_method = 1;
                        $product->note = $value['note'] ?? '';
                        $product->unit_id = $sale_unit->id;
                        $product->unit_sale_id = $sale_unit->id;
                        $product->unit_purchase_id = $purchase_unit->id;
                        $product->stock_alert = floatval($value['stock_alert']) ?? 0.0;
                        $product->image = 'no-image.png';
                        $product->save();

                        if ($warehouses) {
                            foreach ($warehouses as $warehouse) {
                                $product_warehouse = new ProductWarehouse;
                                $product_warehouse->product_id = $product->id;
                                $product_warehouse->warehouse_id = $warehouse;
                                $product_warehouse->manage_stock = 1;
                                $product_warehouse->qty = $value['qty'] ?? 0;
                                $product_warehouse->save();

                            }
                        }
                    }


                }, 10);

                // Return success response
                return response()->json(['status' => true]);

            } catch (QueryException $e) {
                $errorMessage = $e->getMessage();

                // Additional error handling or logging can be performed here

                return response()->json(['status' => false, 'error' => $errorMessage]);
            }

        }


    }

    // Generate_random_code
//    public function generate_random_code($value_code)
//    {
//        if ($value_code == '') {
//            $gen_code = substr(number_format(time() * mt_rand(), 0, '', ''), 0, 8);
//            $this->check_code_exist($gen_code);
//        } else {
//            $this->check_code_exist($value_code);
//        }
//    }


    // check_code_exist
//    public function check_code_exist($code)
//    {
//        $check_code = Product::where('code', $code)->whereNull('deleted_at')->first();
//        if ($check_code) {
//            $this->generate_random_code($code);
//        } else {
//            return $code;
//        }
//
//    }

    //----------------- count_stock_list

    public function count_stock_list(Request $request)
    {
        $this->authorizeForUser($request->user('api'), 'count_stock', Product::class);
        // How many items do you want to display.
        $perPage = $request->limit;
        $pageStart = $request->get('page', 1);
        // Start displaying items from this number;
        $offSet = ($pageStart * $perPage) - $perPage;
        $order = $request->SortField;
        $dir = $request->SortType;
        $helpers = new Helper();

        $count_stock = CountStock::whereNull('deleted_at')->with('warehouse', 'user')

            // Search With Multiple Param
            ->where(function ($query) use ($request) {
                return $query->when($request->filled('search'), function ($query) use ($request) {
                    return $query->whereHas('warehouse', function ($q) use ($request) {
                        $q->where('name', 'LIKE', "%{$request->search}%");
                    });
                });
            });
        $totalRows = $count_stock->count();
        if ($perPage == "-1") {
            $perPage = $totalRows;
        }
        $stocks = $count_stock->offset($offSet)
            ->limit($perPage)
            ->orderBy($order, $dir)
            ->get();

        $data = array();

        foreach ($stocks as $stock) {

            $item['id'] = $stock->id;
            $item['date'] = $stock->date;
            $item['warehouse_name'] = $stock['warehouse']->name;
            $item['file_stock'] = $stock->file_stock;

            $data[] = $item;
        }

        //get warehouses assigned to user
        $user_auth = auth()->user();
        if ($user_auth->is_all_warehouses) {
            $warehouses = Warehouse::whereNull('deleted_at')->get(['id', 'name']);
        } else {
            $warehouses_id = UserWarehouse::where('user_id', $user_auth->id)->pluck('warehouse_id')->toArray();
            $warehouses = Warehouse::whereNull('deleted_at')->whereIn('id', $warehouses_id)->get(['id', 'name']);
        }

        return response()->json([
            'totalRows' => $totalRows,
            'stocks' => $data,
            'warehouses' => $warehouses,
        ]);
    }

    //----------------- store_count_stock

    public function store_count_stock(Request $request)
    {

        $this->authorizeForUser($request->user('api'), 'count_stock', Product::class);

        $request->validate([
            'date' => 'required',
            'warehouse_id' => 'required',
        ]);

        $products = ProductWarehouse::join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->whereNull('product_warehouse.deleted_at')
            ->where('product_warehouse.warehouse_id', '=', $request->warehouse_id)
            ->select('product_warehouse.product_id as productID', 'products.name',
                'product_warehouse.product_variant_id as productVariantID', 'product_warehouse.qty')
            ->get();

        $stock = [];
        $incorrect_stock = [];

        foreach ($products as $product) {

            if ($product->productVariantID) {
                $variant = ProductVariant::where('product_id', $product->productID)->where('id', $product->productVariantID)->first();
                $item['product_name'] = $variant->name . '-' . $product->name;
            } else {
                $item['product_name'] = $product->name;
            }

            $item['quantity'] = $product->qty === 0.0 ? '0' : $product->qty;


            $stock[] = $item;
        }

        // Create an instance of StockExport with the warehouse name
        $stockExport = new StockExport($stock);

        $excelFileName = 'stock_export_' . now()->format('YmdHis') . '.xlsx';
        $excelFolderPath = public_path() . '/images/count_stock/';
        $excelFilePath = $excelFolderPath . $excelFileName;

        // Check if the directory exists, if not, create it
        if (!File::exists($excelFolderPath)) {
            File::makeDirectory($excelFolderPath, 0755, true, true);
        }

        // Use File::put to store the file directly in the desired public directory
        File::put($excelFilePath, Excel::raw($stockExport, \Maatwebsite\Excel\Excel::XLSX));

        // Save the file name in the count_stock table
        CountStock::create([
            'date' => $request->date,
            'warehouse_id' => $request->warehouse_id,
            'user_id' => Auth::user()->id,
            'file_stock' => $excelFileName
        ]);

        return response()->json(['success' => true]);

    }
}
