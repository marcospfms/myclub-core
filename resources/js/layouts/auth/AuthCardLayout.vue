<script setup lang="ts">
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-background p-6 md:p-10"
    >
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(18,106,67,0.16),transparent_38%),linear-gradient(180deg,rgba(14,21,18,0.02),transparent_40%)]" />

        <div class="pointer-events-none absolute inset-x-0 top-0 h-40 border-b border-[hsl(var(--border)/0.55)] bg-[linear-gradient(180deg,rgba(14,21,18,0.05),transparent)]" />

        <div class="relative flex w-full max-w-md flex-col gap-6">
            <div class="flex flex-col gap-6">
                <Card class="rounded-2xl border-[hsl(var(--border)/0.7)] bg-card/95 shadow-[0_18px_60px_-28px_rgba(14,21,18,0.35)] backdrop-blur">
                    <CardHeader class="px-8 pt-0 pb-0 text-center md:px-10">
                        <div class="flex items-center justify-between gap-4 border-b border-[hsl(var(--border)/0.7)] pb-4">
                            <img
                                src="/brand/symbol.svg"
                                alt="MyClub"
                                class="size-20"
                            />
                            <div class="text-right">
                                <span class="block text-sm font-semibold tracking-[0.16em] text-primary uppercase">MyClub</span>
                                <span class="block text-xs text-muted-foreground">Administração</span>
                            </div>

                        </div>

                        <div class="pt-0">
                            <CardTitle class="text-xl text-foreground">{{ title }}</CardTitle>
                            <CardDescription>
                                {{ description }}
                            </CardDescription>
                        </div>
                    </CardHeader>
                    <CardContent class="px-8 pb-8 md:px-10">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
