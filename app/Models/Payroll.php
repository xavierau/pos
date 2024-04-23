<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'user_id','date','employee_id','account_id','amount','payment_method','payment_status','Ref',
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $casts = [
        'amount'      => 'double',
        'user_id'     => 'integer',
        'employee_id' => 'integer',
        'account_id'  => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function account()
    {
        return $this->belongsTo('App\Models\Account');
    }

    public function employee()
    {
        return $this->belongsTo('App\Models\Employee');
    }
}
