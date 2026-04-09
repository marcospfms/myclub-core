<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import CategoryForm from '@/pages/admin/catalog/categories/Partials/CategoryForm.vue';
import { dashboard } from '@/routes';
import {
    create as createCategory,
    index as categoriesIndex,
    store as storeCategory,
} from '@/routes/admin/catalog/categories';

const indexHref = categoriesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: categoriesIndex.url() },
            { title: 'Categories', href: categoriesIndex.url() },
            { title: 'Create', href: createCategory.url() },
        ],
    },
});

const form = useForm({
    key: '',
    name: '',
});

function submit(): void {
    form.post(storeCategory.url());
}
</script>

<template>
    <Head title="Create Category" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Create category" description="Adicione uma nova categoria administrativa para organizar ligas, elencos e inscrições." />
        <CategoryForm :form="form" submit-label="Create category" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
