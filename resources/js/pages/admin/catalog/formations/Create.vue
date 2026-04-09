<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import FormationForm from '@/pages/admin/catalog/formations/Partials/FormationForm.vue';
import { dashboard } from '@/routes';
import {
    create as createFormation,
    index as formationsIndex,
    store as storeFormation,
} from '@/routes/admin/catalog/formations';

const indexHref = formationsIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: indexHref },
            { title: 'Formations', href: indexHref },
            { title: 'Create', href: createFormation.url() },
        ],
    },
});

const form = useForm({
    key: '',
    name: '',
});

function submit(): void {
    form.post(storeFormation.url());
}
</script>

<template>
    <Head title="Create Formation" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Create formation" description="Cadastre um novo desenho tático para ficar disponível nas modalidades esportivas." />
        <FormationForm :form="form" submit-label="Create formation" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
