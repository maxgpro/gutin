<script setup lang="ts">
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    total: number;
    perPage: number;
    currentPage: number;
    pageParam?: string; // если в Laravel поменяешь имя параметра страницы, например paginate(..., 'p')
}>();

const pageParam = computed(() => props.pageParam ?? 'page');

// построить URL с новым ?page, сохранив остальные фильтры
function goTo(page: number) {
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
</script>

<template>
    <Pagination :page="currentPage" :items-per-page="perPage" :total="total" @update:page="goTo">
        <PaginationContent v-slot="{ items }">
            <PaginationPrevious />
            <template v-for="(item, index) in items" :key="index">
                <PaginationItem v-if="item.type === 'page'" :value="item.value" :is-active="item.value === currentPage" @click="goTo(item.value)">
                    {{ item.value }}
                </PaginationItem>

                <PaginationEllipsis v-else :index="index" />
            </template>
            <PaginationNext />
        </PaginationContent>
    </Pagination>
</template>
