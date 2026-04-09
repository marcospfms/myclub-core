<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import PositionForm from '@/pages/admin/catalog/positions/Partials/PositionForm.vue';
import type { Position } from '@/types';

const props = defineProps<{
    position: Position;
}>();

const indexHref = '/admin/catalog/positions';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Positions', href: indexHref },
            { title: 'Edit', href: indexHref },
        ],
    },
});

const form = useForm({
    key: props.position.key,
    label_key: props.position.label_key,
    description_key: props.position.description_key ?? '',
    icon: props.position.icon ?? '',
    abbreviation: props.position.abbreviation,
});

function submit(): void {
    form.put(`${indexHref}/${props.position.id}`);
}
</script>

<template>
    <Head title="Edit Position" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit position" description="Refine a posição mantendo abreviação, iconografia e chaves de tradução consistentes com o catálogo." />
        <PositionForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
