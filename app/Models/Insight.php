<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Insight extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'preview_chars',
        'cover_image_url',
        'tags',
        'is_premium',
        'is_published',
        'published_at',
        'author_id',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_premium' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getPreviewContentAttribute(): string
    {
        return Str::limit(strip_tags($this->content), $this->preview_chars);
    }
}
