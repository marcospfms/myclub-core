<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { store } from '@/routes/login';

defineOptions({
    layout: {
        title: 'Acesso administrativo',
        description: 'Entre com suas credenciais para operar cadastros, configurações e módulos internos do MyClub.',
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head title="Login" />

    <div
        v-if="status"
        class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-medium text-emerald-700"
    >
        {{ status }}
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-6"
    >
        <div class="flex items-center justify-center">
            <Badge variant="outline" class="rounded-full border-[hsl(var(--border)/0.85)] bg-[hsl(var(--background)/0.9)] px-3 py-1 text-[11px] tracking-[0.18em] uppercase text-muted-foreground">
                Painel interno
            </Badge>
        </div>

        <div class="grid gap-6">
            <div class="grid gap-2">
                <Label for="email">Email administrativo</Label>
                <Input
                    id="email"
                    type="email"
                    name="email"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="email"
                    placeholder="Digite o email"
                />
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password">Senha</Label>
                <PasswordInput
                    id="password"
                    name="password"
                    required
                    :tabindex="2"
                    autocomplete="current-password"
                    placeholder="Digite sua senha"
                />
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <Label for="remember" class="flex items-center space-x-3">
                    <Checkbox id="remember" name="remember" :tabindex="3" />
                    <span>Manter sessão ativa</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-4 h-11 w-full"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                Entrar no administrativo
            </Button>
        </div>
    </Form>
</template>
