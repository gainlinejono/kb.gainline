<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Kb\KnowledgebaseController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('kb.index');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('kb')->name('kb.')->group(function () {
        Route::get('/', [KnowledgebaseController::class, 'index'])->name('index');
        Route::get('/{project}', [KnowledgebaseController::class, 'show'])->name('show');
        Route::get('/{project}/search', [KnowledgebaseController::class, 'search'])->name('search');
        Route::get('/{project}/suggestions', [KnowledgebaseController::class, 'suggestions'])->name('suggestions');
        Route::get('/{project}/article/{article}', [KnowledgebaseController::class, 'article'])->name('article');
        Route::post('/{project}/article/{article}/feedback', [KnowledgebaseController::class, 'feedback'])->name('feedback');
        Route::get('/{project}/group/{path}', [KnowledgebaseController::class, 'group'])
            ->where('path', '.*')
            ->name('group');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('users', AdminUserController::class);

        Route::resource('projects', AdminProjectController::class);
        Route::post('/projects/{project}/members', [AdminProjectController::class, 'addMember'])->name('projects.members.add');
        Route::patch('/projects/{project}/members/{user}', [AdminProjectController::class, 'updateMember'])->name('projects.members.update');
        Route::delete('/projects/{project}/members/{user}', [AdminProjectController::class, 'removeMember'])->name('projects.members.remove');

        Route::prefix('projects/{project}')->name('projects.')->group(function () {
            Route::resource('groups', AdminGroupController::class)->except(['show']);
            Route::post('/groups/reorder', [AdminGroupController::class, 'reorder'])->name('groups.reorder');

            Route::resource('articles', AdminArticleController::class);
            Route::post('/articles/{article}/publish', [AdminArticleController::class, 'publish'])->name('articles.publish');
            Route::post('/articles/{article}/unpublish', [AdminArticleController::class, 'unpublish'])->name('articles.unpublish');
            Route::post('/articles/reorder', [AdminArticleController::class, 'reorder'])->name('articles.reorder');
        });

        Route::post('/media/image', [AdminMediaController::class, 'uploadImage'])->name('media.image');
        Route::post('/media/video', [AdminMediaController::class, 'uploadVideo'])->name('media.video');
        Route::post('/media/file', [AdminMediaController::class, 'uploadFile'])->name('media.file');
        Route::delete('/media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/projects/{project}/media', [AdminMediaController::class, 'getProjectMedia'])->name('projects.media');
    });
});

require __DIR__.'/auth.php';
