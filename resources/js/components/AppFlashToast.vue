<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { AlertCircle, CheckCircle2, Info, TriangleAlert, X } from 'lucide-vue-next';
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { Alert, AlertDescription, AlertTitle, type AlertVariants } from '@/components/ui/alert';

type FlashPayload = {
    success?: string | null;
    warning?: string | null;
    neutral?: string | null;
    error?: string | null;
    status?: string | null;
};

const page = usePage();
const visible = ref(false);
const dismissTimer = ref<ReturnType<typeof setTimeout> | null>(null);

const flash = computed(() => (page.props.flash ?? {}) as FlashPayload);
const tone = computed<'success' | 'warning' | 'neutral' | 'danger' | null>(() => {
    if (flash.value.error) {
        return 'danger';
    }

    if (flash.value.warning) {
        return 'warning';
    }

    if (flash.value.neutral || flash.value.status) {
        return 'neutral';
    }

    if (flash.value.success) {
        return 'success';
    }

    return null;
});

const variant = computed<NonNullable<AlertVariants['variant']>>(() => {
    switch (tone.value) {
        case 'success':
            return 'success';
        case 'warning':
            return 'warning';
        case 'neutral':
            return 'neutral';
        case 'danger':
            return 'destructive';
        default:
            return 'neutral';
    }
});

const title = computed(() => {
    switch (tone.value) {
        case 'success':
            return 'Sucesso';
        case 'warning':
            return 'Atenção';
        case 'neutral':
            return 'Aviso';
        case 'danger':
            return 'Erro';
        default:
            return 'Aviso';
    }
});

const message = computed(
    () =>
        flash.value.success
        ?? flash.value.warning
        ?? flash.value.neutral
        ?? flash.value.error
        ?? flash.value.status
        ?? null,
);

function clearTimer(): void {
    if (dismissTimer.value) {
        clearTimeout(dismissTimer.value);
        dismissTimer.value = null;
    }
}

function showToast(nextMessage: string | null): void {
    clearTimer();

    if (!nextMessage) {
        visible.value = false;
        return;
    }

    visible.value = true;

    dismissTimer.value = setTimeout(() => {
        visible.value = false;
        dismissTimer.value = null;
    }, 4200);
}

watch(message, (nextMessage) => {
    showToast(nextMessage);
}, { immediate: true });

onBeforeUnmount(() => {
    clearTimer();
});
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="visible && message"
            class="pointer-events-none fixed top-4 right-4 z-50 w-full max-w-sm px-4"
        >
            <Alert
                :variant="variant"
                class="pointer-events-auto border shadow-lg backdrop-blur-sm"
            >
                <CheckCircle2
                    v-if="tone === 'success'"
                    class="size-4"
                />
                <TriangleAlert v-else-if="tone === 'warning'" class="size-4" />
                <Info v-else-if="tone === 'neutral'" class="size-4" />
                <AlertCircle v-else class="size-4" />

                <button
                    type="button"
                    class="absolute top-3 right-3 rounded-md p-1 text-muted-foreground transition hover:bg-black/5 hover:text-foreground dark:hover:bg-white/10"
                    @click="visible = false"
                >
                    <X class="size-4" />
                    <span class="sr-only">Fechar aviso</span>
                </button>

                <AlertTitle>{{ title }}</AlertTitle>
                <AlertDescription>{{ message }}</AlertDescription>
            </Alert>
        </div>
    </Transition>
</template>
