# Per-Page Selector для пагинации

## Новая функциональность

Добавлен селектор для выбора количества отображаемых элементов на странице в компоненте пагинации.

## Техническая реализация

### Конфигурация

```typescript
// resources/js/config/pagination.ts
export const paginationConfig = {
    // ...existing config
    // Available per-page options
    perPageOptions: [10, 25, 50, 100] as const,
    // Default per page value
    defaultPerPage: 10,
    // Query param name for per page
    perPageParam: 'per_page',
} as const;
```

### Backend Support

**Request Validation:**
```php
// app/Http/Requests/Blog/BlogPostIndexRequest.php
public function rules(): array
{
    return [
        // ...existing rules
        'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
    ];
}
```

**Service Implementation:**
```php
// app/Services/BlogPostService.php
public function getFilteredPosts(BlogPostIndexRequest $request): LengthAwarePaginator
{
    $perPage = (int) $request->integer('per_page', 12);
    // ...
    return $query->paginate($perPage);
}
```

### Frontend Component

**Props:**
```typescript
const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    pageParam?: string;
    showPerPageSelector?: boolean; // новый проп для показа селектора
}>();
```

**Computed Model:**
```typescript
const perPageModel = computed({
    get: () => String(props.perPage),
    set: (value: string) => {
        const perPage = parseInt(value);
        changePerPage(perPage);
    }
});
```

**URL Navigation:**
```typescript
function changePerPage(perPage: number) {
    const url = new URL(window.location.href);
    url.searchParams.set(paginationConfig.perPageParam, String(perPage));
    // Сбрасываем на первую страницу при изменении количества элементов
    url.searchParams.set(pageParam.value, '1');

    router.get(url.pathname + '?' + url.searchParams.toString(), {}, {
        preserveState: true,
        preserveScroll: true,
        replace: false,
    });
}
```

## UI Компонент

### Layout
```vue
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <!-- Per Page Selector -->
    <div v-if="showPerPageSelector" class="flex items-center gap-2 text-sm text-muted-foreground">
        <span>Show</span>
        <Select v-model="perPageModel">
            <SelectTrigger class="w-20">
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                <SelectItem 
                    v-for="option in paginationConfig.perPageOptions" 
                    :key="option" 
                    :value="String(option)"
                >
                    {{ option }}
                </SelectItem>
            </SelectContent>
        </Select>
        <span>entries</span>
    </div>

    <!-- Pagination -->
    <Pagination>
        <!-- ...existing pagination content -->
    </Pagination>
</div>
```

## Использование

### Стандартное (без селектора)
```vue
<LaravelPaginationAdapter 
    :total="posts.total" 
    :perPage="posts.per_page" 
    :currentPage="posts.current_page"
/>
```

### С селектором per-page
```vue
<LaravelPaginationAdapter 
    :total="posts.total" 
    :perPage="posts.per_page" 
    :currentPage="posts.current_page"
    :showPerPageSelector="true"
/>
```

## Поведение

### ✅ URL Management
- Параметр `per_page` сохраняется в URL
- При изменении количества элементов происходит переход на страницу 1
- Остальные фильтры (поиск, категория, сортировка) сохраняются

### ✅ Validation
- Минимальное значение: 1 элемент
- Максимальное значение: 100 элементов  
- Только числовые значения принимаются

### ✅ Responsive Design
- На мобильных: селектор и пагинация в колонку
- На десктопе: селектор слева, пагинация справа

## Тестирование

```php
// tests/Feature/Blog/PerPageSelectorTest.php

public function test_per_page_parameter_changes_pagination_size(): void
{
    // Тест проверяет что параметр per_page корректно изменяет размер страницы
}

public function test_invalid_per_page_values_are_rejected(): void
{
    // Тест проверяет валидацию параметра per_page
}
```

## Преимущества

### 🎯 UX Improvement
- Пользователь может выбрать удобное количество элементов
- Меньше кликов по пагинации при большом количестве данных

### 🔧 Flexibility
- Селектор можно включить/выключить через проп
- Опции настраиваются в конфиге
- Сохранение состояния в URL

### 🚀 Performance
- При выборе большего количества элементов - меньше запросов к серверу
- Эффективная работа с большими списками данных

Теперь пользователи могут выбирать оптимальное количество постов для просмотра: 10, 25, 50 или 100 элементов на странице!