<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\SaleStatus;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Promotions\Entities\Promotion;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'ref', 'is_pos', 'client_id', 'grand_total', 'qte_return', 'tax_net', 'tax_rate', 'notes',
        'total_return', 'warehouse_id', 'user_id', 'status', 'discount', 'shipping',
        'paid_amount', 'payment_status', 'created_at', 'updated_at', 'deleted_at', 'shipping_status'
    ];

    protected $casts = [
        'is_pos' => 'boolean',
        'grand_total' => 'double',
        'qte_return' => 'double',
        'total_return' => 'double',
        'user_id' => 'integer',
        'client_id' => 'integer',
        'warehouse_id' => 'integer',
        'discount' => 'double',
        'shipping' => 'double',
        'tax_net' => 'double',
        'tax_rate' => 'double',
        'paid_amount' => 'double',
        'payment_status' => PaymentStatus::class,
        'status' => SaleStatus::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentSale');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function promotions()
    {
        return $this->belongsToMany(Promotion::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PaymentSale::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SaleReturn::class)->latest();
    }

    public static function newFactory()
    {
        return SaleFactory::new();
    }

    // region Accessors
    public function getDueAttribute()
    {
        return $this->grand_total - $this->paid_amount;
    }

    public function getHasReturnAttribute(): bool
    {
        return $this->returns->count() > 0;
    }
    // endregion

// region Mutators

    public
    function setRefAttribute($value): void
    {
        $this->attributes['ref'] = (!$value || empty($valu)) ? static::generateNumberOrder() : $value;
    }

// endregion

    public
    static function generateNumberOrder(): string
    {
        $last = static::latest('id')->first();

        if (!$last) {
            return 'SL_1111';
        }

        $item = $last->Ref;
        $nwMsg = explode("_", $item);
        $inMsg = $nwMsg[1] + 1;
        return $nwMsg[0] . '_' . $inMsg;
    }

}

