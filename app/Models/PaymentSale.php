<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentSale extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at', 'date'];

    protected $fillable = [
        'sale_id', 'date', 'amount', 'ref', 'change', 'type', 'user_id', 'notes', 'account_id'
    ];

    protected $casts = [
        'amount' => 'double',
        'change' => 'double',
        'sale_id' => 'integer',
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

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale');
    }

    // region Mutators

    public function setRefAttribute($value): void
    {
        $this->attributes['ref'] = $value ?? static::generateNumberOrder();
    }

    // endregion

    // region Helpers

    public static function generateOrderNumber(): string
    {
        $last = static::latest('id')->first();

        if (!$last) {
            return 'INV/SL_1111';
        }

        $item = $last->Ref;
        $nwMsg = explode("_", $item);
        $inMsg = $nwMsg[1] + 1;
        return $nwMsg[0] . '_' . $inMsg;
    }

// endregion

}
