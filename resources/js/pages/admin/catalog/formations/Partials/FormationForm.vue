<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type FormationFormState = {
    key: string;
    name: string;
    errors: Record<string, string | undefined>;
    processing: boolean;
};

defineProps<{
    form: FormationFormState;
    submitLabel: string;
    cancelHref: string;
}>();

defineEmits<{
    (e: 'submit'): void;
}>();
</script>

<template>
    <form class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_18rem]" @submit.prevent="$emit('submit')">
        <Card class="gap-0 py-0">
            <CardHeader class="border-b pt-6">
                <CardTitle>Formation pattern</CardTitle>
                <CardDescription>
                    Use o mesmo valor em `key` e `name` quando a formação for autoexplicativa.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-5 py-6">
                <div class="grid gap-2">
                    <Label for="formation-key">Key</Label>
                    <Input id="formation-key" v-model="form.key" placeholder="4-4-2" />
                    <InputError :message="form.errors.key" />
                </div>

                <div class="grid gap-2">
                    <Label for="formation-name">Name</Label>
                    <Input id="formation-name" v-model="form.name" placeholder="4-4-2" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Cancelar</a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card class="gap-0 py-0">
            <CardHeader class="border-b pt-6">
                <CardTitle>Tactical note</CardTitle>
                <CardDescription>
                    Este catálogo alimenta convites, formações iniciais e visualizações táticas.
                </CardDescription>
            </CardHeader>
        </Card>
    </form>
</template>
