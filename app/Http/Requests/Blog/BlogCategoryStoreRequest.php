<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\BlogCategory;
use Illuminate\Validation\Rule;
use App\Rules\UniqueJsonTranslation;

class BlogCategoryStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BlogCategory::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255', new UniqueJsonTranslation(BlogCategory::class, 'slug', null, null, true)],
            'description' => ['nullable','string'],
            'color' => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['boolean'],
        ];
    }
}
