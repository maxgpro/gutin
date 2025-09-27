<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\BlogPost;
use Illuminate\Validation\Rule;
use App\Rules\UniqueJsonTranslation;

class BlogPostStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', BlogPost::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable','string','max:255', new UniqueJsonTranslation(BlogPost::class, 'slug')],
            'blog_category_id' => ['required', 'exists:blog_categories,id'],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'featured_image' => ['nullable', 'string'],
            'meta_data' => ['nullable', 'array'],
            'status' => ['required', 'in:' . implode(',', BlogPost::STATUSES)],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
