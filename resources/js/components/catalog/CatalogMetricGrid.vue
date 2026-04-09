<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card';
import type { CatalogMetricItem } from '@/types';

defineProps<{
    items: CatalogMetricItem[];
}>();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-3">
        <Card
            v-for="item in items"
            :key="item.label"
            class="gap-0 border-slate-200/80 bg-white/90 py-0 shadow-sm dark:border-slate-800 dark:bg-slate-950/70"
        >
            <CardContent class="space-y-2 px-5 py-5">
                <p
                    class="text-[11px] font-semibold uppercase tracking-[0.22em] text-muted-foreground"
                >
                    {{ item.label }}
                </p>
                <p class="text-3xl font-semibold tracking-tight">
                    {{ item.value }}
                </p>
                <p class="text-sm text-muted-foreground">
                    {{ item.description }}
                </p>
            </CardContent>
        </Card>
    </div>
</template>
