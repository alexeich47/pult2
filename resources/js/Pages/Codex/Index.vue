<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';

const { t, tRaw } = useTranslations();

const SECTIONS: { icon: string; key: 'mission' | 'values' | 'conduct' | 'standards' }[] = [
    { icon: '🎯', key: 'mission' },
    { icon: '⚖️', key: 'values' },
    { icon: '🤝', key: 'conduct' },
    { icon: '📋', key: 'standards' },
];

function items(key: 'mission' | 'values' | 'conduct' | 'standards'): string[] {
    return tRaw<string[]>(`codex.item.${key}`, []);
}
</script>

<template>
    <Head :title="t('codex.title')" />

    <AppLayout>
        <div class="mx-auto max-w-5xl px-6 py-8">
            <h1 class="text-2xl font-semibold text-slate-900">{{ t('codex.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ t('codex.subtitle') }}</p>

            <p class="mt-6 max-w-3xl rounded-lg border border-indigo-100 bg-indigo-50 px-5 py-4 text-sm leading-relaxed text-indigo-900">
                {{ t('codex.intro') }}
            </p>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">
                <article
                    v-for="sec in SECTIONS"
                    :key="sec.key"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <div class="mb-3 flex items-center gap-3">
                        <span class="text-2xl" aria-hidden="true">{{ sec.icon }}</span>
                        <h2 class="text-lg font-semibold text-slate-900">{{ t(`codex.section.${sec.key}`) }}</h2>
                    </div>
                    <ul class="space-y-2 text-sm text-slate-700">
                        <li
                            v-for="(item, i) in items(sec.key)"
                            :key="i"
                            class="flex gap-2 leading-relaxed"
                        >
                            <span class="mt-1 inline-block h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-400"></span>
                            <span>{{ item }}</span>
                        </li>
                    </ul>
                </article>
            </div>
        </div>
    </AppLayout>
</template>
