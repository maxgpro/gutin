<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useWindowScroll } from '@vueuse/core';
import { ArrowUp } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    threshold?: number; // px, после какого отступа показывать кнопку
    bottom?: string; // CSS значение для отступа снизу
    right?: string; // CSS значение для отступа справа
}

const props = withDefaults(defineProps<Props>(), {
    threshold: 300,
    bottom: '1.5rem', // 24px ~ bottom-6
    right: '1.5rem', // 24px ~ right-6
});

const { y } = useWindowScroll();
const { t } = useI18n();

const visible = computed(() => y.value > props.threshold);

function scrollToTop() {
    if (typeof window !== 'undefined') {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<template>
    <Transition name="scroll-fade" appear>
        <Button
            v-show="visible"
            :style="{ bottom: props.bottom, right: props.right }"
            class="fixed z-50 rounded-full shadow-lg"
            variant="secondary"
            size="icon"
            :aria-label="t('common.back_to_top', 'Back to top') as string"
            @click="scrollToTop"
        >
            <ArrowUp class="h-5 w-5" />
            <span class="sr-only">{{ t('common.back_to_top', 'Back to top') }}</span>
        </Button>
    </Transition>
</template>

<style scoped>
.scroll-fade-enter-active,
.scroll-fade-leave-active {
    transition:
        opacity 200ms ease,
        transform 200ms ease;
}
.scroll-fade-enter-from,
.scroll-fade-leave-to {
    opacity: 0;
    transform: translateY(8px) scale(0.98);
}
.scroll-fade-enter-to,
.scroll-fade-leave-from {
    opacity: 1;
    transform: translateY(0) scale(1);
}

.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
</style>
