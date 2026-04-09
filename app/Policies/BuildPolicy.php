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
}
