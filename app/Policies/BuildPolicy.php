<?php

namespace App\Policies;

use App\Models\Build;
use App\Models\User;

class BuildPolicy
{
    public function view(User $user, Build $build): bool
    {
        if ($build->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($build->shares()->where('user_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    public function update(User $user, Build $build): bool
    {
        $role = $build->userRole($user);

        return in_array($role, ['admin', 'editor']);
    }

    public function delete(User $user, Build $build): bool
    {
        return $user->is_admin || $build->userRole($user) === 'admin';
    }

    public function share(User $user, Build $build): bool
    {
        return in_array($build->userRole($user), ['admin', 'editor']);
    }

    public function addViewer(User $user, Build $build): bool
    {
        return in_array($build->userRole($user), ['admin', 'editor']);
    }

    /**
     * Granular Permissions
     */

    public function editGeometry(User $user, Build $build): bool
    {
        // Only admin and editor can edit geometry (place, move, delete parts)
        $role = $build->userRole($user);
        return in_array($role, ['admin', 'editor']);
    }

    public function deleteParts(User $user, Build $build): bool
    {
        // Same as editGeometry - admin and editor can delete
        return $this->editGeometry($user, $build);
    }

    public function manageMembers(User $user, Build $build): bool
    {
        // Only admin can add/remove members and change roles
        return $build->userRole($user) === 'admin';
    }

    public function addComments(User $user, Build $build): bool
    {
        // All roles can add comments
        $role = $build->userRole($user);
        return in_array($role, ['admin', 'editor', 'viewer']);
    }

    public function exportBuild(User $user, Build $build): bool
    {
        // Admin and editor can export
        $role = $build->userRole($user);
        return in_array($role, ['admin', 'editor']);
    }

    public function changeSettings(User $user, Build $build): bool
    {
        // Only admin can change build settings (name, visibility, etc.)
        return $build->userRole($user) === 'admin';
    }

    public function viewBuild(User $user, Build $build): bool
    {
        // All roles can view
        $role = $build->userRole($user);
        return in_array($role, ['admin', 'editor', 'viewer']);
    }

    /**
     * Get all permissions for a user on this build
     */
    public function getPermissions(User $user, Build $build): array
    {
        return [
            'can_edit_geometry' => $this->editGeometry($user, $build),
            'can_delete_parts' => $this->deleteParts($user, $build),
            'can_manage_members' => $this->manageMembers($user, $build),
            'can_add_comments' => $this->addComments($user, $build),
            'can_export_build' => $this->exportBuild($user, $build),
            'can_change_settings' => $this->changeSettings($user, $build),
            'can_view_build' => $this->viewBuild($user, $build),
            'role' => $build->userRole($user) ?? 'viewer',
        ];
    }
}
