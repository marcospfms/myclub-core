<script setup lang="ts">
defineProps<{
    eyebrow: string;
    title: string;
    description: string;
}>();
</script>

<template>
    <section
        class="relative overflow-hidden rounded-2xl border border-emerald-200/70 bg-linear-to-br from-emerald-50 via-white to-amber-50 shadow-sm dark:border-emerald-900/60 dark:from-emerald-950/40 dark:via-background dark:to-amber-950/30"
    >
        <div
            class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-emerald-500 via-lime-400 to-amber-400"
        />

        <div
            class="flex flex-col gap-6 px-6 py-6 md:flex-row md:items-end md:justify-between"
        >
            <div class="space-y-2">
                <p
                    class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-700 dark:text-emerald-300"
                >
                    {{ eyebrow }}
                </p>
                <div class="space-y-1">
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ title }}
                    </h1>
                    <p class="max-w-3xl text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <slot name="actions" />
            </div>
        </div>
    </section>
</template>
