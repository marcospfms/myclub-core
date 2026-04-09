<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import BadgeTypeForm from '@/pages/admin/catalog/badge-types/Partials/BadgeTypeForm.vue';

const indexHref = '/admin/catalog/badge-types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Badge Types', href: indexHref },
            { title: 'Create', href: `${indexHref}/create` },
        ],
    },
});

const form = useForm({
    name: '',
    label_key: '',
    description_key: '',
    icon: '',
    scope: 'championship' as const,
});

function submit(): void {
    form.post(indexHref);
}
</script>

<template>
    <Head title="Create Badge Type" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Recognition Catalog" title="Create badge type" description="Defina um novo tipo de reconhecimento com escopo de distribuição, slug estável e contrato i18n." />
        <BadgeTypeForm :form="form" submit-label="Create badge type" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
