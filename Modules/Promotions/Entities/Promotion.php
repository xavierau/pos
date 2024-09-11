<?php

// Modules/Promotions/Entities/Promotion.php
namespace Modules\Promotions\Entities;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Modules\Promotions\Database\factories\PromotionFactory as ModelFactory;
use Modules\Promotions\DTO\PromotionFilter;
use Modules\Promotions\Factories\PromotionFactory;

class Promotion extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'name', 'description', 'start_date', 'end_date', 'max_applications_per_sale', 'max_usage',
    ];

    private const string ActiveCacheKey = 'promotions_active';
    private const  int DefaultCacheDuration = 5;

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $appends = ['type'];

    public function rules()
    {
        return $this->hasMany(PromotionRule::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class);
    }

    public function saleDetails()
    {
        return $this->belongsToMany(SaleDetail::class);
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class);
    }


    public static function newFactory()
    {
        return ModelFactory::new();
    }

    public function getTotalUsage()
    {
        // Calculate the total usage of the promotion
        // each sales may apply more than one once
        // only fetch sales where the sales not returned

    }

    public function scopeSearch(Builder $query, string $keyword = null)
    {
        return $query->when($keyword, fn($q) => $q->where('name', 'like', "%{$keyword}%")
            ->orWhere('description', 'like', "%{$keyword}%"));
    }

    public function scopeFilter(Builder $query, PromotionFilter $filter)
    {
        return $query->when($filter->filter_start_date || $filter->filter_end_date,
            function ($q) use ($filter) {
                if ($filter->filter_start_date) {
                    $q->whereDate('start_date', '>=', $filter->filter_start_date);
                }
                if ($filter->filter_end_date) {
                    $q->whereDate('end_date', '<=', $filter->filter_end_date);
                }
                return $q;
            });

    }

    public function scopeActive(Builder $query)
    {
        return $query->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now());
    }

    static public function getActivePromotions()
    {
        return Cache::remember(self::ActiveCacheKey, self::DefaultCacheDuration, fn() => Promotion::with('rules')->active()->get());
    }

    /**
     * @param \Modules\Promotions\DTO\PosDetailDTO[] $lineItems
     * @return PromotionRule[]
     */
    public function getPromotedItems(array $lineItems, $rewards): array
    {
        $results = [];
        foreach ($this->rules as $rule) {
            $promotionHandler = PromotionFactory::create($rule);
            $results[] = $promotionHandler->getPromotedItems($lineItems, $rewards);
        }

        return $results;
    }


    public function getTypeAttribute()
    {
        return $this->rules->first()->type;
    }

}
