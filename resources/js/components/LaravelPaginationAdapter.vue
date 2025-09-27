<script setup lang="ts">
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { paginationConfig } from '@/config/pagination';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

// Composables
const { t } = useI18n();

const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    from?: number | null; // начальный индекс для текущей страницы (1, 26, 51...)
    to?: number | null; // конечный индекс для текущей страницы (25, 50, 75...)
    pageParam?: string; // если в Laravel поменяешь имя параметра страницы, например paginate(..., 'p')
    showPerPageSelector?: boolean; // показывать ли селектор количества элементов
    showResultsCount?: boolean; // показывать ли счетчик результатов
}>();

const pageParam = computed(() => props.pageParam ?? paginationConfig.pageParam);

// Модель для селектора количества элементов на странице
const perPageModel = computed({
    get: () => String(props.perPage),
    set: (value: string) => {
        const perPage = parseInt(value);
        changePerPage(perPage);
    },
});

// построить URL с новым ?page, сохранив остальные фильтры
function goTo(page: number) {
    // Проверяем валидность страницы
    if (page < 1 || page === props.currentPage) {
        return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set(pageParam.value, String(page));

    // желательно не трогать per_page/filters, они уже есть в querystring.
    router.get(
        url.pathname + '?' + url.searchParams.toString(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: false, // хотим нормальную историю браузера
        },
    );
}

// изменить количество элементов на странице
function changePerPage(perPage: number) {
    const url = new URL(window.location.href);
    url.searchParams.set(paginationConfig.perPageParam, String(perPage));
    // Сбрасываем на первую страницу при изменении количества элементов
    url.searchParams.set(pageParam.value, '1');

    router.get(
        url.pathname + '?' + url.searchParams.toString(),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: false,
        },
    );
}
</script>

<template>
    <div class="space-y-4">
        <!-- Pagination and Per Page Selector -->
        <div class="flex gap-4 flex-col sm:flex-row sm:flex-wrap sm:flex-start">

            <!-- Per Page Selector -->
            <div v-if="showPerPageSelector" 
                class="flex items-center gap-2 text-sm text-muted-foreground justify-center sm:justify-start">
                <span>{{ t('common.show') }}</span>
                <Select v-model="perPageModel">
                    <SelectTrigger class="w-20">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="option in paginationConfig.perPageOptions" :key="option" :value="String(option)">
                            {{ option }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <span>{{ t('pagination.entries', {}, perPage) }}</span>
            </div>

            <!-- Pagination -->
            <Pagination
                class="w-auto"
                :page="currentPage"
                :items-per-page="perPage"
                :total="total"
                :sibling-count="paginationConfig.siblingCount"
                :show-edges="paginationConfig.showEdges"
                @update:page="goTo"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious />

                    <template v-for="(item, index) in items" :key="item.type === 'page' ? `page-${item.value}` : `ellipsis-${index}`">
                        <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === currentPage">
                            {{ item.value }}
                        </PaginationItem>

                        <PaginationEllipsis v-else :index="index" />
                    </template>

                    <PaginationNext />
                </PaginationContent>
            </Pagination>

            <!-- Results Count -->
            <div
                v-if="showResultsCount && total > 0 && from && to"
                class="flex items-center justify-center gap-2 text-sm text-muted-foreground sm:justify-end"
            >
                {{ t('pagination.showing', { from, to, total }, total) }}
            </div>
        </div>
    </div>
</template>
