<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'name',
        'amount',
        'billing_cycle',
        'next_renewal_date',
        'logo_url',
        'color',
        'is_active',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'next_renewal_date' => 'date',
        'is_active'         => 'boolean',
    ];

    protected $appends = [
        'monthly_amount',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Calculate monthly equivalent cost.
     */
    public function getMonthlyAmountAttribute(): float
    {
        $amount = (float) $this->amount;
        return match ($this->billing_cycle) {
            'weekly' => round($amount * 4.33, 2),
            'yearly' => round($amount / 12, 2),
            default  => $amount,
        };
    }
}
