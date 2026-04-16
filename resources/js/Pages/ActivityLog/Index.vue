<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Paginated } from '../../types';

interface ActivityEntry {
    id: number;
    description: string;
    log_name: string;
    subject_type: string | null;
    subject_id: number | null;
    causer_name: string;
    causer_id: number | null;
    properties: Record<string, unknown>;
    created_at: string | null;
}

interface UserOption {
    id: number;
    name: string;
}

interface Props {
    entries: Paginated<ActivityEntry>;
    filters: {
        date_from: string | null;
        date_to: string | null;
        user_id: string | null;
        log_name: string | null;
    };
    users: UserOption[];
    logNames: string[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

const filterForm = ref({
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    user_id: props.filters.user_id ?? '',
    log_name: props.filters.log_name ?? '',
});

const LOG_ICONS: Record<string, string> = {
    employee: '\uD83D\uDC64',
    idea: '\uD83D\uDCA1',
    risk_entry: '\u26A0\uFE0F',
    service: '\uD83D\uDCE6',
};

function applyFilters() {
    const params: Record<string, string> = {};
    if (filterForm.value.date_from) params.date_from = filterForm.value.date_from;
    if (filterForm.value.date_to) params.date_to = filterForm.value.date_to;
    if (filterForm.value.user_id) params.user_id = filterForm.value.user_id;
    if (filterForm.value.log_name) params.log_name = filterForm.value.log_name;

    router.get('/activity-log', params, { preserveState: true, preserveScroll: true });
}

function clearFilters() {
    filterForm.value = { date_from: '', date_to: '', user_id: '', log_name: '' };
    router.get('/activity-log', {}, { preserveState: true, preserveScroll: true });
}

function formatDate(iso: string | null): string {
    if (!iso) return '\u2014';
    const d = new Date(iso);
    const day = String(d.getDate()).padStart(2, '0');
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${day}.${month}.${year} ${hours}:${minutes}`;
}

function changedKeys(properties: Record<string, unknown>): string[] {
    const attrs = properties.attributes as Record<string, unknown> | undefined;
    if (!attrs) return [];
    return Object.keys(attrs);
}

function getOldValue(properties: Record<string, unknown>, key: string): unknown {
    const old = properties.old as Record<string, unknown> | undefined;
    return old?.[key];
}

function getNewValue(properties: Record<string, unknown>, key: string): unknown {
    const attrs = properties.attributes as Record<string, unknown> | undefined;
    return attrs?.[key];
}

function hasOld(properties: Record<string, unknown>): boolean {
    return !!properties.old && typeof properties.old === 'object';
}

function formatValue(val: unknown): string {
    if (val === null || val === undefined) return 'null';
    if (typeof val === 'string') return val;
    return JSON.stringify(val);
}
</script>

<template>
    <Head :title="t('activity_log.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ t('activity_log.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ t('activity_log.sub') }}</p>
            </div>

            <!-- Filter bar -->
            <div class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('activity_log.filter.date_from') }}</label>
                    <input
                        v-model="filterForm.date_from"
                        type="date"
                        class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('activity_log.filter.date_to') }}</label>
                    <input
                        v-model="filterForm.date_to"
                        type="date"
                        class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                    />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('activity_log.filter.user') }}</label>
                    <select
                        v-model="filterForm.user_id"
                        class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                    >
                        <option value="">{{ t('activity_log.filter.all_users') }}</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ t('activity_log.filter.entity_type') }}</label>
                    <select
                        v-model="filterForm.log_name"
                        class="rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm text-slate-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200"
                    >
                        <option value="">{{ t('activity_log.filter.all_types') }}</option>
                        <option v-for="name in logNames" :key="name" :value="name">
                            {{ LOG_ICONS[name] ?? '' }} {{ name }}
                        </option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button
                        type="button"
                        class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white transition-colors hover:bg-indigo-700"
                        @click="applyFilters"
                    >
                        {{ t('activity_log.filter.apply') }}
                    </button>
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm text-slate-500 transition-colors hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200"
                        @click="clearFilters"
                    >
                        {{ t('activity_log.filter.clear') }}
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ t('activity_log.col.date') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ t('activity_log.col.user') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ t('activity_log.col.action') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ t('activity_log.col.entity') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-400">{{ t('activity_log.col.changes') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        <tr v-if="entries.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400">{{ t('activity_log.empty') }}</td>
                        </tr>
                        <tr v-for="entry in entries.data" :key="entry.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                            <td class="whitespace-nowrap px-4 py-3 text-xs text-slate-500 dark:text-slate-400">
                                {{ formatDate(entry.created_at) }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                {{ entry.causer_name }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                {{ entry.description }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 dark:text-slate-400">
                                <span class="mr-1">{{ LOG_ICONS[entry.log_name] ?? '' }}</span>
                                <span v-if="entry.subject_type">{{ entry.subject_type }}</span>
                                <span v-if="entry.subject_id" class="ml-1 font-mono text-xs text-slate-400">#{{ entry.subject_id }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="changedKeys(entry.properties).length > 0" class="flex flex-wrap gap-1">
                                    <template v-for="key in changedKeys(entry.properties)" :key="key">
                                        <span
                                            v-if="hasOld(entry.properties)"
                                            class="inline-flex items-center gap-1 rounded bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                                        >
                                            <span class="font-medium">{{ key }}:</span>
                                            <span class="text-red-500 line-through">{{ formatValue(getOldValue(entry.properties, key)) }}</span>
                                            <span class="text-slate-400">&rarr;</span>
                                            <span class="text-green-600 dark:text-green-400">{{ formatValue(getNewValue(entry.properties, key)) }}</span>
                                        </span>
                                        <span
                                            v-else
                                            class="inline-flex items-center gap-1 rounded bg-slate-100 px-1.5 py-0.5 text-[11px] text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                                        >
                                            <span class="font-medium">{{ key }}:</span>
                                            {{ formatValue(getNewValue(entry.properties, key)) }}
                                        </span>
                                    </template>
                                </div>
                                <span v-else class="text-xs text-slate-300 dark:text-slate-600">&mdash;</span>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <Pagination :links="entries.links" :from="entries.from" :to="entries.to" :total="entries.total" />
            </div>
        </div>
    </AppLayout>
</template>
