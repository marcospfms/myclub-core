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
    create as createCategory,
    destroy as destroyCategoryRoute,
    edit as editCategory,
    index as categoriesIndex,
} from '@/routes/admin/catalog/categories';
import type { CatalogMetricItem, Category } from '@/types';

const props = defineProps<{
    categories: { data: Category[] };
}>();

const indexHref = categoriesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: categoriesIndex.url() },
            { title: 'Categories', href: categoriesIndex.url() },
        ],
    },
});

const metrics: CatalogMetricItem[] = [
    { label: 'Total categories', value: props.categories.data.length, description: 'Faixas etárias e agrupamentos esportivos disponíveis.' },
    { label: 'Distinct keys', value: new Set(props.categories.data.map((item) => item.key)).size, description: 'Chaves únicas já publicadas para integrações.' },
    { label: 'Named entries', value: props.categories.data.filter((item) => item.name.length > 0).length, description: 'Entradas com nomenclatura administrativa pronta para uso.' },
];

function destroyCategory(id: number): void {
    if (!window.confirm('Remover esta categoria?')) {
        return;
    }

    router.delete(destroyCategoryRoute.url(id));
}
</script>

<template>
    <Head title="Categories" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader
            eyebrow="Sports Catalog"
            title="Categories"
            description="Mantenha faixas e agrupamentos consistentes para inscrições, elencos e filtros públicos."
        >
            <template #actions>
                <Button as-child>
                    <Link :href="createCategory.url()"><Plus class="size-4" />New category</Link>
                </Button>
            </template>
        </CatalogPageHeader>

        <CatalogMetricGrid :items="metrics" />

        <CatalogEmptyState v-if="categories.data.length === 0" title="No categories yet" description="Crie categorias administrativas para começar a estruturar as modalidades.">
            <Button as-child>
                <Link :href="createCategory.url()">Create first category</Link>
            </Button>
        </CatalogEmptyState>

        <Card v-else class="gap-0 py-0">
            <CardContent class="overflow-x-auto px-0">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                    <thead class="bg-slate-50/80 dark:bg-slate-950/60">
                        <tr class="text-left text-xs uppercase tracking-[0.18em] text-muted-foreground">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Key</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-for="category in categories.data" :key="category.id">
                            <td class="px-6 py-5 font-medium">{{ category.name }}</td>
                            <td class="px-6 py-5 text-sm text-muted-foreground">{{ category.key }}</td>
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <Button as-child variant="outline" size="sm">
                                        <Link :href="editCategory.url(category.id)"><Pencil class="size-4" />Edit</Link>
                                    </Button>
                                    <Button variant="outline" size="sm" @click="destroyCategory(category.id)">
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
