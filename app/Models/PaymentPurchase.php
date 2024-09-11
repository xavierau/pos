<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentPurchase extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'purchase_id', 'date', 'amount', 'change', 'ref', 'amount', 'user_id', 'notes', 'account_id'
    ];

    protected $casts = [
        'amount' => 'double',
        'change' => 'double',
        'purchase_id' => 'integer',
        'user_id' => 'integer',
        'account_id' => 'integer',
        'date' => 'date:Y-m-d H:m'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function purchase()
    {
        return $this->belongsTo('App\Models\Purchase');
    }

    // region Mutators

    public function setRefAttribute($value)
    {
        $this->attributes['ref'] = $value ? $value : static::genRef();
    }

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = $value ?? now();
    }

    // endregion

    // region Helper

    public static function genRef()
    {
        $last = static::withTrashed()->latest('id')->first();

        if ($last) {
            $item = $last->ref;
            $nwMsg = explode("_", $item);
            $inMsg = $nwMsg[1] + 1;
            return $nwMsg[0] . '_' . $inMsg;
        } else {
            return 'INV/PR_1111';
        }

    }


    // endregion

}
