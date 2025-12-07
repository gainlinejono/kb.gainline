<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'projects' => Project::count(),
            'articles' => Article::count(),
            'published_articles' => Article::where('status', 'published')->count(),
            'users' => User::count(),
            'total_views' => Article::sum('views'),
        ];

        $recentArticles = Article::with(['author', 'project'])
            ->latest()
            ->take(5)
            ->get();

        $recentProjects = Project::withCount('articles')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentArticles' => $recentArticles,
            'recentProjects' => $recentProjects,
        ]);
    }
}
