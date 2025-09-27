<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validates uniqueness of a JSONB translation for a specific locale (PostgreSQL-only).
 */
class UniqueJsonTranslation implements ValidationRule
{
    /** @param class-string $modelClass */
    public function __construct(
        protected string $modelClass,
        protected string $column,
        protected ?string $locale = null,
        protected int|string|null $ignoreId = null,
        protected string $idColumn = 'id',
    ) {
        $this->locale = $this->locale ?: app()->getLocale();
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return; // nothing to validate
        }

        $query = ($this->modelClass)::query();

        if ($this->ignoreId !== null) {
            $query->where($this->idColumn, '!=', $this->ignoreId);
        }

        // PostgreSQL JSONB operator to extract text value for the given locale
        $query->whereRaw("{$this->column}->>? = ?", [$this->locale, $value]);

        if ($query->exists()) {
            $fail(trans('validation.unique', ['attribute' => $attribute]));
        }
    }
}
