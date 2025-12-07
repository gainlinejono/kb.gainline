<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchQuery extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'user_id',
        'query',
        'results_count',
        'has_clicked_result',
    ];

    protected function casts(): array
    {
        return [
            'results_count' => 'integer',
            'has_clicked_result' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getPopularQueries(int $projectId, int $limit = 10): array
    {
        return static::where('project_id', $projectId)
            ->where('results_count', '>', 0)
            ->select('query')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('query')
            ->toArray();
    }

    public static function getFailedQueries(int $projectId, int $limit = 10): array
    {
        return static::where('project_id', $projectId)
            ->where('results_count', 0)
            ->select('query')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->pluck('query')
            ->toArray();
    }
}
