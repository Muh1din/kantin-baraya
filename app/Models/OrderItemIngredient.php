<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemIngredient extends Model
{
    protected $fillable = [
        'order_item_id',
        'ingredient_id',
        'ingredient_name',
        'unit_abbreviation',
        'quantity_per_item',
        'required_quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity_per_item' => 'decimal:3',
            'required_quantity' => 'decimal:3',
        ];
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }
}
