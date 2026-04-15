<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface Term {
    term: string;
    def: string;
}

const { t, tRaw } = useTranslations();

const allTerms = computed<Term[]>(() => tRaw<Term[]>('dictionary.terms', []));

const query = ref('');

const filtered = computed<Term[]>(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return allTerms.value;
    return allTerms.value.filter(
        (t) =>
            t.term.toLowerCase().includes(q) ||
            t.def.toLowerCase().includes(q),
    );
});

function firstLetter(term: Term): string {
    return term.term.charAt(0).toUpperCase();
}

function isLetterHeader(i: number): boolean {
    if (i === 0) return true;
    return firstLetter(filtered.value[i - 1]) !== firstLetter(filtered.value[i]);
}
</script>

<template>
    <Head :title="t('dictionary.title')" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-6 py-8">
            <h1 class="text-2xl font-semibold text-slate-900">{{ t('dictionary.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ t('dictionary.subtitle', { count: allTerms.length }) }}</p>

            <div class="relative mt-6">
                <svg
                    class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input
                    v-model="query"
                    type="text"
                    :placeholder="t('dictionary.search_ph')"
                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
            </div>

            <div v-if="filtered.length === 0" class="mt-10 rounded-lg border border-dashed border-slate-200 bg-white py-12 text-center">
                <div class="text-4xl">🔍</div>
                <div class="mt-2 text-sm text-slate-500">{{ t('dictionary.empty') }}</div>
            </div>

            <div v-else class="mt-6 space-y-1">
                <div
                    v-for="(term, i) in filtered"
                    :key="term.term"
                    class="flex gap-4"
                >
                    <div class="w-10 shrink-0 pt-3 text-right">
                        <span
                            v-if="isLetterHeader(i)"
                            class="inline-block rounded bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-700"
                        >
                            {{ firstLetter(term) }}
                        </span>
                    </div>
                    <div class="flex-1 border-b border-slate-200 py-3">
                        <div class="font-semibold text-slate-900">{{ term.term }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ term.def }}</div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
