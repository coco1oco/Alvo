<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'linked_account_id',
        'name',
        'target_amount',
        'current_amount',
        'deadline',
        'color',
        'icon',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'date',
    ];

    protected $appends = [
        'effective_current_amount',
        'progress_percentage',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function linkedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'linked_account_id');
    }

    /**
     * Get the effective current amount saved toward the goal.
     * If linked to a savings account, uses the account balance (if positive), else manual current_amount.
     */
    public function getEffectiveCurrentAmountAttribute(): float
    {
        if ($this->linked_account_id && $this->linkedAccount) {
            return (float) $this->linkedAccount->balance;
        }

        return (float) $this->current_amount;
    }

    /**
     * Get progress percentage (0-100).
     */
    public function getProgressPercentageAttribute(): float
    {
        $target = (float) $this->target_amount;
        if ($target <= 0) {
            return 0;
        }

        $current = $this->effective_current_amount;

        return round(min(($current / $target) * 100, 100), 1);
    }
}
