<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'note_customer', 'show_note', 'show_barcode', 'show_discount', 'show_customer',
         'show_email','show_phone','show_address','is_printable','show_Warehouse'
    ];

    protected $casts = [
        'show_note' => 'boolean',
        'show_barcode' => 'boolean',
        'show_discount' => 'boolean',
        'show_customer' => 'boolean',
        'show_Warehouse' => 'boolean',
        'show_email' => 'boolean',
        'show_phone' => 'boolean',
        'show_address' => 'boolean',
        'is_printable' => 'boolean',
    ];


}
