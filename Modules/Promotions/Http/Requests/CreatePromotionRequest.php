<?php

namespace Modules\Promotions\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Promotions\Enums\PromotionType;

class CreatePromotionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {

        $rules = [
            'name' => ['required'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'max_applications_per_sale' => ['numeric'],
            'max_usage' => ['numeric', 'min:0'],
            'rule' => ['required'],
            'rule.type' => ['required', 'in:' . implode(',', PromotionType::values())],

        ];

        $type = PromotionType::from($this->get('rule')['type']);

        if ($type === PromotionType::BuyXGetY) {
            $rules = array_merge($rules, [
                'rule.x_qty' => ['required', 'numeric', 'min:0'],
                'rule.y_qty' => ['required', 'numeric', 'min:0'],
                'rule.x_product' => ['required'],
                'rule.x_product.product_id' => ['required', Rule::exists('products', 'id')],
                'rule.x_product.product_variant_id' => ["nullable", Rule::exists('product_variants', 'id')->where(fn($q) => $q->where('product_id', $this->get('rule')['x_product']['product_id']))],
                'rule.y_product' => ['required'],
                'rule.y_product.product_id' => ['required', Rule::exists('products', 'id')],
                'rule.y_product.product_variant_id' => ["nullable", Rule::exists('product_variants', 'id')->where(fn($q) => $q->where('product_id', $this->get('rule')['y_product']['product_id']))],
            ]);
        } elseif ($type === PromotionType::FreeXIfOverYAmount) {
            $rules = array_merge($rules, [
                'rule.x_qty' => ['required', 'numeric', 'min:0'],
                'rule.y_qty' => ['required', 'numeric', 'min:0'],
                'rule.x_product' => ['required'],
                'rule.x_product.product_id' => ['required', Rule::exists('products', 'id')],
                'rule.x_product.product_variant_id' => ['nullable', Rule::exists('product_variants', 'id')->where(fn($q) => $q->where('product_id', $this->get('rule')['x_product']['product_id']))],
            ]);
        } elseif ($type === PromotionType::DiscountXPercentageIfOverYAmount) {
            $rules = array_merge($rules, [
                'rule.amount' => ['required', 'numeric', 'min:0'],
                'rule.x_qty' => ['required', 'numeric', 'in:1,2'],
                'rule.y_qty' => ['required', 'numeric'],
            ]);
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
