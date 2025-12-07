<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Article extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'project_id',
        'group_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'table_of_contents',
        'featured_image',
        'order',
        'status',
        'published_at',
        'views',
        'helpful_count',
        'not_helpful_count',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'table_of_contents' => 'array',
            'meta' => 'array',
            'published_at' => 'datetime',
            'order' => 'integer',
            'views' => 'integer',
            'helpful_count' => 'integer',
            'not_helpful_count' => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured_image')
            ->singleFile();

        $this->addMediaCollection('images');
        $this->addMediaCollection('videos');
        $this->addMediaCollection('attachments');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(ArticleFeedback::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function publish(): void
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => 'archived']);
    }

    public function unpublish(): void
    {
        $this->update([
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function markHelpful(): void
    {
        $this->increment('helpful_count');
    }

    public function markNotHelpful(): void
    {
        $this->increment('not_helpful_count');
    }

    public function getHelpfulPercentageAttribute(): ?float
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return null;
        }

        return round(($this->helpful_count / $total) * 100, 1);
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }

    public function generateTableOfContents(): array
    {
        preg_match_all('/<h([2-4])[^>]*>(.*?)<\/h\1>/i', $this->content, $matches, PREG_SET_ORDER);

        $toc = [];
        foreach ($matches as $match) {
            $level = (int) $match[1];
            $text = strip_tags($match[2]);
            $id = Str::slug($text);

            $toc[] = [
                'level' => $level,
                'text' => $text,
                'id' => $id,
            ];
        }

        return $toc;
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];

        if ($this->group) {
            $breadcrumb = $this->group->getBreadcrumb();
        }

        $breadcrumb[] = [
            'id' => $this->id,
            'name' => $this->title,
            'slug' => $this->slug,
            'type' => 'article',
        ];

        return $breadcrumb;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSearch($query, string $term)
    {
        return $query->whereFullText(['title', 'content'], $term);
    }
}
