<?php

namespace App\Http\Requests\Blog;

use App\Models\BlogPost;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class BlogPostIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Все могут просматривать список постов
        // Но фильтрация по статусам - только для админов
        if ($this->has('status')) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            
            // Если статус не 'published', требуется админ
            if ($this->status !== BlogPost::STATUS_PUBLISHED && (!$user || !$user->isAdmin())) {
                return false;
            }
        }
        
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', 'in:' . implode(',', BlogPost::STATUSES)],
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
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