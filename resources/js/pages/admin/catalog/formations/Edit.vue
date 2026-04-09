<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import FormationForm from '@/pages/admin/catalog/formations/Partials/FormationForm.vue';
import { dashboard } from '@/routes';
import {
    edit as editFormation,
    index as formationsIndex,
    update as updateFormation,
} from '@/routes/admin/catalog/formations';
import type { Formation } from '@/types';

const props = defineProps<{
    formation: Formation;
}>();

const indexHref = formationsIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: indexHref },
            { title: 'Formations', href: indexHref },
            { title: 'Edit', href: editFormation.url(props.formation.id) },
        ],
    },
});

const form = useForm({
    key: props.formation.key,
    name: props.formation.name,
});

function submit(): void {
    form.put(updateFormation.url(props.formation.id));
}
</script>

<template>
    <Head title="Edit Formation" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit formation" description="Ajuste a formação tática preservando a coerência com o restante do catálogo esportivo." />
        <FormationForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
