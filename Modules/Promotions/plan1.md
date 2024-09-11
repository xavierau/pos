# Laravel POS Project: Promotions Module

This guide will help you create a Promotions module for a Laravel POS project using `nwidart/laravel-modules`. The
module supports various promotional strategies like "buy x get y" and "free x if over y amount".

## Step 1: Create a New Module

Create a new module named `Promotions`.

```sh
php artisan module:make Promotions
```

## Step 2: Create Migrations

Create the necessary migrations for promotions.

```sh
php artisan module:make-migration create_promotions_table Promotions
php artisan module:make-migration create_promotion_rules_table Promotions
php artisan module:make-migration create_sale_promotion_table Promotions
```

Define the schema in the migration files:

### Modules/Promotions/Database/Migrations/2024_05_26_000000_create_promotions_table.php

```php
public function up()
{
    Schema::create('promotions', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->dateTime('start_date');
        $table->dateTime('end_date');
        $table->timestamps();
    });
}
```

### Modules/Promotions/Database/Migrations/2024_05_26_000001_create_promotion_rules_table.php

```php
public function up()
{
    Schema::create('promotion_rules', function (Blueprint $table) {
        $table->id();
        $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
        $table->string('type');
        $table->integer('x')->nullable();
        $table->integer('y')->nullable();
        $table->decimal('amount', 8, 2)->nullable();
        $table->timestamps();
    });
}
```

### Modules/Promotions/Database/Migrations/2024_05_26_000002_create_sale_promotion_table.php

```php
public function up()
{
    Schema::create('sale_promotion', function (Blueprint $table) {
        $table->id();
        $table->foreignId('sale_id')->constrained()->onDelete('cascade');
        $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
        $table->timestamps();
    });
}
```

## Step 3: Define Models and Relationships

Create models within the module and define their relationships.

```sh
php artisan module:make-model Promotion Promotions
php artisan module:make-model PromotionRule Promotions
```

### Modules/Promotions/Entities/Promotion.php

```php
namespace Modules\Promotions\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Promotion extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'start_date', 'end_date'];

    public function rules()
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'sale_promotion');
    }
}
```

### Modules/Promotions/Entities/PromotionRule.php

```php
namespace Modules\Promotions\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PromotionRule extends Model
{
    use HasFactory;

    protected $fillable = ['promotion_id', 'type', 'x', 'y', 'amount'];

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }
}
```

## Step 4: Create Promotion Handlers and Factory

Implement the promotion handlers and the factory within the module.

```sh
mkdir -p Modules/Promotions/Contracts
mkdir -p Modules/Promotions/Services/Promotions
mkdir -p Modules/Promotions/Database/Factories
touch Modules/Promotions/Contracts/IPromotion.php
touch Modules/Promotions/Services/Promotions/BuyXGetYPromotion.php
touch Modules/Promotions/Services/Promotions/FreeXIfOverYAmountPromotion.php
touch Modules/Promotions/Factories/PromotionFactory.php
touch Modules/Promotions/Services/PromotionService.php
```

### Modules/Promotions/Contracts/IPromotion.php

```php
namespace Modules\Promotions\Contracts;

use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleReturn;

interface IPromotion
{
    public function apply(Sale $sale);
    public function adjustForReturn(SaleReturn $saleReturn);
}
```

### Modules/Promotions/Services/Promotions/BuyXGetYPromotion.php

```php
namespace Modules\Promotions\Services\Promotions;

use Modules\Promotions\Contracts\IPromotion;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleReturn;

class BuyXGetYPromotion implements IPromotion
{
    protected $rule;

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    public function apply(Sale $sale)
    {
        foreach ($sale->details as $detail) {
            if ($this->isEligibleForPromotion($detail)) {
                $this->applyDiscount($detail);
                $sale->promotions()->attach($this->rule->promotion_id);
            }
        }
    }

    public function adjustForReturn(SaleReturn $saleReturn)
    {
        foreach ($saleReturn->details as $detail) {
            if ($this->wasPromotionApplied($detail)) {
                $this->reverseDiscount($detail);
            }
        }
    }

    protected function isEligibleForPromotion($detail)
    {
        return $detail->product_id == $this->rule->x;
    }

    protected function wasPromotionApplied($detail)
    {
        return $detail->discount_method == 'buy_x_get_y';
    }

    protected function applyDiscount($detail)
    {
        $detail->discount = $detail->price * $this->rule->y;
        $detail->discount_method = 'buy_x_get_y';
        $detail->save();
    }

    protected function reverseDiscount($detail)
    {
        $detail->discount = 0;
        $detail->discount_method = null;
        $detail->save();
    }
}
```

### Modules/Promotions/Services/Promotions/FreeXIfOverYAmountPromotion.php

```php
namespace Modules\Promotions\Services\Promotions;

use Modules\Promotions\Contracts\IPromotion;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleReturn;

class FreeXIfOverYAmountPromotion implements IPromotion
{
    protected $rule;

    public function __construct($rule)
    {
        $this->rule = $rule;
    }

    public function apply(Sale $sale)
    {
        if ($sale->GrandTotal >= $this->rule->amount) {
            $this->applyFreeProduct($sale);
        }
    }

    public function adjustForReturn(SaleReturn $saleReturn)
    {
        if ($saleReturn->GrandTotal < $this->rule->amount) {
            $this->reverseFreeProduct($saleReturn);
        }
    }

    protected function applyFreeProduct($sale)
    {
        $sale->details()->create([
            'product_id' => $this->rule->x,
            'price' => 0,
            'quantity' => 1,
            'discount' => 0,
            'discount_method' => 'free_x_if_over_y_amount'
        ]);
    }

    protected function reverseFreeProduct($saleReturn)
    {
        $freeProduct = $saleReturn->details()->where('discount_method', 'free_x_if_over_y_amount')->first();
        if ($freeProduct) {
            $freeProduct->delete();
        }
    }
}
```

### Modules/Promotions/Factories/PromotionFactory.php

```php

namespace Modules\Promotions\Factories;

use Modules\Promotions\Entities\PromotionRule;
use Modules\Promotions\Services\Promotions\BuyXGetYPromotion;
use Modules\Promotions\Services\Promotions\FreeXIfOverYAmountPromotion;

class PromotionFactory
{
    public static function create(PromotionRule $rule)
    {
        switch ($rule->type) {
            case 'buy_x_get_y':
                return new BuyXGetYPromotion($rule);
            case 'free_x_if_over_y_amount':
                return new FreeXIfOverYAmountPromotion($rule);
            default:
                throw new \Exception("Unknown promotion type: {$rule->type}");
        }
    }
}
```

### Modules/Promotions/Services/PromotionService.php

```php
namespace Modules\Promotions\Services;

use Modules\Promotions\Factories\PromotionFactory;
use Modules\Promotions\Entities\Promotion;
use Modules\Sales\Entities\Sale;
use Modules\Sales\Entities\SaleReturn;

class PromotionService
{
    public function applyPromotions(Sale $sale)
    {
        $promotions = Promotion::where('start_date', '<=', now())
                               ->where('end_date', '>=', now())
                               ->get();

        foreach ($promotions as $promotion) {
            foreach ($promotion->rules as $rule) {
                $promotionHandler = PromotionFactory::create($rule);
                $promotionHandler->apply($sale);
            }
        }

        return $sale;
    }

    public function adjustPromotionsForReturn(SaleReturn $saleReturn)
    {
        $originalSale = $saleReturn->sale;

        // Get promotions applied to the original sale
        $promotions = $this->getPromotionsFromSale($originalSale);
        foreach ($promotions as $promotion) {
            foreach ($promotion->rules as $rule) {
                $promotionHandler = PromotionFactory::create($rule);
                $promotionHandler->adjustForReturn($saleReturn);
            }
        }
    
        return $saleReturn;
    }

    protected function getPromotionsFromSale(Sale $sale)
    {
        // Assuming there is a way to track which promotions were applied to a sale
        // This method should retrieve those promotions. For simplicity, let's assume
        // it's stored in a pivot table sale_promotion or similar.
    
        // Example pseudo-code:
        // return $sale->promotions;
    
        // Replace this with actual logic as per your database structure
        return Promotion::whereIn('id', $sale->promotion_ids)->get();
    }
}
``` 

## Summary

- **Created Module**: Promotions module created using `nwidart/laravel-modules`.
- **Database Structure**: Migrations for promotions, promotion rules, and sale promotions.
- **Models and Relationships**: Defined models and relationships for promotions and rules.
- **Promotion Handlers**: Implemented promotion handlers and a factory for creating them.
- **Service Layer**: Added service layer to apply and adjust promotions.
- **Organized Structure**: Followed directory structure conventions for contracts and factories.


