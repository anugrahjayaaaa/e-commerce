<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'name', 'slug', 'image', 'status'])]
class Category extends Model
{
    /**
     * Get the parent category that owns the category.
     * 
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the subcategories for the category.
     * 
     * @return HasMany
     */
    public function children(): HasMany
    {
        // Add ->with('children') here for eager loading nested categories
        return $this->hasMany(Category::class, 'parent_id');
    }
}
