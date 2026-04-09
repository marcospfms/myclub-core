<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import SelectionMatrix from '@/components/catalog/SelectionMatrix.vue';
import TranslationKeyPreview from '@/components/catalog/TranslationKeyPreview.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { Category, Formation, Position } from '@/types';

type SportModeFormState = {
    key: string;
    label_key: string;
    description_key: string;
    icon: string;
    category_ids: number[];
    formation_ids: number[];
    position_ids: number[];
    errors: Record<string, string | undefined>;
    processing: boolean;
};

defineProps<{
    form: SportModeFormState;
    categories: Category[];
    formations: Formation[];
    positions: Position[];
    submitLabel: string;
    cancelHref: string;
}>();

defineEmits<{
    (e: 'submit'): void;
}>();
</script>

<template>
    <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]" @submit.prevent="$emit('submit')">
        <div class="space-y-6">
            <Card class="gap-0 py-0">
                <CardHeader class="border-b pt-6">
                    <CardTitle>Core identity</CardTitle>
                    <CardDescription>
                        Defina a chave estável da modalidade e as chaves de tradução consumidas pelos clientes.
                    </CardDescription>
                </CardHeader>
                <CardContent class="grid gap-5 py-6 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="sport-mode-key">Key</Label>
                        <Input id="sport-mode-key" v-model="form.key" placeholder="campo" />
                        <InputError :message="form.errors.key" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="sport-mode-icon">Icon key</Label>
                        <Input id="sport-mode-icon" v-model="form.icon" placeholder="map" />
                        <InputError :message="form.errors.icon" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="sport-mode-label-key">Label key</Label>
                        <Input
                            id="sport-mode-label-key"
                            v-model="form.label_key"
                            placeholder="sport_modes.campo.label"
                        />
                        <InputError :message="form.errors.label_key" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="sport-mode-description-key">Description key</Label>
                        <Input
                            id="sport-mode-description-key"
                            v-model="form.description_key"
                            placeholder="sport_modes.campo.description"
                        />
                        <InputError :message="form.errors.description_key" />
                    </div>
                </CardContent>
            </Card>

            <SelectionMatrix
                v-model="form.category_ids"
                title="Categories"
                description="Categorias disponíveis para esta modalidade."
                :items="categories.map((item) => ({ id: item.id, label: item.name, description: item.key }))"
            />
            <InputError :message="form.errors.category_ids" />

            <SelectionMatrix
                v-model="form.formation_ids"
                title="Formations"
                description="Formações táticas compatíveis com a modalidade."
                :items="formations.map((item) => ({ id: item.id, label: item.name, description: item.key }))"
            />
            <InputError :message="form.errors.formation_ids" />

            <SelectionMatrix
                v-model="form.position_ids"
                title="Positions"
                description="Posições que poderão ser usadas em escalações e convites."
                :items="positions.map((item) => ({ id: item.id, label: item.abbreviation, description: item.key }))"
            />
            <InputError :message="form.errors.position_ids" />

            <div class="flex items-center gap-3">
                <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
                <Button as-child variant="outline">
                    <a :href="cancelHref">Cancelar</a>
                </Button>
            </div>
        </div>

        <div class="space-y-6">
            <TranslationKeyPreview
                :label-key="form.label_key || 'sport_modes.preview.label'"
                :description-key="form.description_key || null"
            />

            <Card class="gap-0 py-0">
                <CardHeader class="border-b pt-6">
                    <CardTitle>Sideline notes</CardTitle>
                    <CardDescription>
                        Mantenha a `key` imutável depois de publicada e use `label_key` para textos visíveis.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3 py-6 text-sm text-muted-foreground">
                    <p>As relações com categorias, formações e posições definem o universo tático disponível para a modalidade.</p>
                    <p>Evite trocar a `key` de uma modalidade existente; prefira criar uma nova entrada se a semântica mudar.</p>
                </CardContent>
            </Card>
        </div>
    </form>
</template>
