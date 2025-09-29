<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\BlogCategory;
use Illuminate\Validation\Rule;
use App\Rules\UniqueJsonTranslation;

class BlogCategoryUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var BlogCategory|null $category */
        $category = $this->route('category');
        return $category && ($this->user()?->can('update', $category) ?? false);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $id = $category?->id;
        return [
            'title' => ['required','string','max:255'],
            'slug' => ['nullable','string','max:255', new UniqueJsonTranslation(BlogCategory::class, 'slug', null, $id, true)],
            'description' => ['nullable','string'],
            'color' => ['nullable','string','regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['boolean'],
        ];
    }
}
