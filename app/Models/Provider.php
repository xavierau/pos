<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'code', 'address', 'phone_1', 'phone_2', 'country', 'email', 'city', 'tax_number'
    ];

    // region Relation
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function purchaseDetails(): HasManyThrough
    {
        return $this->hasManyThrough(PurchaseDetail::class, Purchase::class);
    }

    public function purchaseReturnDetails(): HasManyThrough
    {
        return $this->hasManyThrough(PurchaseReturnDetail::class, PurchaseReturn::class);
    }

    // endregion


    //scope

    public function scopeFilter($query, array $filters)
    {
        return $query->where(function ($q) use ($filters) {
            $q->when($filters['name'], fn($subq) => $subq->where('name', 'like', '%' . $filters['name'] . '%'));
            $q->when($filters['code'], fn($subq) => $subq->where('code', 'like', '%' . $filters['code'] . '%'));
            $q->when($filters['phone'], fn($subq) => $subq->where('phone_1', 'like', '%' . $filters['phone'] . '%')->orWhere('phone_2', 'like', '%' . $filters['phone'] . '%'));


            $q->when(request()->query('phone'), function ($subq) {
                $subq->where('phone_1', 'like', '%' . request()->query('phone') . '%')
                    ->orWhere('phone_2', 'like', '%' . request()->query('phone') . '%');
            });
            $q->when(request()->query('email'), function ($subq) {
                $subq->where('email', 'like', '%' . request()->query('email') . '%');
            });
            $q->when(request()->query('contact'), function ($subq) {
                $subq->where('contact', 'like', '%' . request()->query('contact') . '%');
            });
        });
    }

    // end scope
}
