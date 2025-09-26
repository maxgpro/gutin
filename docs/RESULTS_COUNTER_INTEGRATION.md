# Results Counter Integration в LaravelPaginationAdapter

## Рефакторинг компонента пагинации

Перенос счетчика результатов из страницы в компонент `LaravelPaginationAdapter` для создания единого, самодостаточного компонента пагинации.

## Техническая реализация

### Новые пропсы компонента

```typescript
const props = defineProps<{
    // ...existing props
    from?: number | null; // начальный индекс для текущей страницы (1, 26, 51...)
    to?: number | null; // конечный индекс для текущей страницы (25, 50, 75...)
    showResultsCount?: boolean; // показывать ли счетчик результатов
}>();
```

### Структура компонента

```vue
<template>
    <div class="space-y-4">
        <!-- Results Count -->
        <div v-if="showResultsCount && total > 0 && from && to" 
             class="flex items-center gap-2 text-sm text-muted-foreground">
            <ListFilter class="h-4 w-4" />
            Showing {{ from }} to {{ to }} of {{ total }} results
        </div>

        <!-- Pagination and Per Page Selector -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <!-- Per Page Selector -->
            <div v-if="showPerPageSelector">
                <!-- Select component -->
            </div>

            <!-- Pagination -->
            <Pagination>
                <!-- Pagination content -->
            </Pagination>
        </div>
    </div>
</template>
```

## Использование

### До рефакторинга (дублирование кода)

```vue
<!-- На каждой странице отдельно -->
<div v-if="posts.data.length > 0" class="mb-4 flex items-center gap-2 text-sm text-muted-foreground">
    <ListFilter class="h-4 w-4" />
    Showing {{ posts.from }} to {{ posts.to }} of {{ posts.total }} results
</div>

<LaravelPaginationAdapter 
    :total="posts.total" 
    :perPage="posts.per_page" 
    :currentPage="posts.current_page"
    :showPerPageSelector="true"
/>
```

### После рефакторинга (единый компонент)

```vue
<!-- Всё в одном компоненте -->
<LaravelPaginationAdapter 
    :total="posts.total" 
    :perPage="posts.per_page" 
    :currentPage="posts.current_page"
    :from="posts.from"
    :to="posts.to"
    :showPerPageSelector="true"
    :showResultsCount="true"
/>
```

## Преимущества рефакторинга

### ✅ DRY Principle
- Убрали дублирование кода счетчика результатов
- Единая реализация для всех страниц с пагинацией

### ✅ Consistency
- Единообразный внешний вид и поведение
- Централизованная стилизация компонента

### ✅ Maintainability
- Изменения в одном месте влияют на все использования
- Проще добавлять новые функции пагинации

### ✅ Reusability
- Компонент стал более полнофункциональным
- Легче переиспользовать в других частях приложения

## Responsive Layout

### Мобильная версия (колонка)
```
Showing 1 to 25 of 154 results

Show [25▼] entries

[◀ 1 2 3 ... 7 ▶]
```

### Десктопная версия (ряд)
```
Showing 1 to 25 of 154 results

Show [25▼] entries          [◀ 1 2 3 ... 7 ▶]
```

## Условная отрисовка

### Счетчик результатов
Отображается только если:
- `showResultsCount="true"`
- `total > 0` 
- `from` и `to` не `null`

### Селектор количества
Отображается только если:
- `showPerPageSelector="true"`

### Сама пагинация
Всегда отображается (основная функциональность)

## Type Safety

```typescript
// Поддержка Laravel pagination структуры
from?: number | null; // может быть null для пустых результатов
to?: number | null;   // может быть null для пустых результатов
```

## Обратная совместимость

Компонент остается полностью совместимым со старым API:

```vue
<!-- Минимальное использование (как раньше) -->
<LaravelPaginationAdapter 
    :total="posts.total" 
    :perPage="posts.per_page" 
    :currentPage="posts.current_page"
/>

<!-- Полнофункциональное использование -->
<LaravelPaginationAdapter 
    :total="posts.total" 
    :perPage="posts.per_page" 
    :currentPage="posts.current_page"
    :from="posts.from"
    :to="posts.to"
    :showPerPageSelector="true"
    :showResultsCount="true"
/>
```

## Результат

Теперь `LaravelPaginationAdapter` - это комплексный компонент пагинации, который включает:
- 📊 Счетчик результатов  
- 🔢 Селектор количества элементов на странице
- 📄 Навигацию по страницам
- ✨ Подсветку активной страницы
- 📱 Responsive дизайн

Единый компонент для всех потребностей пагинации!