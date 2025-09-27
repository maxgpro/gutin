<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'slug' => $this->getTranslation('slug', $locale),
            'excerpt' => $this->getTranslation('excerpt', $locale),
            'content' => $this->getTranslation('content', $locale),
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'views_count' => $this->views_count,
            'reading_time' => $this->reading_time,
            'user_id' => $this->when(isset($this->user_id), $this->user_id),
            'blog_category_id' => $this->when(isset($this->blog_category_id), $this->blog_category_id),
            'user' => $this->when($this->relationLoaded('user') || isset($this->user), function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'category' => $this->when($this->relationLoaded('category') || isset($this->category), function () use ($locale) {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->getTranslation('name', $locale),
                    'slug' => $this->category->getTranslation('slug', $locale),
                    'color' => $this->category->color,
                ];
            }),
        ];
    }
}
