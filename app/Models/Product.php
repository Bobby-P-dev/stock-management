<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'sku',
        'name',
        'category_id',
        'unit_id',
        'minimum_stock',
        'current_stock',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minimum_stock' => 'integer',
            'current_stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return HasMany<StockTransactionItem, $this>
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(StockTransactionItem::class);
    }

    /**
     * @return HasMany<StockOpnameItem, $this>
     */
    public function opnameItems(): HasMany
    {
        return $this->hasMany(StockOpnameItem::class);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('current_stock', '<=', 'minimum_stock');
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->current_stock > 0 && $this->current_stock <= $this->minimum_stock;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Habis';
        }

        if ($this->isLowStock()) {
            return 'Menipis';
        }

        return 'Aman';
    }

    public function getVelocityLabelAttribute(): string
    {
        return match ($this->velocity_status ?? 'NON_MOVING') {
            'FAST_MOVING' => 'Fast Moving',
            'MEDIUM_MOVING' => 'Medium Moving',
            'SLOW_MOVING' => 'Slow Moving',
            default => 'Non-Moving',
        };
    }
}
