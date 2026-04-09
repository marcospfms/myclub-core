<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import PositionForm from '@/pages/admin/catalog/positions/Partials/PositionForm.vue';
import { dashboard } from '@/routes';
import {
    create as createPosition,
    index as positionsIndex,
    store as storePosition,
} from '@/routes/admin/catalog/positions';

const indexHref = positionsIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: positionsIndex.url() },
            { title: 'Positions', href: positionsIndex.url() },
            { title: 'Create', href: createPosition.url() },
        ],
    },
});

const form = useForm({
    key: '',
    label_key: '',
    description_key: '',
    icon: '',
    abbreviation: '',
});

function submit(): void {
    form.post(storePosition.url());
}
</script>

<template>
    <Head title="Create Position" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Create position" description="Cadastre uma nova posição com sigla curta e chaves de tradução compatíveis com as demais stacks." />
        <PositionForm :form="form" submit-label="Create position" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
