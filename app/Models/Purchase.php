<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'ref', 'provider_id', 'warehouse_id', 'grand_total',
        'discount', 'shipping', 'status', 'notes', 'tax_net', 'tax_rate', 'paid_amount',
        'payment_status', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'provider_id' => 'integer',
        'warehouse_id' => 'integer',
        'grand_total' => 'double',
        'discount' => 'double',
        'shipping' => 'double',
        'tax_net' => 'double',
        'tax_rate' => 'double',
        'paid_amount' => 'double',
        'payment_status' => PaymentStatus::class
    ];

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function facture()
    {
        return $this->hasMany(PaymentPurchase::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
