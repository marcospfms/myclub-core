<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import CatalogEmptyState from '@/components/catalog/CatalogEmptyState.vue';
import CatalogMetricGrid from '@/components/catalog/CatalogMetricGrid.vue';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import {
    create as createFormation,
    destroy as destroyFormationRoute,
    edit as editFormation,
    index as formationsIndex,
} from '@/routes/admin/catalog/formations';
import type { CatalogMetricItem, Formation } from '@/types';

const props = defineProps<{
    formations: Formation[];
}>();

const indexHref = formationsIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: indexHref },
            { title: 'Formations', href: indexHref },
        ],
    },
});

const metrics: CatalogMetricItem[] = [
    { label: 'Total formations', value: props.formations.length, description: 'Desenhos táticos cadastrados para modalidades esportivas.' },
    { label: 'Distinct lines', value: new Set(props.formations.map((item) => item.name)).size, description: 'Padrões táticos únicos publicados.' },
    { label: 'Max width', value: props.formations.reduce((max, item) => Math.max(max, item.name.length), 0), description: 'Maior comprimento textual entre formações cadastradas.' },
];

function destroyFormation(id: number): void {
    if (!window.confirm('Remover esta formação?')) {
        return;
    }

    router.delete(destroyFormationRoute.url(id));
}
</script>

<template>
    <Head title="Formations" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Formations" description="Centralize os desenhos táticos que alimentam escalações, convites e resumos de jogo." >
            <template #actions>
                <Button as-child><Link :href="createFormation.url()"><Plus class="size-4" />New formation</Link></Button>
            </template>
        </CatalogPageHeader>

        <CatalogMetricGrid :items="metrics" />

        <CatalogEmptyState v-if="formations.length === 0" title="No formations yet" description="Comece pelas formações principais usadas nas modalidades atuais.">
            <Button as-child><Link :href="createFormation.url()">Create first formation</Link></Button>
        </CatalogEmptyState>

        <Card v-else class="gap-0 py-0">
            <CardContent class="overflow-x-auto px-0">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            <th class="px-6 py-4">Formation</th>
                            <th class="px-6 py-4">Key</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-for="formation in formations" :key="formation.id">
                            <td class="px-6 py-5 font-medium">{{ formation.name }}</td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ formation.key }}</td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="editFormation.url(formation.id)"><Pencil class="size-4" />Edit</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" @click="destroyFormation(formation.id)">
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
