<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransaction extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'transaction_no',
        'type',
        'transaction_date',
        'supplier_id',
        'recipient',
        'note',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return HasMany<StockTransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransactionItem::class);
    }

    /**
     * @param  Builder<StockTransaction>  $query
     * @return Builder<StockTransaction>
     */
    public function scopeIn(Builder $query): Builder
    {
        return $query->where('type', 'IN');
    }

    /**
     * @param  Builder<StockTransaction>  $query
     * @return Builder<StockTransaction>
     */
    public function scopeOut(Builder $query): Builder
    {
        return $query->where('type', 'OUT');
    }
}
