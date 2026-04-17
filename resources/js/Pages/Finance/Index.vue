<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import MvrChart from '../../Components/Pult/MvrChart.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { MvrEntry } from '../../types';

interface Props {
    entries: MvrEntry[];
    year: number;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

type Tab = 'chart' | 'data';
const activeTab = ref<Tab>('chart');

const MONTH_NAMES_RU = ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'];
const MONTH_NAMES_UK = ['Січень', 'Лютий', 'Березень', 'Квітень', 'Травень', 'Червень', 'Липень', 'Серпень', 'Вересень', 'Жовтень', 'Листопад', 'Грудень'];
const MONTH_NAMES_EN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

function monthName(month: number): string {
    const locale = document.documentElement.lang || 'ru';
    if (locale === 'uk') return MONTH_NAMES_UK[month - 1];
    if (locale === 'en') return MONTH_NAMES_EN[month - 1];
    return MONTH_NAMES_RU[month - 1];
}

// Inline editing
const editingId = ref<number | null>(null);
const editForm = useForm({ target: 0, actual: 0 });

function startEdit(entry: MvrEntry) {
    editingId.value = entry.id;
    editForm.target = Number(entry.target);
    editForm.actual = Number(entry.actual);
}

function saveEdit(entry: MvrEntry) {
    editForm.put(`/finance/${entry.id}`, {
        preserveScroll: true,
        onSuccess: () => { editingId.value = null; },
    });
}

function cancelEdit() { editingId.value = null; }

// Adding
const showAddForm = ref(false);
const addForm = useForm({ year: props.year, month: 1, target: 0, actual: 0, currency: 'USD' });

const usedMonths = computed(() => props.entries.map((e) => e.month));
const availableMonths = computed(() =>
    Array.from({ length: 12 }, (_, i) => i + 1).filter((m) => !usedMonths.value.includes(m)),
);

function openAddForm() {
    showAddForm.value = true;
    addForm.year = props.year;
    addForm.month = availableMonths.value[0] ?? 1;
    addForm.target = 0;
    addForm.actual = 0;
}

function submitAdd() {
    addForm.post('/finance', {
        preserveScroll: true,
        onSuccess: () => { showAddForm.value = false; },
    });
}

// Delete
const deletingId = ref<number | null>(null);

function doDelete() {
    if (deletingId.value === null) return;
    router.delete(`/finance/${deletingId.value}`, {
        preserveScroll: true,
        onFinish: () => { deletingId.value = null; },
    });
}

// Year nav
function changeYear(delta: number) {
    router.get('/finance', { year: props.year + delta }, { preserveState: true });
}

// Chart data
const chartData = computed(() =>
    props.entries.map((e) => ({ month: e.month, target: Number(e.target), actual: Number(e.actual) })),
);

function formatDiff(target: string, actual: string): string {
    const tv = Number(target); const av = Number(actual);
    if (av === 0) return '---';
    const diff = av - tv;
    return `${diff >= 0 ? '+' : ''}$${diff.toLocaleString()}`;
}

function diffClass(target: string, actual: string): string {
    if (Number(actual) === 0) return 'text-slate-400';
    return Number(actual) - Number(target) >= 0 ? 'text-emerald-600' : 'text-rose-600';
}

const TABS: { id: Tab; label: string; icon: string }[] = [
    { id: 'chart', label: 'График', icon: '📊' },
    { id: 'data', label: 'Данные', icon: '📝' },
];
</script>

<template>
    <Head :title="t('finance.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('finance.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('finance.sub') }}</p>
                </div>
                <!-- Year selector -->
                <div class="flex items-center gap-2">
                    <button class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200" @click="changeYear(-1)">&larr;</button>
                    <span class="text-sm font-bold text-slate-700">{{ year }}</span>
                    <button class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200" @click="changeYear(1)">&rarr;</button>
                </div>
            </div>

            <!-- Single card with tabs -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <!-- Tab bar -->
                <div class="flex border-b border-slate-200">
                    <button
                        v-for="tb in TABS"
                        :key="tb.id"
                        type="button"
                        class="relative flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors"
                        :style="{
                            backgroundColor: activeTab === tb.id ? 'var(--tab-active-bg, white)' : 'var(--tab-bg, #f8fafc)',
                            color: activeTab === tb.id ? '#6366f1' : '#94a3b8',
                        }"
                        @click="activeTab = tb.id"
                    >
                        <span>{{ tb.icon }}</span>
                        {{ tb.label }}
                        <span
                            v-if="activeTab === tb.id"
                            class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-500"
                        ></span>
                    </button>
                </div>

                <!-- Chart tab -->
                <div v-if="activeTab === 'chart'" class="p-6">
                    <h2 class="mb-1 text-base font-semibold text-slate-800">{{ t('finance.mvr_title') }}</h2>
                    <p class="mb-4 text-sm text-slate-500">{{ t('finance.mvr_sub') }}</p>
                    <MvrChart v-if="chartData.length > 0" :data="chartData" />
                    <p v-else class="py-12 text-center text-sm text-slate-400">No data for {{ year }}</p>
                </div>

                <!-- Data tab -->
                <div v-else>
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h2 class="text-sm font-semibold text-slate-800">{{ t('finance.mvr_title') }} — {{ year }}</h2>
                        <button
                            v-if="can.create && availableMonths.length > 0"
                            class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700"
                            @click="openAddForm"
                        >
                            + {{ t('finance.add_entry') }}
                        </button>
                    </div>

                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-3">{{ t('finance.col.month') }}</th>
                                <th class="px-5 py-3 text-right">{{ t('finance.col.target') }}</th>
                                <th class="px-5 py-3 text-right">{{ t('finance.col.actual') }}</th>
                                <th class="px-5 py-3 text-right">{{ t('finance.col.diff') }}</th>
                                <th class="w-24 px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Add row -->
                            <tr v-if="showAddForm" class="bg-indigo-50/40">
                                <td class="px-5 py-2">
                                    <select v-model.number="addForm.month" class="w-full rounded border border-slate-300 px-2 py-1 text-sm">
                                        <option v-for="m in availableMonths" :key="m" :value="m">{{ monthName(m) }}</option>
                                    </select>
                                </td>
                                <td class="px-5 py-2">
                                    <input v-model.number="addForm.target" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" />
                                </td>
                                <td class="px-5 py-2">
                                    <input v-model.number="addForm.actual" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" />
                                </td>
                                <td></td>
                                <td class="px-5 py-2 text-right">
                                    <button class="mr-1 text-xs text-indigo-600 hover:text-indigo-800" @click="submitAdd">✓</button>
                                    <button class="text-xs text-slate-400 hover:text-slate-600" @click="showAddForm = false">✕</button>
                                </td>
                            </tr>

                            <tr v-for="entry in entries" :key="entry.id" class="hover:bg-slate-50">
                                <td class="px-5 py-2.5 font-medium text-slate-700">{{ monthName(entry.month) }}</td>
                                <template v-if="editingId === entry.id">
                                    <td class="px-5 py-2">
                                        <input v-model.number="editForm.target" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" />
                                    </td>
                                    <td class="px-5 py-2">
                                        <input v-model.number="editForm.actual" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" />
                                    </td>
                                    <td></td>
                                    <td class="px-5 py-2 text-right">
                                        <button class="mr-1 text-xs text-indigo-600 hover:text-indigo-800" @click="saveEdit(entry)">✓</button>
                                        <button class="text-xs text-slate-400 hover:text-slate-600" @click="cancelEdit">✕</button>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-5 py-2.5 text-right text-slate-600">${{ Number(entry.target).toLocaleString() }}</td>
                                    <td class="px-5 py-2.5 text-right text-slate-600">
                                        {{ Number(entry.actual) > 0 ? `$${Number(entry.actual).toLocaleString()}` : '---' }}
                                    </td>
                                    <td class="px-5 py-2.5 text-right font-medium" :class="diffClass(entry.target, entry.actual)">
                                        {{ formatDiff(entry.target, entry.actual) }}
                                    </td>
                                    <td class="px-5 py-2.5 text-right">
                                        <button
                                            v-if="can.create"
                                            class="mr-1 text-slate-400 hover:text-indigo-600"
                                            @click="startEdit(entry)"
                                            title="Edit"
                                        >
                                            <svg class="inline h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                        </button>
                                        <button
                                            v-if="can.delete"
                                            class="text-slate-400 hover:text-rose-600"
                                            @click="deletingId = entry.id"
                                            title="Delete"
                                        >
                                            <svg class="inline h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </td>
                                </template>
                            </tr>

                            <tr v-if="entries.length === 0 && !showAddForm">
                                <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-400">No data for {{ year }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmDialog
            :show="deletingId !== null"
            :title="t('finance.title')"
            :message="t('risks.delete_confirm')"
            @confirm="doDelete"
            @cancel="deletingId = null"
        />
    </AppLayout>
</template>
