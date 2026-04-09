<script setup lang="ts">
import { computed } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import type { CatalogSelectionItem } from '@/types';

const props = defineProps<{
    title: string;
    description: string;
    items: CatalogSelectionItem[];
    modelValue: number[];
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number[]): void;
}>();

const selectedValues = computed(() => props.modelValue ?? []);

function toggleItem(id: number, checked: boolean | 'indeterminate'): void {
    if (checked === 'indeterminate') {
        return;
    }

    const next = new Set(selectedValues.value);

    if (checked) {
        next.add(id);
    } else {
        next.delete(id);
    }

    emit('update:modelValue', Array.from(next));
}
</script>

<template>
    <div class="space-y-4 rounded-2xl border border-slate-200/80 p-5 dark:border-slate-800">
        <div class="space-y-1">
            <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                {{ title }}
            </h3>
            <p class="text-sm text-muted-foreground">
                {{ description }}
            </p>
        </div>

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <label
                v-for="item in items"
                :key="item.id"
                class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200/70 bg-slate-50/80 p-3 transition-colors hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-800 dark:hover:bg-emerald-950/20"
            >
                <Checkbox
                    :model-value="selectedValues.includes(item.id)"
                    @update:model-value="toggleItem(item.id, $event)"
                    class="mt-0.5"
                />
                <span class="space-y-1">
                    <span class="block text-sm font-medium">{{ item.label }}</span>
                    <span
                        v-if="item.description"
                        class="block text-xs text-muted-foreground"
                    >
                        {{ item.description }}
                    </span>
                </span>
            </label>
        </div>
    </div>
</template>
