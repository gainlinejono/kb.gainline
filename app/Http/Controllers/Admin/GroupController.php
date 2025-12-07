<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class GroupController extends Controller
{
    public function index(Request $request, Project $project): Response
    {
        $groups = $project->rootGroups()
            ->with('children.children')
            ->withCount('articles')
            ->get();

        return Inertia::render('Admin/Groups/Index', [
            'project' => $project,
            'groups' => $groups,
        ]);
    }

    public function create(Project $project): Response
    {
        $parentGroups = $project->groups()
            ->whereNull('parent_id')
            ->orWhere(function ($query) use ($project) {
                $query->where('project_id', $project->id)
                    ->whereNotNull('parent_id');
            })
            ->get();

        return Inertia::render('Admin/Groups/Create', [
            'project' => $project,
            'parentGroups' => $parentGroups,
        ]);
    }

    public function store(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:groups,id',
            'order' => 'nullable|integer',
            'is_visible' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['project_id'] = $project->id;

        if (!isset($validated['order'])) {
            $validated['order'] = Group::where('project_id', $project->id)
                ->where('parent_id', $validated['parent_id'] ?? null)
                ->max('order') + 1;
        }

        Group::create($validated);

        return redirect()->route('admin.projects.groups.index', $project)
            ->with('success', 'Group created successfully.');
    }

    public function edit(Project $project, Group $group): Response
    {
        $parentGroups = $project->groups()
            ->where('id', '!=', $group->id)
            ->whereNotIn('id', $this->getDescendantIds($group))
            ->get();

        return Inertia::render('Admin/Groups/Edit', [
            'project' => $project,
            'group' => $group,
            'parentGroups' => $parentGroups,
        ]);
    }

    public function update(Request $request, Project $project, Group $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:groups,id',
            'order' => 'nullable|integer',
            'is_visible' => 'boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        if (isset($validated['parent_id']) && $validated['parent_id'] == $group->id) {
            return back()->with('error', 'A group cannot be its own parent.');
        }

        $group->update($validated);

        return redirect()->route('admin.projects.groups.index', $project)
            ->with('success', 'Group updated successfully.');
    }

    public function destroy(Project $project, Group $group): RedirectResponse
    {
        $group->articles()->update(['group_id' => null]);
        $group->children()->update(['parent_id' => $group->parent_id]);
        $group->delete();

        return redirect()->route('admin.projects.groups.index', $project)
            ->with('success', 'Group deleted successfully.');
    }

    public function reorder(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'groups' => 'required|array',
            'groups.*.id' => 'required|exists:groups,id',
            'groups.*.order' => 'required|integer',
            'groups.*.parent_id' => 'nullable|exists:groups,id',
        ]);

        foreach ($validated['groups'] as $groupData) {
            Group::where('id', $groupData['id'])
                ->where('project_id', $project->id)
                ->update([
                    'order' => $groupData['order'],
                    'parent_id' => $groupData['parent_id'] ?? null,
                ]);
        }

        return back()->with('success', 'Groups reordered successfully.');
    }

    protected function getDescendantIds(Group $group): array
    {
        $ids = [];
        $children = $group->children;

        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }

        return $ids;
    }
}
