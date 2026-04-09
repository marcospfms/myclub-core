<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import CatalogPageHeader from '@/components/catalog/CatalogPageHeader.vue';
import SportModeForm from '@/pages/admin/catalog/sport-modes/Partials/SportModeForm.vue';
import type { Category, Formation, Position } from '@/types';

defineProps<{
    categories: Category[];
    formations: Formation[];
    positions: Position[];
}>();

const indexHref = '/admin/catalog/sport-modes';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Catalog', href: indexHref },
            { title: 'Sport Modes', href: indexHref },
            { title: 'Create', href: `${indexHref}/create` },
        ],
    },
});

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
    form.post(indexHref);
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
            :categories="categories"
            :formations="formations"
            :positions="positions"
            submit-label="Create sport mode"
            :cancel-href="indexHref"
            @submit="submit"
        />
    </div>
</template>
