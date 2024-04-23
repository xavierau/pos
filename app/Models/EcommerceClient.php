<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;

class EcommerceClient extends Model implements Authenticatable
{
    use AuthenticatableTrait;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'client_id', 'username', 'email', 'status','password'

    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'client_id' => 'integer',
        'email_verified_at' => 'datetime',
        'status' => 'integer',
    ];


    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}
