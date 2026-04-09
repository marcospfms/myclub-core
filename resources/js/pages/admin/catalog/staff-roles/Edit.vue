<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import StaffRoleForm from '@/pages/admin/catalog/staff-roles/Partials/StaffRoleForm.vue';
import type { StaffRole } from '@/types';

const props = defineProps<{
    staffRole: StaffRole;
}>();

const indexHref = '/admin/catalog/staff-roles';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Staff Roles', href: indexHref },
            { title: 'Edit', href: indexHref },
        ],
    },
});

const form = useForm({
    name: props.staffRole.name,
    label_key: props.staffRole.label_key,
    description_key: props.staffRole.description_key ?? '',
    icon: props.staffRole.icon ?? '',
});

function submit(): void {
    form.put(`${indexHref}/${props.staffRole.id}`);
}
</script>

<template>
    <Head title="Edit Staff Role" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit staff role" description="Atualize o papel da comissão técnica sem perder o contrato estável usado pelas demais superfícies." />
        <StaffRoleForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
