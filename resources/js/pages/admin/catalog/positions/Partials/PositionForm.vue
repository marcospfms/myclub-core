<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TranslationKeyPreview from '@/components/catalog/TranslationKeyPreview.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type PositionFormState = {
    key: string;
    label_key: string;
    description_key: string;
    icon: string;
    abbreviation: string;
    errors: Record<string, string | undefined>;
    processing: boolean;
};

defineProps<{
    form: PositionFormState;
    submitLabel: string;
    cancelHref: string;
}>();

defineEmits<{
    (e: 'submit'): void;
}>();
</script>

<template>
    <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]" @submit.prevent="$emit('submit')">
        <Card class="gap-0 py-0">
            <CardHeader class="border-b">
                <CardTitle>Position identity</CardTitle>
                <CardDescription>
                    A posição precisa de abreviação estável para lineup e `label_key` para os clientes.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-5 py-6 md:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="position-key">Key</Label>
                    <Input id="position-key" v-model="form.key" placeholder="goleiro" />
                    <InputError :message="form.errors.key" />
                </div>

                <div class="grid gap-2">
                    <Label for="position-abbreviation">Abbreviation</Label>
                    <Input id="position-abbreviation" v-model="form.abbreviation" placeholder="GOL" maxlength="3" />
                    <InputError :message="form.errors.abbreviation" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="position-label-key">Label key</Label>
                    <Input id="position-label-key" v-model="form.label_key" placeholder="positions.goleiro.label" />
                    <InputError :message="form.errors.label_key" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="position-description-key">Description key</Label>
                    <Input id="position-description-key" v-model="form.description_key" placeholder="positions.goleiro.description" />
                    <InputError :message="form.errors.description_key" />
                </div>

                <div class="grid gap-2 md:col-span-2">
                    <Label for="position-icon">Icon key</Label>
                    <Input id="position-icon" v-model="form.icon" placeholder="shield" />
                    <InputError :message="form.errors.icon" />
                </div>

                <div class="flex items-center gap-3 md:col-span-2">
                    <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Cancelar</a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <TranslationKeyPreview :label-key="form.label_key || 'positions.preview.label'" :description-key="form.description_key || null" />
    </form>
</template>
