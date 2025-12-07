<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240',
            'article_id' => 'nullable|exists:articles,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        if ($request->article_id) {
            $model = Article::findOrFail($request->article_id);
        } elseif ($request->project_id) {
            $model = Project::findOrFail($request->project_id);
        } else {
            return response()->json(['error' => 'No model specified'], 400);
        }

        $media = $model->addMediaFromRequest('image')
            ->toMediaCollection('images');

        return response()->json([
            'url' => $media->getUrl(),
            'id' => $media->id,
        ]);
    }

    public function uploadVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|mimetypes:video/mp4,video/webm,video/ogg|max:102400',
            'article_id' => 'nullable|exists:articles,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        if ($request->article_id) {
            $model = Article::findOrFail($request->article_id);
        } elseif ($request->project_id) {
            $model = Project::findOrFail($request->project_id);
        } else {
            return response()->json(['error' => 'No model specified'], 400);
        }

        $media = $model->addMediaFromRequest('video')
            ->toMediaCollection('videos');

        return response()->json([
            'url' => $media->getUrl(),
            'id' => $media->id,
        ]);
    }

    public function uploadFile(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200',
            'article_id' => 'nullable|exists:articles,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        if ($request->article_id) {
            $model = Article::findOrFail($request->article_id);
        } elseif ($request->project_id) {
            $model = Project::findOrFail($request->project_id);
        } else {
            return response()->json(['error' => 'No model specified'], 400);
        }

        $media = $model->addMediaFromRequest('file')
            ->toMediaCollection('attachments');

        return response()->json([
            'url' => $media->getUrl(),
            'id' => $media->id,
            'name' => $media->file_name,
            'size' => $media->size,
        ]);
    }

    public function destroy(Media $media): JsonResponse
    {
        $media->delete();

        return response()->json(['success' => true]);
    }

    public function getProjectMedia(Project $project): JsonResponse
    {
        $images = $project->getMedia('images')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
        ]);

        $videos = $project->getMedia('videos')->map(fn ($media) => [
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
        ]);

        return response()->json([
            'images' => $images,
            'videos' => $videos,
        ]);
    }
}
