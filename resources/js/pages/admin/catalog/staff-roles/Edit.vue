<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import StaffRoleForm from '@/pages/admin/catalog/staff-roles/Partials/StaffRoleForm.vue';
import { dashboard } from '@/routes';
import {
    edit as editStaffRole,
    index as staffRolesIndex,
    update as updateStaffRole,
} from '@/routes/admin/catalog/staff-roles';
import type { StaffRole } from '@/types';

const props = defineProps<{
    staffRole: { data: StaffRole };
}>();

const indexHref = staffRolesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: staffRolesIndex.url() },
            { title: 'Staff Roles', href: staffRolesIndex.url() },
            { title: 'Edit', href: staffRolesIndex.url() },
        ],
    },
});

const form = useForm({
    name: props.staffRole.data.name,
    label_key: props.staffRole.data.label_key,
    description_key: props.staffRole.data.description_key ?? '',
    icon: props.staffRole.data.icon ?? '',
});

function submit(): void {
    form.put(updateStaffRole.url(props.staffRole.data.id));
}
</script>

<template>
    <Head title="Edit Staff Role" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Edit staff role" description="Atualize o papel da comissão técnica sem perder o contrato estável usado pelas demais superfícies." />
        <StaffRoleForm :form="form" submit-label="Save changes" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
