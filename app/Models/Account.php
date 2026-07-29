<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

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
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'billing_cycle_day' => 'integer',
        'due_date_day' => 'integer',
        'is_archived' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_account_id');
    }

    /**
     * Adjust balance by a signed amount (positive = credit, negative = debit) using BCMath precision.
     *
     * @param  string|float|int  $amount  the signed amount to adjust by
     */
    public function adjustBalance(string|float|int $amount): void
    {
        $current = number_format((float) ($this->balance ?? 0), 2, '.', '');
        $delta = number_format((float) $amount, 2, '.', '');
        $newBalance = bcadd($current, $delta, 2);

        $this->update(['balance' => $newBalance]);
    }
}
