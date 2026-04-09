<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import SportModeForm from '@/pages/admin/catalog/sport-modes/Partials/SportModeForm.vue';
import { dashboard } from '@/routes';
import {
    create as createSportMode,
    index as sportModesIndex,
    store as storeSportMode,
} from '@/routes/admin/catalog/sport-modes';
import type { Category, Formation, Position } from '@/types';

const indexHref = sportModesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: sportModesIndex.url() },
            { title: 'Sport Modes', href: sportModesIndex.url() },
            { title: 'Create', href: createSportMode.url() },
        ],
    },
});

const props = defineProps<{
    categories: { data: Category[] };
    formations: { data: Formation[] };
    positions: { data: Position[] };
}>();

const form = useForm({
    key: '',
    label_key: '',
    description_key: '',
    icon: '',
    category_ids: [] as number[],
    formation_ids: [] as number[],
    position_ids: [] as number[],
});

function submit(): void {
    form.post(storeSportMode.url());
}
</script>

<template>
    <Head title="Create Sport Mode" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader
            eyebrow="Sports Catalog"
            title="Create sport mode"
            description="Cadastre uma nova modalidade e defina o pacote tático que ela libera no restante da plataforma."
        />

        <SportModeForm
            :form="form"
            :categories="props.categories.data"
            :formations="props.formations.data"
            :positions="props.positions.data"
            submit-label="Create sport mode"
            :cancel-href="indexHref"
            @submit="submit"
        />
    </div>
</template>
