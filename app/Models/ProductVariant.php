<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $table = 'product_variants';

    protected $fillable = [
        'product_id', 'name', 'qty', 'cost', 'price', 'code', 'image'
    ];

    protected $casts = [
        'product_id' => 'integer',
        'qty' => 'double',
        'cost' => 'double',
        'price' => 'double',
    ];

    // region relationship

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);

    }

    // endregion

}
