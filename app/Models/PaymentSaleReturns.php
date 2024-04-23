<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSaleReturns extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'sale_return_id', 'date', 'montant','change', 'Ref', 'Reglement', 'user_id', 'notes','account_id'
    ];

    protected $casts = [
        'montant' => 'double',
        'change'  => 'double',
        'sale_return_id' => 'integer',
        'user_id' => 'integer',
        'account_id' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function SaleReturn()
    {
        return $this->belongsTo('App\Models\SaleReturn');
    }

}
