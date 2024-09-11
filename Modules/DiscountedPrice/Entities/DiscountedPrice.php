<?php

namespace Modules\DiscountedPrice\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountedPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'discountable_id', 'discountable_type', 'discounted_price', 'start_date', 'end_date'
    ];


    protected $dates = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d'
    ];

    public function discountable()
    {
        return $this->morphTo();
    }

    public function scopeValid(Builder $query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

}
