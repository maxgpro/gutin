<?php

namespace App\Http\Requests\Blog;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;

class BlogPostIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Все могут просматривать список постов
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:' . implode(',', BlogPost::STATUSES)],
            'category' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
            'sort_by' => ['nullable', 'string', 'in:published_at,created_at'],
            'sort_order' => ['nullable', 'string', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Недопустимый статус поста.',
            'sort_by.in' => 'Сортировка возможна только по дате публикации или создания.',
            'sort_order.in' => 'Порядок сортировки может быть только по возрастанию (asc) или убыванию (desc).',
            'per_page.min' => 'Минимальное количество постов на странице: 1.',
            'per_page.max' => 'Максимальное количество постов на странице: 100.',
        ];
    }
}