<?php

namespace App\Models\Traits;

trait OrdersByLocalizedTitle
{
    /**
     * Scope for ordering by localized title (PostgreSQL JSONB)
     */
    public function scopeOrderByLocalizedTitle($query, ?string $locale = null, string $direction = 'asc')
    {
        $locale = $locale ?: app()->getLocale();
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        return $query->orderByRaw("title->>? {$direction}", [$locale]);
    }
}
