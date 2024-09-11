<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleReturn extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'date', 'ref', 'grand_total',
        'user_id', 'discount', 'shipping',
        'warehouse_id', 'client_id', 'sale_id', 'notes', 'tax_net', 'tax_rate', 'status',
        'paid_amount', 'payment_status', 'created_at', 'updated_at', 'deleted_at',
    ];

    protected $casts = [
        'grand_total' => 'double',
        'user_id' => 'integer',
        'client_id' => 'integer',
        'sale_id' => 'integer',
        'warehouse_id' => 'integer',
        'discount' => 'double',
        'shipping' => 'double',
        'TaxNet' => 'double',
        'tax_rate' => 'double',
        'paid_amount' => 'double',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function details()
    {
        return $this->hasMany('App\Models\SaleReturnDetails');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    public function facture()
    {
        return $this->hasMany('App\Models\PaymentSaleReturns');
    }

}
