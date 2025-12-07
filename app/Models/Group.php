<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Group::class, 'parent_id')
            ->orderBy('order');
    }

    public function visibleChildren(): HasMany
    {
        return $this->hasMany(Group::class, 'parent_id')
            ->where('is_visible', true)
            ->orderBy('order');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class)
            ->orderBy('order');
    }

    public function publishedArticles(): HasMany
    {
        return $this->hasMany(Article::class)
            ->where('status', 'published')
            ->orderBy('order');
    }

    public function allDescendants(): HasMany
    {
        return $this->children()->with('allDescendants');
    }

    public function getAncestors(): array
    {
        $ancestors = [];
        $current = $this->parent;

        while ($current) {
            array_unshift($ancestors, $current);
            $current = $current->parent;
        }

        return $ancestors;
    }

    public function getBreadcrumb(): array
    {
        $ancestors = $this->getAncestors();
        $ancestors[] = $this;

        return array_map(fn($group) => [
            'id' => $group->id,
            'name' => $group->name,
            'slug' => $group->slug,
        ], $ancestors);
    }

    public function getFullPath(): string
    {
        $ancestors = $this->getAncestors();
        $slugs = array_map(fn($g) => $g->slug, $ancestors);
        $slugs[] = $this->slug;

        return implode('/', $slugs);
    }

    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    public function hasChildren(): bool
    {
        return $this->children()->count() > 0;
    }

    public function getDepth(): int
    {
        return count($this->getAncestors());
    }
}
