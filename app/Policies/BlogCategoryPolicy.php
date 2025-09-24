<?php

namespace App\Policies;

use App\Models\BlogCategory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Все могут просматривать список категорий
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, BlogCategory $blogCategory): bool
    {
        // Активные категории может видеть любой
        if ($blogCategory->is_active) {
            return true;
        }

        // Неактивные категории могут видеть только админы
        return $user && $user->is_admin;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Только админы могут создавать категории
        return $user->is_admin;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BlogCategory $blogCategory): bool
    {
        // Только админы могут редактировать категории
        return $user->is_admin;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BlogCategory $blogCategory): bool
    {
        // Только админы могут удалять категории
        return $user->is_admin;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BlogCategory $blogCategory): bool
    {
        return $user->is_admin;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BlogCategory $blogCategory): bool
    {
        return $user->is_admin;
    }
}
