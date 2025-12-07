<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(Request $request, Project $project): Response
    {
        $query = $project->articles()->with(['author', 'group']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                    ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('group_id') && $request->group_id) {
            $query->where('group_id', $request->group_id);
        }

        $articles = $query->latest()->paginate(15);

        $groups = $project->groups()->get(['id', 'name', 'parent_id']);

        return Inertia::render('Admin/Articles/Index', [
            'project' => $project,
            'articles' => $articles,
            'groups' => $groups,
            'filters' => $request->only('search', 'status', 'group_id'),
        ]);
    }

    public function create(Project $project): Response
    {
        $groups = $project->groups()
            ->with('parent')
            ->get();

        return Inertia::render('Admin/Articles/Create', [
            'project' => $project,
            'groups' => $groups,
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'group_id' => 'nullable|exists:groups,id',
            'status' => 'required|in:draft,published,archived',
            'order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        $validated['project_id'] = $project->id;
        $validated['author_id'] = auth()->id();

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        if (!isset($validated['order'])) {
            $validated['order'] = Article::where('project_id', $project->id)
                ->where('group_id', $validated['group_id'] ?? null)
                ->max('order') + 1;
        }

        $article = Article::create($validated);

        $article->update([
            'table_of_contents' => $article->generateTableOfContents(),
        ]);

        if ($request->hasFile('featured_image')) {
            $article->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        return redirect()->route('admin.projects.articles.index', $project)
            ->with('success', 'Article created successfully.');
    }

    public function show(Project $project, Article $article): Response
    {
        $article->load(['author', 'group', 'feedback']);

        return Inertia::render('Admin/Articles/Show', [
            'project' => $project,
            'article' => $article,
        ]);
    }

    public function edit(Project $project, Article $article): Response
    {
        $groups = $project->groups()
            ->with('parent')
            ->get();

        return Inertia::render('Admin/Articles/Edit', [
            'project' => $project,
            'article' => $article,
            'groups' => $groups,
        ]);
    }

    public function update(Request $request, Project $project, Article $article): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'group_id' => 'nullable|exists:groups,id',
            'status' => 'required|in:draft,published,archived',
            'order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($validated['status'] === 'published' && $article->status !== 'published') {
            $validated['published_at'] = now();
        }

        $article->update($validated);

        $article->update([
            'table_of_contents' => $article->generateTableOfContents(),
        ]);

        if ($request->hasFile('featured_image')) {
            $article->clearMediaCollection('featured_image');
            $article->addMediaFromRequest('featured_image')
                ->toMediaCollection('featured_image');
        }

        return redirect()->route('admin.projects.articles.index', $project)
            ->with('success', 'Article updated successfully.');
    }

    public function destroy(Project $project, Article $article): RedirectResponse
    {
        $article->delete();

        return redirect()->route('admin.projects.articles.index', $project)
            ->with('success', 'Article deleted successfully.');
    }

    public function publish(Project $project, Article $article): RedirectResponse
    {
        $article->publish();

        return back()->with('success', 'Article published successfully.');
    }

    public function unpublish(Project $project, Article $article): RedirectResponse
    {
        $article->unpublish();

        return back()->with('success', 'Article unpublished successfully.');
    }

    public function reorder(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'articles' => 'required|array',
            'articles.*.id' => 'required|exists:articles,id',
            'articles.*.order' => 'required|integer',
            'articles.*.group_id' => 'nullable|exists:groups,id',
        ]);

        foreach ($validated['articles'] as $articleData) {
            Article::where('id', $articleData['id'])
                ->where('project_id', $project->id)
                ->update([
                    'order' => $articleData['order'],
                    'group_id' => $articleData['group_id'] ?? null,
                ]);
        }

        return back()->with('success', 'Articles reordered successfully.');
    }
}
