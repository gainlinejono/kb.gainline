<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function searchQueries(): HasMany
    {
        return $this->hasMany(SearchQuery::class);
    }

    public function hasProjectAccess(Project $project): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->projects()->where('projects.id', $project->id)->exists();
    }

    public function getProjectRole(Project $project): ?string
    {
        $membership = $this->projectMemberships()
            ->where('project_id', $project->id)
            ->first();

        return $membership?->role;
    }

    public function canEditProject(Project $project): bool
    {
        if ($this->is_admin) {
            return true;
        }

        $role = $this->getProjectRole($project);
        return in_array($role, ['editor', 'admin']);
    }

    public function canAdminProject(Project $project): bool
    {
        if ($this->is_admin) {
            return true;
        }

        return $this->getProjectRole($project) === 'admin';
    }
}
