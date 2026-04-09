<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import CategoryForm from '@/pages/admin/catalog/categories/Partials/CategoryForm.vue';
import { dashboard } from '@/routes';
import {
    edit as editCategory,
    index as categoriesIndex,
    update as updateCategory,
} from '@/routes/admin/catalog/categories';
import type { Category } from '@/types';

const props = defineProps<{
    category: { data: Category };
}>();

const indexHref = categoriesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: categoriesIndex.url() },
            { title: 'Categories', href: categoriesIndex.url() },
            { title: 'Edit', href: categoriesIndex.url() },
        ],
    },
});

const form = useForm({
    key: props.category.data.key,
    name: props.category.data.name,
});

function submit(): void {
    form.put(updateCategory.url(props.category.data.id));
}
</script>

<template>
    <Head title="Edit Category" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit category" description="Ajuste a nomenclatura administrativa mantendo a chave estável para integrações futuras." />
        <CategoryForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
