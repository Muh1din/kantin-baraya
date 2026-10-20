<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_REFUNDED = 'refunded';

    public const METHOD_CASH = 'cash';
    public const METHOD_QRIS = 'qris';
    public const METHOD_DEBIT_CARD = 'debit_card';
    public const METHOD_OTHER = 'other';

    /** Monetary fields are intentionally excluded from mass assignment. */
    protected $fillable = [
        'cashier_id',
        'status',
        'payment_status',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'total' => 'integer',
            'cash_received' => 'integer',
            'change_due' => 'integer',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
