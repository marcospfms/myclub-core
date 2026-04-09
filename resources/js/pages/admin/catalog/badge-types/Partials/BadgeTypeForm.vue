<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TranslationKeyPreview from '@/components/catalog/TranslationKeyPreview.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { BadgeScope } from '@/types';

type BadgeTypeFormState = {
    name: string;
    label_key: string;
    description_key: string;
    icon: string;
    scope: BadgeScope;
    errors: Record<string, string | undefined>;
    processing: boolean;
};

defineProps<{
    form: BadgeTypeFormState;
    submitLabel: string;
    cancelHref: string;
}>();

defineEmits<{
    (e: 'submit'): void;
}>();

const scopeOptions: { value: BadgeScope; label: string }[] = [
    { value: 'championship', label: 'Championship' },
    { value: 'friendly', label: 'Friendly match' },
    { value: 'career', label: 'Career' },
    { value: 'seasonal', label: 'Seasonal' },
];
</script>

<template>
    <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]" @submit.prevent="$emit('submit')">
        <Card class="gap-0 py-0">
            <CardHeader class="border-b pt-6">
                <CardTitle>Badge contract</CardTitle>
                <CardDescription>
                    Tipos de badge têm um slug estável, chaves de tradução e escopo de concessão.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-5 py-6">
                <div class="grid gap-2">
                    <Label for="badge-name">Slug name</Label>
                    <Input id="badge-name" v-model="form.name" placeholder="golden_ball" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="badge-icon">Icon key</Label>
                    <Input id="badge-icon" v-model="form.icon" placeholder="award" />
                    <InputError :message="form.errors.icon" />
                </div>

                <div class="grid gap-2">
                    <Label for="badge-label-key">Label key</Label>
                    <Input id="badge-label-key" v-model="form.label_key" placeholder="badges.golden_ball.label" />
                    <InputError :message="form.errors.label_key" />
                </div>

                <div class="grid gap-2">
                    <Label for="badge-description-key">Description key</Label>
                    <Input id="badge-description-key" v-model="form.description_key" placeholder="badges.golden_ball.description" />
                    <InputError :message="form.errors.description_key" />
                </div>

                <div class="grid gap-2">
                    <Label for="badge-scope">Scope</Label>
                    <Select v-model="form.scope">
                        <SelectTrigger id="badge-scope" class="w-full">
                            <SelectValue placeholder="Select scope" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="option in scopeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.scope" />
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Cancelar</a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <TranslationKeyPreview :label-key="form.label_key || 'badges.preview.label'" :description-key="form.description_key || null" />
    </form>
</template>
