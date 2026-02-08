<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationship: A category has many products
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // Scope: Get only active categories
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
