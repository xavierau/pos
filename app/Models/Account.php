<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'account_num','account_name','initial_balance','balance','note',
    ];

    protected $casts = [
        'initial_balance' => 'double',
        'balance' => 'double',
    ];
}
