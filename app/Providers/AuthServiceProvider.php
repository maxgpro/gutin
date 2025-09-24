<?php

namespace App\Providers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Policies\BlogPostPolicy;
use App\Policies\BlogCategoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        BlogPost::class => BlogPostPolicy::class,
        BlogCategory::class => BlogCategoryPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
