<?php

namespace App\Http\Controllers\Kb;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use App\Models\SearchQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgebaseController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $projects = $user->is_admin
            ? Project::where('is_active', true)->get()
            : $user->projects()->where('is_active', true)->get();

        return Inertia::render('Kb/Index', [
            'projects' => $projects,
        ]);
    }

    public function show(Project $project): Response
    {
        $this->authorizeProjectAccess($project);

        $project->load(['rootGroups' => function ($query) {
            $query->where('is_visible', true)
                ->orderBy('order')
                ->with(['visibleChildren' => function ($q) {
                    $q->orderBy('order');
                }, 'publishedArticles']);
        }]);

        $featuredArticles = $project->articles()
            ->where('status', 'published')
            ->orderByDesc('views')
            ->take(6)
            ->get();

        $recentArticles = $project->articles()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->take(5)
            ->get();

        return Inertia::render('Kb/Project/Show', [
            'project' => $project,
            'featuredArticles' => $featuredArticles,
            'recentArticles' => $recentArticles,
            'navigation' => $this->buildNavigation($project),
        ]);
    }

    public function article(Project $project, Article $article): Response
    {
        $this->authorizeProjectAccess($project);

        if ($article->project_id !== $project->id) {
            abort(404);
        }

        if ($article->status !== 'published' && !auth()->user()->canEditProject($project)) {
            abort(404);
        }

        $article->incrementViews();
        $article->load(['author', 'group']);

        $relatedArticles = $project->articles()
            ->where('status', 'published')
            ->where('id', '!=', $article->id)
            ->when($article->group_id, fn ($q) => $q->where('group_id', $article->group_id))
            ->take(3)
            ->get();

        $prevArticle = $this->getPreviousArticle($project, $article);
        $nextArticle = $this->getNextArticle($project, $article);

        return Inertia::render('Kb/Article/Show', [
            'project' => $project,
            'article' => $article,
            'relatedArticles' => $relatedArticles,
            'prevArticle' => $prevArticle,
            'nextArticle' => $nextArticle,
            'navigation' => $this->buildNavigation($project),
            'breadcrumb' => $article->getBreadcrumb(),
        ]);
    }

    public function group(Project $project, string $groupPath): Response
    {
        $this->authorizeProjectAccess($project);

        $slugs = explode('/', $groupPath);
        $group = null;

        foreach ($slugs as $slug) {
            $query = $project->groups()->where('slug', $slug);

            if ($group) {
                $query->where('parent_id', $group->id);
            } else {
                $query->whereNull('parent_id');
            }

            $group = $query->firstOrFail();
        }

        $group->load(['visibleChildren', 'publishedArticles']);

        return Inertia::render('Kb/Group/Show', [
            'project' => $project,
            'group' => $group,
            'navigation' => $this->buildNavigation($project),
            'breadcrumb' => $group->getBreadcrumb(),
        ]);
    }

    public function search(Request $request, Project $project): JsonResponse
    {
        $this->authorizeProjectAccess($project);

        $query = $request->input('q');

        if (empty($query) || strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $articles = $project->articles()
            ->where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', '%' . $query . '%')
                    ->orWhere('content', 'like', '%' . $query . '%')
                    ->orWhere('excerpt', 'like', '%' . $query . '%');
            })
            ->take(10)
            ->get(['id', 'title', 'slug', 'excerpt', 'group_id']);

        SearchQuery::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'query' => $query,
            'results_count' => $articles->count(),
        ]);

        return response()->json([
            'results' => $articles->map(fn ($article) => [
                'id' => $article->id,
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt ?? $this->generateExcerpt($article->content),
                'url' => route('kb.article', [$project->slug, $article->slug]),
            ]),
        ]);
    }

    public function suggestions(Project $project): JsonResponse
    {
        $this->authorizeProjectAccess($project);

        $popularQueries = SearchQuery::getPopularQueries($project->id, 5);

        $popularArticles = $project->articles()
            ->where('status', 'published')
            ->orderByDesc('views')
            ->take(5)
            ->get(['id', 'title', 'slug']);

        return response()->json([
            'popularQueries' => $popularQueries,
            'popularArticles' => $popularArticles,
        ]);
    }

    public function feedback(Request $request, Project $project, Article $article): JsonResponse
    {
        $this->authorizeProjectAccess($project);

        $validated = $request->validate([
            'is_helpful' => 'required|boolean',
            'comment' => 'nullable|string|max:1000',
        ]);

        $article->feedback()->create([
            'user_id' => auth()->id(),
            'is_helpful' => $validated['is_helpful'],
            'comment' => $validated['comment'] ?? null,
        ]);

        if ($validated['is_helpful']) {
            $article->markHelpful();
        } else {
            $article->markNotHelpful();
        }

        return response()->json(['success' => true]);
    }

    protected function authorizeProjectAccess(Project $project): void
    {
        if (!auth()->user()->hasProjectAccess($project)) {
            abort(403, 'You do not have access to this project.');
        }
    }

    protected function buildNavigation(Project $project): array
    {
        $groups = $project->rootGroups()
            ->where('is_visible', true)
            ->orderBy('order')
            ->with(['visibleChildren' => function ($q) {
                $q->orderBy('order')->with('visibleChildren');
            }, 'publishedArticles'])
            ->get();

        return $this->formatNavigation($groups);
    }

    protected function formatNavigation($groups): array
    {
        return $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'slug' => $group->slug,
                'icon' => $group->icon,
                'path' => $group->getFullPath(),
                'children' => $this->formatNavigation($group->visibleChildren),
                'articles' => $group->publishedArticles->map(fn ($article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'slug' => $article->slug,
                ]),
            ];
        })->toArray();
    }

    protected function generateExcerpt(string $content, int $length = 150): string
    {
        $text = strip_tags($content);
        return strlen($text) > $length
            ? substr($text, 0, $length) . '...'
            : $text;
    }

    protected function getPreviousArticle(Project $project, Article $article): ?array
    {
        $prev = $project->articles()
            ->where('status', 'published')
            ->where('order', '<', $article->order)
            ->when($article->group_id, fn ($q) => $q->where('group_id', $article->group_id))
            ->orderByDesc('order')
            ->first(['id', 'title', 'slug']);

        return $prev ? ['title' => $prev->title, 'slug' => $prev->slug] : null;
    }

    protected function getNextArticle(Project $project, Article $article): ?array
    {
        $next = $project->articles()
            ->where('status', 'published')
            ->where('order', '>', $article->order)
            ->when($article->group_id, fn ($q) => $q->where('group_id', $article->group_id))
            ->orderBy('order')
            ->first(['id', 'title', 'slug']);

        return $next ? ['title' => $next->title, 'slug' => $next->slug] : null;
    }
}
