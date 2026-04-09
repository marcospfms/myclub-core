<script setup lang="ts">
defineProps<{
    title: string;
    description: string;
}>();
</script>

<template>
    <div
        class="rounded-2xl border border-dashed border-slate-300/80 bg-slate-50/80 px-6 py-12 text-center dark:border-slate-700 dark:bg-slate-950/50"
    >
        <p class="text-lg font-semibold tracking-tight">{{ title }}</p>
        <p class="mt-2 text-sm text-muted-foreground">
            {{ description }}
        </p>
        <div class="mt-4">
            <slot />
        </div>
    </div>
</template>
