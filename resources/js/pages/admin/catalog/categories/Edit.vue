<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import CategoryForm from '@/pages/admin/catalog/categories/Partials/CategoryForm.vue';
import type { Category } from '@/types';

const props = defineProps<{
    category: Category;
}>();

const indexHref = '/admin/catalog/categories';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Categories', href: indexHref },
            { title: 'Edit', href: indexHref },
        ],
    },
});

const form = useForm({
    key: props.category.key,
    name: props.category.name,
});

function submit(): void {
    form.put(`${indexHref}/${props.category.id}`);
}
</script>

<template>
    <Head title="Edit Category" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit category" description="Ajuste a nomenclatura administrativa mantendo a chave estável para integrações futuras." />
        <CategoryForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
