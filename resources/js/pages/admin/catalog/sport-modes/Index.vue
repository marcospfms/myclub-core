<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import CatalogEmptyState from '@/components/catalog/CatalogEmptyState.vue';
import CatalogMetricGrid from '@/components/catalog/CatalogMetricGrid.vue';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { resolveCatalogMessage } from '@/i18n/catalog';
import type { CatalogMetricItem, SportMode } from '@/types';

const props = defineProps<{
    sportModes: SportMode[];
}>();

const indexHref = '/admin/catalog/sport-modes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Sport Modes', href: indexHref },
        ],
    },
});

const metrics: CatalogMetricItem[] = [
    {
        label: 'Total modes',
        value: props.sportModes.length,
        description: 'Modalidades disponíveis para campeonatos, amistosos e descoberta.',
    },
    {
        label: 'With formations',
        value: props.sportModes.filter((item) => item.formations.length > 0).length,
        description: 'Entradas com desenho tático pronto para escalação.',
    },
    {
        label: 'With positions',
        value: props.sportModes.reduce((count, item) => count + item.positions.length, 0),
        description: 'Relações de posição ativas distribuídas pelas modalidades.',
    },
];

function destroySportMode(id: number): void {
    if (!window.confirm('Remover esta modalidade do catálogo?')) {
        return;
    }

    router.delete(`/admin/catalog/sport-modes/${id}`);
}
</script>

<template>
    <Head title="Sport Modes" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader
            eyebrow="Sports Catalog"
            title="Sport modes"
            description="Organize a base tática do produto. Cada modalidade define categorias, formações e posições compatíveis."
        >
            <template #actions>
                <Button as-child>
                    <Link :href="`${indexHref}/create`">
                        <Plus class="size-4" />
                        New sport mode
                    </Link>
                </Button>
            </template>
        </CatalogPageHeader>

        <CatalogMetricGrid :items="metrics" />

        <CatalogEmptyState
            v-if="sportModes.length === 0"
            title="No sport modes registered yet"
            description="Create the first modality to unlock tactical configuration for matches and championships."
        >
            <Button as-child>
                <Link :href="`${indexHref}/create`">Create first sport mode</Link>
            </Button>
        </CatalogEmptyState>

        <Card v-else class="gap-0 py-0">
            <CardContent class="overflow-x-auto px-0">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            <th class="px-6 py-4">Mode</th>
                            <th class="px-6 py-4">Key</th>
                            <th class="px-6 py-4">Relations</th>
                            <th class="px-6 py-4">Icon</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-for="sportMode in sportModes" :key="sportMode.id" class="align-top">
                            <td class="px-6 py-5">
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ resolveCatalogMessage('pt-BR', sportMode.label_key) }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ resolveCatalogMessage('pt-BR', sportMode.description_key ?? '') }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ sportMode.key }}</td>
                            <td class="px-6 py-5">
                                <div class="flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-200">
                                        {{ sportMode.categories.length }} categories
                                    </span>
                                    <span class="rounded-full bg-sky-100 px-3 py-1 font-medium text-sky-800 dark:bg-sky-950/50 dark:text-sky-200">
                                        {{ sportMode.formations.length }} formations
                                    </span>
                                    <span class="rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                                        {{ sportMode.positions.length }} positions
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ sportMode.icon ?? '—' }}</td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="`${indexHref}/${sportMode.id}/edit`">
                                            <Pencil class="size-4" />
                                            Edit
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm" @click="destroySportMode(sportMode.id)">
                                        <Trash2 class="size-4" />
                                        Remove
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
