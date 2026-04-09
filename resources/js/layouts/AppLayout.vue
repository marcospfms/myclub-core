<script setup lang="ts">
import AppFlashToast from '@/components/AppFlashToast.vue';
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <AppFlashToast />
        <slot />
    </AppLayout>
</template>
