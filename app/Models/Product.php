<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'slug',
    'short_description',
    'information',
    'description',
    'regular_price',
    'sale_price',
    'SKU',
    'stock_status',
    'featured',
    'quantity',
    'image',
    'images',
    'category_id',
    'brand_id',
    'status',
])]
class Product extends Model
{
    /**
     * Get the brand that owns the product.
     * 
     * @return BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * Get the category that owns the product.
     * 
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
