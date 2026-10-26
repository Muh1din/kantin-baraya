<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientStockMovement extends Model
{
    public const TYPE_OPENING_BALANCE = 'opening_balance';
    public const TYPE_STOCK_IN = 'stock_in';
    public const TYPE_STOCK_OUT = 'stock_out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'ingredient_id',
        'user_id',
        'movement_type',
        'quantity_change',
        'stock_after',
        'transaction_key',
        'notes',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'decimal:3',
            'stock_after' => 'decimal:3',
            'occurred_at' => 'datetime',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
