<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import CatalogEmptyState from '@/components/catalog/CatalogEmptyState.vue';
import CatalogMetricGrid from '@/components/catalog/CatalogMetricGrid.vue';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { resolveCatalogMessage } from '@/i18n/catalog';
import { dashboard } from '@/routes';
import {
    create as createStaffRole,
    destroy as destroyStaffRoleRoute,
    edit as editStaffRole,
    index as staffRolesIndex,
} from '@/routes/admin/catalog/staff-roles';
import type { CatalogMetricItem, StaffRole } from '@/types';

const props = defineProps<{
    staffRoles: StaffRole[];
}>();

const indexHref = staffRolesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: indexHref },
            { title: 'Staff Roles', href: indexHref },
        ],
    },
});

const metrics: CatalogMetricItem[] = [
    { label: 'Total roles', value: props.staffRoles.length, description: 'Papéis administrativos e esportivos disponíveis para a comissão.' },
    { label: 'With icon key', value: props.staffRoles.filter((item) => item.icon).length, description: 'Roles com referência visual pronta para consumo no admin.' },
    { label: 'Translated labels', value: props.staffRoles.filter((item) => item.label_key).length, description: 'Entradas prontas para renderização em múltiplos idiomas.' },
];

function destroyStaffRole(id: number): void {
    if (!window.confirm('Remover esta função da comissão?')) {
        return;
    }

    router.delete(destroyStaffRoleRoute.url(id));
}
</script>

<template>
    <Head title="Staff Roles" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Staff roles" description="Gerencie o vocabulário oficial da comissão técnica para vínculos, permissões futuras e visualização pública." >
            <template #actions>
                <Button as-child><Link :href="createStaffRole.url()"><Plus class="size-4" />New staff role</Link></Button>
            </template>
        </CatalogPageHeader>

        <CatalogMetricGrid :items="metrics" />

        <CatalogEmptyState v-if="staffRoles.length === 0" title="No staff roles yet" description="Cadastre os papéis da comissão técnica antes de abrir os módulos de time e staff.">
            <Button as-child><Link :href="createStaffRole.url()">Create first staff role</Link></Button>
        </CatalogEmptyState>

        <Card v-else class="gap-0 py-0">
            <CardContent class="overflow-x-auto px-0">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            <th class="px-6 py-4">Role</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Icon</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-for="staffRole in staffRoles" :key="staffRole.id">
                            <td class="px-6 py-5">
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ resolveCatalogMessage('pt-BR', staffRole.label_key) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ staffRole.description_key ? resolveCatalogMessage('pt-BR', staffRole.description_key) : 'No description key' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ staffRole.name }}</td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ staffRole.icon ?? '—' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="editStaffRole.url(staffRole.id)"><Pencil class="size-4" />Edit</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" @click="destroyStaffRole(staffRole.id)">
                                        <Trash2 class="size-4" />Remove
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>
