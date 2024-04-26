<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $fillable = [
        'currency_id', 'email', 'CompanyName', 'CompanyPhone', 'CompanyAddress','quotation_with_stock',
         'logo','footer','developed_by','client_id','warehouse_id','default_language',
         'is_invoice_footer','invoice_footer',
    ];

    protected $casts = [
        'currency_id' => 'integer',
        'client_id' => 'integer',
        'quotation_with_stock' => 'integer',
        'is_invoice_footer' => 'integer',
        'warehouse_id' => 'integer',
    ];

    public function Currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function Client()
    {
        return $this->belongsTo(Client::class);
    }

}
