<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import { useTranslations } from '../Composables/useTranslations';

interface Props {
    // i18n keys, e.g. "nav.strategy", "placeholder.wip"
    titleKey: string;
    subtitleKey: string;
    icon?: string;
}

const props = withDefaults(defineProps<Props>(), {
    icon: '🚧',
});

const { t } = useTranslations();
</script>

<template>
    <Head :title="t(props.titleKey)" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-6 py-16">
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-8 py-16 text-center shadow-sm">
                <div class="text-5xl" aria-hidden="true">{{ props.icon }}</div>
                <h1 class="mt-4 text-2xl font-semibold text-slate-900">
                    {{ t(props.titleKey) }}
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    {{ t(props.subtitleKey) }}
                </p>
                <p class="mt-8 text-xs text-slate-400">
                    Pult 2.0 · Phase 5 placeholder
                </p>
            </div>
        </div>
    </AppLayout>
</template>
