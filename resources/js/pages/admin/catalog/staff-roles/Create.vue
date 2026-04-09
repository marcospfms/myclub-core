<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import StaffRoleForm from '@/pages/admin/catalog/staff-roles/Partials/StaffRoleForm.vue';

const indexHref = '/admin/catalog/staff-roles';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Staff Roles', href: indexHref },
            { title: 'Create', href: `${indexHref}/create` },
        ],
    },
});

const form = useForm({
    name: '',
    label_key: '',
    description_key: '',
    icon: '',
});

function submit(): void {
    form.post(indexHref);
}
</script>

<template>
    <Head title="Create Staff Role" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Create staff role" description="Cadastre um papel da comissão técnica usando slug estável e chaves de tradução compatíveis com os clientes." />
        <StaffRoleForm :form="form" submit-label="Create staff role" :cancel-href="indexHref" @submit="submit" />
    </div>
</template>
