<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, Prunable, SoftDeletes;

    /** @var string[] */
    protected $fillable = [
        'user_id',
        'account_id',
        'to_account_id',
        'category_id',
        'type',
        'amount',
        'description',
        'notes',
        'is_reimbursable',
        'attachment_path',
        'tags',
        'date',
        'is_split',
        'split_data',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_split' => 'boolean',
        'split_data' => 'array',
        'is_reimbursable' => 'boolean',
        'tags' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable()
    {
        return static::where('deleted_at', '<=', now()->subDays(30));
    }
}
