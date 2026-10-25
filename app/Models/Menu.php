<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = [
        'name',
        'description',
        'category_id',
        'category',
        'price',
        'image',
        'stock',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'is_available' => 'boolean',
        ];
    }

    public function menuCategory(): BelongsTo
    {
        return $this->belongsTo(MenuCategory::class, 'category_id');
    }

    public function recipeIngredients(): HasMany
    {
        return $this->hasMany(MenuIngredient::class);
    }

    // public function saleItems(): HasMany
    // {
    //     return $this->hasMany(SaleItem::class);
    // }
}
