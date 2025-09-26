# Система прав доступа к блог-постам

## Обзор функциональности

Реализована роль-ориентированная система доступа к блог-постам, где администраторы имеют полный доступ ко всем постам и функциям фильтрации, а обычные пользователи и гости видят только опубликованные материалы.

## Роли пользователей

### Администратор (`role: admin`)
- ✅ Видит **все посты** (published, draft, archived)
- ✅ Может **фильтровать по статусу** 
- ✅ Имеет доступ к селектору статусов в UI
- ✅ Может создавать, редактировать и удалять посты

### Обычный пользователь (`role: user/mentee`)  
- ✅ Видит **только опубликованные посты**
- ❌ Не может фильтровать по статусу
- ❌ Селектор статусов скрыт в UI
- ❌ Не может создавать посты (по политике)

### Гость (неаутентифицированный)
- ✅ Видит **только опубликованные посты** 
- ❌ Не может фильтровать по статусу
- ❌ Селектор статусов скрыт в UI
- ❌ Не может создавать посты

## Техническая реализация

### Backend Logic

#### BlogPostService
```php
protected function applyStatusFilter(Builder $query, BlogPostIndexRequest $request): void
{
    $isAdmin = $this->isUserAdmin();
    
    if ($isAdmin && $request->has('status') && in_array($request->status, BlogPost::STATUSES)) {
        // Администратор может фильтровать по любому статусу
        $query->where('status', $request->status);
    } elseif (!$isAdmin) {
        // Не-администраторы видят только опубликованные посты
        $query->published();
    }
    // Администратор без фильтра видит все посты
}

protected function isUserAdmin(): bool
{
    $user = Auth::user();
    if (!$user) return false;
    
    return DB::table('user_roles')
        ->join('roles', 'user_roles.role_id', '=', 'roles.id')
        ->where('user_roles.user_id', $user->id)
        ->where('roles.name', Role::ADMIN)
        ->exists();
}
```

#### BlogPostController
```php
public function index(BlogPostIndexRequest $request)
{
    $posts = $this->blogPostService->getFilteredPosts($request);
    $categories = $this->blogPostService->getActiveCategories();
    $user = Auth::user();
    $isAdmin = $user && DB::table('user_roles')...->exists();

    return Inertia::render('Blog/Posts/Index', [
        'posts' => $posts,
        'categories' => $categories,
        'filters' => $request->only(['status', 'category', 'search', 'sort_by', 'sort_order']),
        'statuses' => BlogPost::STATUSES,
        'canCreate' => $user ? Gate::allows('create', BlogPost::class) : false,
        'canFilterByStatus' => $isAdmin, // ← Новый флаг
    ]);
}
```

### Frontend Logic

#### TypeScript Types
```typescript
export interface BlogPostsIndexProps {
    posts: LaravelPagination<BlogPost>;
    categories: BlogCategory[];
    filters: BlogFilters;
    statuses: string[];
    canCreate: boolean;
    canFilterByStatus: boolean; // ← Новый флаг
}
```

#### Vue Component (Index.vue)
```vue
<script setup lang="ts">
const { canCreate, canFilterByStatus } = props;

// Условное отображение селектора статусов
const hasActiveFilters = computed(() => {
    return (
        form.value.search ||
        (form.value.category && form.value.category !== '_all') ||
        (canFilterByStatus && form.value.status && form.value.status !== '_all') ||
        form.value.sort_by !== 'published_at' ||
        form.value.sort_order !== 'desc'
    );
});

function clearFilters() {
    form.value.search = '';
    form.value.category = null;
    if (canFilterByStatus) {  // ← Условная очистка статуса
        form.value.status = null;
    }
    form.value.sort_by = 'published_at';
    form.value.sort_order = 'desc';
    updateFilters();
}
</script>

<template>
    <!-- Селектор статусов только для администраторов -->
    <Select v-if="canFilterByStatus" v-model="statusModel">
        <SelectTrigger class="w-full sm:w-40">
            <div class="flex items-center gap-2">
                <ListFilter class="h-4 w-4 text-muted-foreground" />
                <SelectValue placeholder="All Statuses" />
            </div>
        </SelectTrigger>
        <SelectContent>
            <SelectItem value="_all">All Statuses</SelectItem>
            <SelectItem v-for="status in props.statuses" :key="status" :value="status">
                {{ status.charAt(0).toUpperCase() + status.slice(1) }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
```

## Логика фильтрации

### Сценарии доступа

| Пользователь | Фильтр статуса | Результат |
|--------------|----------------|-----------|
| **Админ** | Не выбран | Все посты (published + draft + archived) |
| **Админ** | `published` | Только опубликованные |  
| **Админ** | `draft` | Только черновики |
| **Админ** | `archived` | Только архивные |
| **Обычный** | Любой | Только опубликованные |
| **Гость** | Любой | Только опубликованные |

### Правила безопасности

1. **Server-side enforcement**: Фильтрация происходит на сервере в `BlogPostService`
2. **UI consistency**: Frontend скрывает недоступные опции через `v-if="canFilterByStatus"`
3. **Policy integration**: Используются существующие роли через `user_roles` таблицу
4. **Fallback behavior**: По умолчанию показываются только опубликованные посты

## Тестирование

### Покрытые сценарии

#### Unit Tests (`BlogPostServiceTest`)
- ✅ Администратор может фильтровать по статусу
- ✅ Обычный пользователь видит только опубликованные посты
- ✅ Поиск работает корректно независимо от роли

#### Feature Tests (`AdminAccessTest`)
- ✅ Администратор получает `canFilterByStatus: true`
- ✅ Обычный пользователь получает `canFilterByStatus: false`
- ✅ Гость получает `canFilterByStatus: false`
- ✅ Администратор видит все посты без фильтра
- ✅ Обычный пользователь видит только опубликованные

#### Existing Tests
- ✅ Все существующие 92+ теста продолжают работать
- ✅ Политики доступа не нарушены
- ✅ Middleware продолжает работать корректно

### Запуск тестов
```bash
# Все тесты блога
php artisan test --filter=Blog

# Тесты прав доступа
php artisan test --filter=AdminAccessTest

# Тесты сервиса
php artisan test --filter=BlogPostServiceTest
```

## Влияние на производительность

### Оптимизации
- **Database query**: Эффективный JOIN для проверки роли администратора
- **Single point of logic**: Вся логика доступа централизована в `BlogPostService`
- **Frontend efficiency**: Условное отображение через `v-if` без лишних вычислений

### Мониторинг
```sql
-- Запрос для проверки роли администратора
SELECT 1 FROM user_roles 
JOIN roles ON user_roles.role_id = roles.id 
WHERE user_roles.user_id = ? AND roles.name = 'admin'
```

## Совместимость

- ✅ **Laravel 12**: Использует стандартные возможности Eloquent и Query Builder
- ✅ **Inertia.js**: Передача данных через props работает корректно
- ✅ **Vue 3 + TypeScript**: Типы обновлены, компиляция без ошибок
- ✅ **Existing Policies**: Интеграция с `BlogPostPolicy` сохранена
- ✅ **Role System**: Использует существующую систему ролей

## Расширение функциональности

### Возможные улучшения
1. **Кэширование ролей**: Redis/память для частых проверок администратора
2. **Гранулярные права**: Отдельные роли для разных типов контента
3. **Audit logging**: Логирование действий администратора
4. **Batch operations**: Массовые операции для администратора
5. **Advanced filters**: Дополнительные фильтры только для админов

### Миграция
Система готова к работе без дополнительных миграций - использует существующие таблицы `users`, `roles`, `user_roles` и `blog_posts`.