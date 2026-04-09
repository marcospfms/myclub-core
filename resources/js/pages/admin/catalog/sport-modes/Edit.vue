<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import SportModeForm from '@/pages/admin/catalog/sport-modes/Partials/SportModeForm.vue';
import { dashboard } from '@/routes';
import {
    edit as editSportMode,
    index as sportModesIndex,
    update as updateSportMode,
} from '@/routes/admin/catalog/sport-modes';
import type { Category, Formation, Position, SportMode } from '@/types';

const props = defineProps<{
    sportMode: { data: SportMode };
    categories: { data: Category[] };
    formations: { data: Formation[] };
    positions: { data: Position[] };
}>();

const indexHref = sportModesIndex.url();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: dashboard.url() },
            { title: 'Catalog', href: sportModesIndex.url() },
            { title: 'Sport Modes', href: sportModesIndex.url() },
            { title: 'Edit', href: sportModesIndex.url() },
        ],
    },
});

const form = useForm({
    key: props.sportMode.data.key,
    label_key: props.sportMode.data.label_key,
    description_key: props.sportMode.data.description_key ?? '',
    icon: props.sportMode.data.icon ?? '',
    category_ids: props.sportMode.data.categories.map((item) => item.id),
    formation_ids: props.sportMode.data.formations.map((item) => item.id),
    position_ids: props.sportMode.data.positions.map((item) => item.id),
});

function submit(): void {
    form.put(updateSportMode.url(props.sportMode.data.id));
}
</script>

<template>
    <Head title="Edit Sport Mode" />

    <div class="space-y-6 px-4 py-4 md:px-6">
        <CatalogPageHeader
            eyebrow="Sports Catalog"
            title="Edit sport mode"
            description="Atualize a identidade da modalidade e os vínculos táticos disponíveis para os produtos cliente e admin."
        />

        <SportModeForm
            :form="form"
            :categories="props.categories.data"
            :formations="props.formations.data"
            :positions="props.positions.data"
            submit-label="Save changes"
            :cancel-href="indexHref"
            @submit="submit"
        />
    </div>
</template>
