<?php

namespace App\Models;

use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'code', 'address', 'email', 'phone', 'country', 'city','tax_number'

    ];

    protected $casts = [
        'code' => 'integer',
    ];

    public static function newFactory()
    {
        return ClientFactory::new();
    }
}
