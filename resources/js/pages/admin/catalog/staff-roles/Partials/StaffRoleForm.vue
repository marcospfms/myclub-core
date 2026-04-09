<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TranslationKeyPreview from '@/components/catalog/TranslationKeyPreview.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type StaffRoleFormState = {
    name: string;
    label_key: string;
    description_key: string;
    icon: string;
    errors: Record<string, string | undefined>;
    processing: boolean;
};

defineProps<{
    form: StaffRoleFormState;
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
                <CardTitle>Staff role identity</CardTitle>
                <CardDescription>
                    A `name` funciona como slug técnico. Os clientes exibem o papel via chaves de tradução.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-5 py-6">
                <div class="grid gap-2">
                    <Label for="staff-role-name">Slug name</Label>
                    <Input id="staff-role-name" v-model="form.name" placeholder="head_coach" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="staff-role-icon">Icon key</Label>
                    <Input id="staff-role-icon" v-model="form.icon" placeholder="whistle" />
                    <InputError :message="form.errors.icon" />
                </div>

                <div class="grid gap-2">
                    <Label for="staff-role-label-key">Label key</Label>
                    <Input id="staff-role-label-key" v-model="form.label_key" placeholder="staff_roles.head_coach.label" />
                    <InputError :message="form.errors.label_key" />
                </div>

                <div class="grid gap-2">
                    <Label for="staff-role-description-key">Description key</Label>
                    <Input id="staff-role-description-key" v-model="form.description_key" placeholder="staff_roles.head_coach.description" />
                    <InputError :message="form.errors.description_key" />
                </div>

                <div class="flex items-center gap-3">
                    <Button type="submit" :disabled="form.processing">{{ submitLabel }}</Button>
                    <Button as-child variant="outline">
                        <a :href="cancelHref">Cancelar</a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <TranslationKeyPreview :label-key="form.label_key || 'staff_roles.preview.label'" :description-key="form.description_key || null" />
    </form>
</template>
