<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Override;

class Category extends Model
{
    use HasUuids;

    #[Override]
    protected static function booted()
    {
        static::creating(function ($category) {
            $category->slug = self::generateSlug($category);
        });

        static::updating(function ($category) {
            $category->slug = self::generateSlug($category);
        });
    }

    protected static function generateSlug(Category $category): string 
    {
        $slug = Str::slug($category->name);
        $originalSlug = $slug;
        $count = 1;

        while (static::where('slug', $slug)
            // If updating, ignore the current category's own ID
            ->when($category->exists, function ($query) use ($category) {
                return $query->where('id', '!=', $category->id);
            })
            ->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    protected $fillable = [
        'name', 
        'slug',
        'description',
        'image_url',
        'image_public_id',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
