<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'cost_price',
        'is_active',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function getActives($query)
    {
        return $query->where('is_active', true);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: $this->name;
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }

        if (!empty($this->description)) {
            return \Illuminate\Support\Str::limit(
                strip_tags($this->description),
                160
            );
        }

        return null;
    }
}
