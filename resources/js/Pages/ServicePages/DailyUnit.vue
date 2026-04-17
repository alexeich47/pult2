<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface RecentRow { id: number; date: string; plan: number; fact: number }

const props = defineProps<{
    unitId: string;
    unitName: string;
    unitColor: string;
    pageTitle: string;
    icon: string;
    defaultDate: string;
    defaultPlan: number;
    existingFact: number;
    recent: RecentRow[];
}>();

const { t } = useTranslations();

const form = useForm({
    date: props.defaultDate,
    plan: props.defaultPlan,
    fact: props.existingFact,
});

function submit() {
    form.post(`/service-pages/daily/${props.unitId}`, { preserveScroll: true });
}

const MONTH_NAMES_RU = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
function formatLong(iso: string): string {
    const d = new Date(iso);
    return `${d.getDate()} ${MONTH_NAMES_RU[d.getMonth()]} ${d.getFullYear()}`;
}
function formatShort(iso: string): string {
    const d = new Date(iso);
    return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}`;
}

const diff = computed(() => Number(form.fact) - Number(form.plan));
const diffClass = computed(() => {
    if (!form.fact || Number(form.fact) === 0) return 'text-slate-400';
    return diff.value >= 0 ? 'text-emerald-600' : 'text-rose-600';
});
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-6 py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/service-pages" class="hover:text-indigo-600">{{ t('service_pages.title') }}</Link>
                <span>/</span>
                <span class="text-slate-700">{{ pageTitle }}</span>
            </div>

            <div class="mb-8 flex items-start gap-3">
                <span class="text-3xl">{{ icon }}</span>
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">
                        {{ pageTitle }}
                        <span
                            class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium text-white"
                            :style="{ backgroundColor: unitColor }"
                        >{{ unitName }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        {{ t('service_pages.daily.page_sub_prefix') }} {{ unitName }} {{ t('service_pages.daily.page_sub_suffix') }}
                    </p>
                </div>
            </div>

            <!-- Form card -->
            <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <p class="mb-4 text-sm text-slate-500 dark:text-slate-400">
                    {{ t('service_pages.daily.prompt_prefix') }} {{ unitName }} {{ t('service_pages.daily.prompt_for') }}
                    <strong class="text-slate-800 dark:text-slate-200">{{ formatLong(form.date) }}</strong>
                </p>

                <form class="grid gap-4 sm:grid-cols-[1fr_1fr_1fr_auto] sm:items-end" @submit.prevent="submit">
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">{{ t('service_pages.daily.date') }}</span>
                        <input
                            v-model="form.date"
                            type="date"
                            class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">{{ t('service_pages.daily.plan') }}</span>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">$</span>
                            <input
                                v-model.number="form.plan"
                                type="number"
                                min="0"
                                step="1"
                                class="w-full rounded-md border border-slate-300 bg-white py-2 pl-6 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                            />
                        </div>
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600 dark:text-slate-400">{{ t('service_pages.daily.fact') }}</span>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">$</span>
                            <input
                                v-model.number="form.fact"
                                type="number"
                                min="0"
                                step="1"
                                required
                                autofocus
                                class="w-full rounded-md border border-slate-300 bg-white py-2 pl-6 pr-3 text-sm font-semibold text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            />
                        </div>
                    </label>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.fact"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                    >
                        {{ t('service_pages.daily.save') }}
                    </button>
                </form>

                <div v-if="form.fact" class="mt-4 flex items-center gap-2 text-sm">
                    <span class="text-slate-500">{{ t('service_pages.daily.deviation') }}:</span>
                    <span class="font-semibold" :class="diffClass">
                        {{ diff >= 0 ? '+' : '−' }}${{ Math.abs(diff).toLocaleString() }}
                    </span>
                </div>
            </div>

            <!-- Recent entries -->
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    {{ t('service_pages.daily.recent') }}
                </div>
                <table v-if="recent.length > 0" class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-2">{{ t('service_pages.daily.date') }}</th>
                            <th class="px-5 py-2 text-right">{{ t('service_pages.daily.fact') }}</th>
                            <th class="px-5 py-2 text-right">{{ t('service_pages.daily.plan') }}</th>
                            <th class="px-5 py-2 text-right">{{ t('service_pages.daily.deviation') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="r in recent" :key="r.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-5 py-2 font-mono text-xs text-slate-700 dark:text-slate-300">{{ formatShort(r.date) }}</td>
                            <td class="px-5 py-2 text-right font-semibold text-slate-900 dark:text-slate-100">
                                {{ r.fact > 0 ? `$${r.fact.toLocaleString()}` : '—' }}
                            </td>
                            <td class="px-5 py-2 text-right text-slate-500">${{ r.plan.toLocaleString() }}</td>
                            <td class="px-5 py-2 text-right text-xs font-medium" :class="r.fact === 0 ? 'text-slate-400' : (r.fact - r.plan) >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                                <template v-if="r.fact > 0">
                                    {{ (r.fact - r.plan) >= 0 ? '+' : '−' }}${{ Math.abs(r.fact - r.plan).toLocaleString() }}
                                </template>
                                <template v-else>—</template>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-else class="px-5 py-8 text-center text-sm text-slate-400">{{ t('service_pages.daily.no_recent') }}</div>
            </div>
        </div>
    </AppLayout>
</template>
