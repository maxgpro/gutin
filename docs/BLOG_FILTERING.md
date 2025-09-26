# Blog Post Filtering and Sorting Features

## Overview
Система блога теперь поддерживает комплексную фильтрацию и сортировку постов через UI и API.

## Features

### 1. Status Filtering
- **Статусы**: `draft`, `published`, `archived`
- **UI**: Select компонент для выбора статуса
- **API**: `GET /blog/posts?status=draft`
- **По умолчанию**: показываются только опубликованные посты

### 2. Date Sorting
- **Поля сортировки**: `published_at`, `created_at`
- **Направления**: `asc` (старые первые), `desc` (новые первые)
- **UI**: Отдельные селекты для поля и направления сортировки
- **API**: `GET /blog/posts?sort_by=created_at&sort_order=asc`

### 3. Category Filtering
- **UI**: Select с категориями + опция "All Categories"
- **API**: `GET /blog/posts?category=technology`

### 4. Search
- **UI**: Input поле с debounce (300ms)
- **API**: `GET /blog/posts?search=laravel`
- **Поля поиска**: `title`, `content`, `excerpt`

## API Parameters

### Available Parameters
```http
GET /blog/posts?status=published&category=tech&sort_by=created_at&sort_order=desc&search=laravel&per_page=12
```

| Parameter    | Type    | Values                           | Default        |
|--------------|---------|----------------------------------|----------------|
| `status`     | string  | `draft`, `published`, `archived` | `published`    |
| `category`   | string  | Category slug                    | -              |
| `sort_by`    | string  | `published_at`, `created_at`     | `published_at` |
| `sort_order` | string  | `asc`, `desc`                    | `desc`         |
| `search`     | string  | Search query                     | -              |
| `per_page`   | integer | 1-100                            | 12             |

### Validation
- Все параметры валидируются через `BlogPostIndexRequest`
- Неверные значения возвращают 422 ошибку с описанием

## UI Components

### 1. Filter Controls
- **Category Select**: "All Categories" + список активных категорий
- **Status Select**: "All Statuses" + draft/published/archived
- **Sort Controls**: Отдельные селекты для поля и направления

### 2. Additional Features
- **Clear Filters Button**: Появляется при наличии активных фильтров
- **Results Counter**: "Showing X to Y of Z results"
- **Status Badges**: Визуальные индикаторы статуса на карточках постов

### 3. Responsive Design
- Mobile-first подход
- Адаптивная сетка фильтров
- Гибкое расположение элементов

## Technical Implementation

### Backend
- **Controller**: `BlogPostController@index()` с `BlogPostIndexRequest`
- **Model Scopes**: `published()`, `draft()` методы в `BlogPost`
- **Constants**: `BlogPost::STATUS_*` константы для типобезопасности

### Frontend
- **Vue 3 + TypeScript**: Типизированные интерфейсы и props
- **Reactive Forms**: `ref()` формы с `watch()` для автообновления
- **Debounced Search**: `useDebounceFn()` для оптимизации запросов
- **URL State**: Синхронизация фильтров с URL через Inertia

### Testing
- **Pest PHP**: Комплексные тесты фильтрации и сортировки
- **Validation Tests**: Проверка обработки неверных параметров
- **Edge Cases**: Тестирование граничных случаев

## Usage Examples

### Basic Filtering
```typescript
// Show only draft posts
router.get('/blog/posts?status=draft');

// Show tech posts sorted by creation date
router.get('/blog/posts?category=technology&sort_by=created_at&sort_order=asc');
```

### Programmatic Access
```php
// Controller example
$posts = BlogPost::published()
    ->when($status, fn($q) => $q->where('status', $status))
    ->when($category, fn($q) => $q->whereHas('category', fn($q) => $q->where('slug', $category)))
    ->latest('published_at')
    ->paginate(12);
```