<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type CategoryFormState = {
    key: string;
    name: string;
    errors: Record<string, string | undefined>;
    processing: boolean;
};

defineProps<{
    form: CategoryFormState;
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
                <CardTitle>Category profile</CardTitle>
                <CardDescription>
                    Categorias seguem um contrato simples: chave estável e nome administrativo.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-5 py-6">
                <div class="grid gap-2">
                    <Label for="category-key">Key</Label>
                    <Input id="category-key" v-model="form.key" placeholder="sub_20" />
                    <InputError :message="form.errors.key" />
                </div>

                <div class="grid gap-2">
                    <Label for="category-name">Name</Label>
                    <Input id="category-name" v-model="form.name" placeholder="Sub-20" />
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
                <CardTitle>Guideline</CardTitle>
                <CardDescription>
                    Use nomes curtos e consistentes. Esta entidade é administrativa e reaproveitada em toda a plataforma.
                </CardDescription>
            </CardHeader>
        </Card>
    </form>
</template>
