<script setup lang="ts">
import { computed } from 'vue';
import { resolveCatalogMessage, type AdminLocale } from '@/i18n/catalog';

const props = withDefaults(
    defineProps<{
        labelKey: string;
        descriptionKey?: string | null;
        locale?: AdminLocale;
    }>(),
    {
        descriptionKey: null,
        locale: 'pt-BR',
    },
);

const label = computed(() =>
    resolveCatalogMessage(props.locale, props.labelKey),
);

const description = computed(() =>
    props.descriptionKey
        ? resolveCatalogMessage(props.locale, props.descriptionKey)
        : null,
);
</script>

<template>
    <div
        class="rounded-xl border border-dashed border-emerald-300/70 bg-emerald-50/70 p-4 dark:border-emerald-900 dark:bg-emerald-950/30"
    >
        <p class="text-[11px] font-semibold uppercase tracking-[0.24em] text-emerald-700 dark:text-emerald-300">
            Preview i18n
        </p>
        <div class="mt-3 space-y-2">
            <p class="text-base font-semibold">{{ label }}</p>
            <p v-if="description" class="text-sm text-muted-foreground">
                {{ description }}
            </p>
            <div class="grid gap-1 pt-2 text-xs text-muted-foreground">
                <span><strong class="font-medium text-foreground">label_key:</strong> {{ labelKey }}</span>
                <span v-if="descriptionKey"><strong class="font-medium text-foreground">description_key:</strong> {{ descriptionKey }}</span>
            </div>
        </div>
    </div>
</template>
