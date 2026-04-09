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
    create as createPosition,
    destroy as destroyPositionRoute,
    edit as editPosition,
    index as positionsIndex,
} from '@/routes/admin/catalog/positions';
import type { CatalogMetricItem, Position } from '@/types';

const props = defineProps<{
    positions: Position[];
}>();

const indexHref = positionsIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: indexHref },
            { title: 'Positions', href: indexHref },
        ],
    },
});

const metrics: CatalogMetricItem[] = [
    { label: 'Total positions', value: props.positions.length, description: 'Posições reutilizadas em escalação, convites e preferências.' },
    { label: 'Unique abbreviations', value: new Set(props.positions.map((item) => item.abbreviation)).size, description: 'Abreviações disponíveis para lineup e resumos de partida.' },
    { label: 'With icon key', value: props.positions.filter((item) => item.icon).length, description: 'Entradas prontas para render visual no admin e em clientes externos.' },
];

function destroyPosition(id: number): void {
    if (!window.confirm('Remover esta posição?')) {
        return;
    }

    router.delete(destroyPositionRoute.url(id));
}
</script>

<template>
    <Head title="Positions" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Sports Catalog" title="Positions" description="Orquestre as posições esportivas com siglas curtas, chaves i18n e iconografia neutra." >
            <template #actions>
                <Button as-child>
                    <Link :href="createPosition.url()"><Plus class="size-4" />New position</Link>
                </Button>
            </template>
        </CatalogPageHeader>

        <CatalogMetricGrid :items="metrics" />

        <CatalogEmptyState v-if="positions.length === 0" title="No positions yet" description="Cadastre as posições para liberar convites, escalações e preferências de jogadores.">
            <Button as-child><Link :href="createPosition.url()">Create first position</Link></Button>
        </CatalogEmptyState>

        <Card v-else class="gap-0 py-0">
            <CardContent class="overflow-x-auto px-0">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            <th class="px-6 py-4">Position</th>
                            <th class="px-6 py-4">Key</th>
                            <th class="px-6 py-4">Abbreviation</th>
                            <th class="px-6 py-4">Icon</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-for="position in positions" :key="position.id">
                            <td class="px-6 py-5">
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ resolveCatalogMessage('pt-BR', position.label_key) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ position.description_key ? resolveCatalogMessage('pt-BR', position.description_key) : 'No description key' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ position.key }}</td>
                            <td class="px-6 py-5 font-medium">{{ position.abbreviation }}</td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ position.icon ?? '—' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="editPosition.url(position.id)"><Pencil class="size-4" />Edit</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" @click="destroyPosition(position.id)">
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
