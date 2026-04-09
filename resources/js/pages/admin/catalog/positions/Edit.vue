<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import PositionForm from '@/pages/admin/catalog/positions/Partials/PositionForm.vue';
import { dashboard } from '@/routes';
import {
    edit as editPosition,
    index as positionsIndex,
    update as updatePosition,
} from '@/routes/admin/catalog/positions';
import type { Position } from '@/types';

const props = defineProps<{
    position: { data: Position };
}>();

const indexHref = positionsIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: positionsIndex.url() },
            { title: 'Positions', href: positionsIndex.url() },
            { title: 'Edit', href: positionsIndex.url() },
        ],
    },
});

const form = useForm({
    key: props.position.data.key,
    label_key: props.position.data.label_key,
    description_key: props.position.data.description_key ?? '',
    icon: props.position.data.icon ?? '',
    abbreviation: props.position.data.abbreviation,
});

function submit(): void {
    form.put(updatePosition.url(props.position.data.id));
}
</script>

<template>
    <Head title="Edit Position" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit position" description="Refine a posição mantendo abreviação, iconografia e chaves de tradução consistentes com o catálogo." />
        <PositionForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
