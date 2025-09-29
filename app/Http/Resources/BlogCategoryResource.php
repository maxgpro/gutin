<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
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
            'base_slug' => $this->getLocalizedBaseSlug($locale),
            'description' => $this->getTranslation('description', $locale),
            'color' => $this->color,
            'is_active' => $this->is_active,
            'posts_count' => $this->when(isset($this->posts_count), $this->posts_count),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}