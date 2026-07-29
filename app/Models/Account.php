<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'balance',
        'credit_limit',
        'billing_cycle_day',
        'due_date_day',
        'color',
        'icon',
        'is_archived',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'balance'           => 'decimal:2',
        'credit_limit'      => 'decimal:2',
        'billing_cycle_day' => 'integer',
        'due_date_day'      => 'integer',
        'is_archived'       => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    /**
     * Adjust balance by a signed amount (positive = credit, negative = debit).
     *
     * @param  float $amount the signed amount to adjust by
     * @return void
     */
    public function adjustBalance(float $amount): void
    {
        $this->increment('balance', $amount);
    }
}
