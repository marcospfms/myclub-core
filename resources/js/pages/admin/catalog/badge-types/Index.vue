<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Pencil, Plus, Trash2 } from 'lucide-vue-next';
import CatalogEmptyState from '@/components/catalog/CatalogEmptyState.vue';
import CatalogMetricGrid from '@/components/catalog/CatalogMetricGrid.vue';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { resolveCatalogIcon } from '@/catalog/badgeIcons';
import { resolveCatalogMessage } from '@/i18n/catalog';
import { dashboard } from '@/routes';
import {
    create as createBadgeType,
    destroy as destroyBadgeTypeRoute,
    edit as editBadgeType,
    index as badgeTypesIndex,
} from '@/routes/admin/catalog/badge-types';
import type { BadgeType, CatalogMetricItem } from '@/types';

const props = defineProps<{
    badgeTypes: { data: BadgeType[] };
}>();

const indexHref = badgeTypesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: badgeTypesIndex.url() },
            { title: 'Badge Types', href: badgeTypesIndex.url() },
        ],
    },
});

const metrics: CatalogMetricItem[] = [
    { label: 'Total badges', value: props.badgeTypes.data.length, description: 'Tipos de badge disponíveis para carreira, temporada e campeonatos.' },
    { label: 'Championship scope', value: props.badgeTypes.data.filter((item) => item.scope === 'championship').length, description: 'Badges ligadas a distribuição em campeonatos.' },
    { label: 'Career scope', value: props.badgeTypes.data.filter((item) => item.scope === 'career').length, description: 'Badges relacionadas a evolução individual do jogador.' },
];

function destroyBadgeType(id: number): void {
    if (!window.confirm('Remover este tipo de badge?')) {
        return;
    }

    router.delete(destroyBadgeTypeRoute.url(id));
}
</script>

<template>
    <Head title="Badge Types" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader eyebrow="Recognition Catalog" title="Badge types" description="Gerencie os símbolos de reconhecimento usados por campeonatos, carreira e eventos sazonais." >
            <template #actions>
                <Button as-child><Link :href="createBadgeType.url()"><Plus class="size-4" />New badge type</Link></Button>
            </template>
        </CatalogPageHeader>

        <CatalogMetricGrid :items="metrics" />

        <CatalogEmptyState v-if="badgeTypes.data.length === 0" title="No badge types yet" description="Cadastre os tipos de badge para destravar distribuição e reconhecimento no ecossistema MyClub.">
            <Button as-child><Link :href="createBadgeType.url()">Create first badge type</Link></Button>
        </CatalogEmptyState>

        <Card v-else class="gap-0 py-0">
            <CardContent class="overflow-x-auto px-0">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            <th class="px-6 py-4">Badge</th>
                            <th class="px-6 py-4">Slug</th>
                            <th class="px-6 py-4">Scope</th>
                            <th class="px-6 py-4">Icon</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-for="badgeType in badgeTypes.data" :key="badgeType.id">
                            <td class="px-6 py-5">
                                <div class="space-y-1">
                                    <p class="font-semibold">{{ resolveCatalogMessage('pt-BR', badgeType.label_key) }}</p>
                                    <p class="text-sm text-muted-foreground">{{ badgeType.description_key ? resolveCatalogMessage('pt-BR', badgeType.description_key) : 'No description key' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ badgeType.name }}</td>
                            <td class="px-6 py-5">
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium uppercase tracking-[0.14em] text-amber-800 dark:bg-amber-950/50 dark:text-amber-200">
                                    {{ badgeType.scope }}
                                </span>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3 text-sm text-muted-foreground">
                                    <component
                                        :is="resolveCatalogIcon(badgeType.icon)"
                                        v-if="resolveCatalogIcon(badgeType.icon)"
                                        class="size-4"
                                    />
                                    <span>{{ badgeType.icon ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="editBadgeType.url(badgeType.id)"><Pencil class="size-4" />Edit</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" @click="destroyBadgeType(badgeType.id)">
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
