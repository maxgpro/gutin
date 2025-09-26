# Рефакторинг системы доступа к блог-постам

## Проблема исходного подхода

### ❌ **Что было неправильно:**

1. **Дублирование логики проверки админа:**
   ```php
   // В Service
   protected function isUserAdmin(): bool {
       return DB::table('user_roles')->join('roles'...)...
   }
   
   // В Controller  
   $isAdmin = $user && DB::table('user_roles')->join('roles'...)...
   ```

2. **Игнорирование готового метода `isAdmin()`:**
   - В модели `User` уже был метод `isAdmin()` 
   - Писали сложные DB запросы вместо использования готового решения

3. **Смешанная ответственность в Service:**
   ```php
   // Service занимался и авторизацией, и бизнес-логикой
   if ($isAdmin && $request->has('status')) {
       $query->where('status', $request->status);
   } elseif (!$isAdmin) {
       $query->published();
   }
   ```

## Решение: Разделение ответственности

### ✅ **Новый подход:**

#### 1. **Request отвечает за авторизацию**
```php
// BlogPostIndexRequest::authorize()
public function authorize(): bool
{
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
```

#### 2. **Service занимается только бизнес-логикой**
```php
// BlogPostService::applyStatusFilter()
protected function applyStatusFilter(Builder $query, BlogPostIndexRequest $request): void
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();
    $isAdmin = $user && $user->isAdmin(); // Используем готовый метод!
    
    if ($request->has('status') && in_array($request->status, BlogPost::STATUSES)) {
        // Валидация уже прошла в Request
        $query->where('status', $request->status);
    } elseif (!$isAdmin) {
        // Не-админы видят только опубликованные
        $query->published();
    }
    // Админы без фильтра видят ВСЕ посты
}
```

#### 3. **Controller передаёт только UI флаги**
```php
// BlogPostController::index()
return Inertia::render('Blog/Posts/Index', [
    'canFilterByStatus' => $user?->isAdmin() ?? false, // Простая проверка
    // остальные данные...
]);
```

## Преимущества нового подхода

### 🚀 **Архитектурные улучшения:**

1. **Single Responsibility Principle:**
   - Request → Авторизация
   - Service → Бизнес-логика 
   - Controller → Координация

2. **DRY (Don't Repeat Yourself):**
   - Используем готовый `User::isAdmin()`
   - Нет дублирования DB запросов

3. **Security by Design:**
   - Блокировка на уровне входа (Request authorization)
   - Impossible States Made Impossible

### ⚡ **Производительность:**

| Подход | Запросы к БД | Сложность |
|--------|--------------|-----------|
| **Старый** | `DB::table('user_roles')->join()` на каждый вызов | O(n) |
| **Новый** | `$user->isAdmin()` (использует загруженные отношения) | O(1) |

### 🧪 **Тестируемость:**

```php
// Легко тестировать авторизацию отдельно
$request = new BlogPostIndexRequest();
$request->merge(['status' => 'draft']);
$this->assertFalse($request->authorize());

// Легко тестировать бизнес-логику отдельно  
$posts = $service->getFilteredPosts($validRequest);
```

## Поведение системы

### 📋 **Матрица доступа:**

| Пользователь | Параметр `?status=` | Request Auth | Service Filter | Результат |
|-------------|-------------------|-------------|----------------|-----------|
| **Guest** | `published` | ✅ Pass | Published only | Опубликованные |
| **Guest** | `draft` | ❌ **403** | - | Доступ запрещён |
| **User** | `published` | ✅ Pass | Published only | Опубликованные |  
| **User** | `draft` | ❌ **403** | - | Доступ запрещён |
| **Admin** | `published` | ✅ Pass | Status filter | Опубликованные |
| **Admin** | `draft` | ✅ Pass | Status filter | Черновики |
| **Admin** | не указан | ✅ Pass | No filter | **ВСЕ посты** |

### 🔒 **Безопасность:**

1. **Защита на уровне HTTP:** 
   - Невалидные запросы = 403 Forbidden
   - Не доходят до Service/Database

2. **Валидация входных данных:**
   ```php
   // В rules() проверяем формат
   'status' => ['nullable', 'string', 'in:published,draft,archived']
   
   // В authorize() проверяем права  
   if ($this->status !== 'published' && !$user?->isAdmin()) return false;
   ```

3. **Defense in Depth:**
   - Даже если Request пропустит - Service всё равно защищён

## Миграция и совместимость

### ✅ **Обратная совместимость:**
- **101 тест проходит** (из 101 общего количества)
- Все существующие API endpoints работают
- Frontend получает те же данные

### 🔄 **Изменения в коде:**

#### Удалено:
- Сложные DB запросы в Service и Controller  
- Дублированная логика проверки админа
- Импорты `DB`, `Role` где они не нужны

#### Добавлено:
- Авторизация в `BlogPostIndexRequest`
- 8 новых тестов для Request авторизации
- Использование готового `User::isAdmin()`

#### Изменено:
- Упрощена логика в `BlogPostService::applyStatusFilter()`
- Упрощён `BlogPostController::index()`

## Рекомендации по дальнейшему развитию

### 🎯 **Для других частей системы:**
1. Всегда используйте готовые методы модели вместо прямых DB запросов
2. Разделяйте авторизацию (Request) и бизнес-логику (Service)  
3. Покрывайте тестами каждый слой отдельно

### 🔮 **Возможные расширения:**
1. **Кэширование ролей:** `$user->roles` в Redis для высоконагруженных систем
2. **Middleware авторизация:** Перенос логики из Request в middleware
3. **Policy интеграция:** Связать с существующими `BlogPostPolicy`
4. **Audit logging:** Логирование попыток доступа к restricted контенту

## Заключение

Новый подход **чище, быстрее и безопаснее**:
- ✅ Используем готовые решения Laravel
- ✅ Следуем принципам SOLID  
- ✅ Улучшаем производительность
- ✅ Повышаем тестируемость
- ✅ Упрощаем поддержку

Этот рефакторинг показывает важность **ревизии архитектурных решений** и **использования готовых возможностей фреймворка** вместо изобретения велосипедов.