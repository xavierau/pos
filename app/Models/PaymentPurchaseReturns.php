<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPurchaseReturns extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'date'];

    protected $fillable = [
        'purchase_return_id', 'date', 'amount', 'change', 'ref', 'type', 'user_id', 'notes', 'account_id'
    ];

    protected $casts = [
        'amount' => 'double',
        'change' => 'double',
        'purchase_return_id' => 'integer',
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

    public function PurchaseReturn()
    {
        return $this->belongsTo('App\Models\PurchaseReturn');
    }

}
