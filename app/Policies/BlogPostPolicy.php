<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BlogPostPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Все могут просматривать список постов
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, BlogPost $blogPost): bool
    {
        // Опубликованные посты может видеть любой
        if ($blogPost->isPublished()) {
            return true;
        }

        // Черновики могут видеть только авторы и админы
        if (!$user) {
            return false;
        }

        return $this->isAuthorOrAdmin($user, $blogPost);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Авторизованные пользователи могут создавать посты
        // return true;

        // Только админы могут создавать посты
        return $user?->isAdmin() ?? false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BlogPost $blogPost): bool
    {
        return $this->isAuthorOrAdmin($user, $blogPost);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $this->isAuthorOrAdmin($user, $blogPost);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BlogPost $blogPost): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BlogPost $blogPost): bool
    {
        return $user->isAdmin();
    }

    /**
     * Check if user is author or admin
     */
    private function isAuthorOrAdmin(User $user, BlogPost $blogPost): bool
    {
        return $user->id === $blogPost->user_id || $user->isAdmin();
    }
}
