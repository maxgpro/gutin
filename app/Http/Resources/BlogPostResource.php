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
        // Получаем локализованный контент для расчёта времени чтения
        $localizedContent = (string) ($this->getTranslation('content', $locale) ?? '');
        $readingTime = $this->estimateReadingTime($localizedContent);

        return [
            'id' => $this->id,
            'title' => $this->getTranslation('title', $locale),
            'slug' => $this->getTranslation('slug', $locale),
            'base_slug' => $this->getLocalizedBaseSlug($locale),
            'excerpt' => $this->getTranslation('excerpt', $locale),
            'content' => $this->getTranslation('content', $locale),
            'featured_image' => $this->featured_image,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'views_count' => $this->views_count,
            'reading_time' => $readingTime,
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
                    'title' => $this->category->getTranslation('title', $locale),
                    'slug' => $this->category->getTranslation('slug', $locale),
                    'color' => $this->category->color,
                ];
            }),
        ];
    }

        /**
         * Примерная оценка времени чтения (в минутах) по локализованному HTML-содержимому.
         * Используем ~200 слов в минуту. Поддержка Unicode-слов через \pL.
         */
        protected function estimateReadingTime(string $html): int
        {
            if ($html === '') {
                return 1; // минимальное значение
            }
            // Удаляем теги и считаем количество слов в Unicode
            $text = strip_tags($html);
            if ($text === '') {
                return 1;
            }
            preg_match_all('/\p{L}+/u', $text, $matches);
            $words = isset($matches[0]) ? count($matches[0]) : 0;
            if ($words === 0) {
                // fallback: считаем по символам, предполагая ~6 символов на слово
                $chars = mb_strlen($text);
                $words = (int) ceil($chars / 6.0);
            }
            return max(1, (int) ceil($words / 200));
        }
}
