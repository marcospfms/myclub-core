<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import BadgeTypeForm from '@/pages/admin/catalog/badge-types/Partials/BadgeTypeForm.vue';
import type { BadgeType } from '@/types';

const props = defineProps<{
    badgeType: BadgeType;
}>();

const indexHref = '/admin/catalog/badge-types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Badge Types', href: indexHref },
            { title: 'Edit', href: indexHref },
        ],
    },
});

const form = useForm({
    name: props.badgeType.name,
    label_key: props.badgeType.label_key,
    description_key: props.badgeType.description_key ?? '',
    icon: props.badgeType.icon ?? '',
    scope: props.badgeType.scope,
});

function submit(): void {
    form.put(`${indexHref}/${props.badgeType.id}`);
}
</script>

<template>
    <Head title="Edit Badge Type" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Recognition Catalog" title="Edit badge type" description="Ajuste o contrato do badge sem perder a compatibilidade entre admin, API e clientes externos." />
        <BadgeTypeForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
