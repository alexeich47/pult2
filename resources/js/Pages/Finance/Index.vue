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

function cancelEdit() {
    editingId.value = null;
}

// Adding new entry
const showAddForm = ref(false);
const addForm = useForm({
    year: props.year,
    month: 1,
    target: 0,
    actual: 0,
    currency: 'USD',
});

// Figure out which months are unused for the current year
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

function confirmDelete(id: number) {
    deletingId.value = id;
}

function doDelete() {
    if (deletingId.value === null) return;
    router.delete(`/finance/${deletingId.value}`, {
        preserveScroll: true,
        onFinish: () => { deletingId.value = null; },
    });
}

// Year navigation
function changeYear(delta: number) {
    router.get('/finance', { year: props.year + delta }, { preserveState: true });
}

// Chart data
const chartData = computed(() =>
    props.entries.map((e) => ({
        month: e.month,
        target: Number(e.target),
        actual: Number(e.actual),
    })),
);

function formatDiff(target: string, actual: string): string {
    const t = Number(target);
    const a = Number(actual);
    if (a === 0) return '---';
    const diff = a - t;
    const sign = diff >= 0 ? '+' : '';
    return `${sign}$${diff.toLocaleString()}`;
}

function diffClass(target: string, actual: string): string {
    const diff = Number(actual) - Number(target);
    if (Number(actual) === 0) return 'text-slate-400';
    return diff >= 0 ? 'text-emerald-600' : 'text-rose-600';
}
</script>

<template>
    <Head :title="t('finance.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900">{{ t('finance.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ t('finance.sub') }}</p>
            </div>

            <!-- MVR Chart -->
            <div class="mb-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h2 class="mb-1 text-base font-semibold text-slate-800">{{ t('finance.mvr_title') }}</h2>
                <p class="mb-4 text-sm text-slate-500">{{ t('finance.mvr_sub') }}</p>
                <MvrChart v-if="chartData.length > 0" :data="chartData" />
                <p v-else class="py-8 text-center text-sm text-slate-400">No data for this year</p>
            </div>

            <!-- Year selector + Add button -->
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <button
                        class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200"
                        @click="changeYear(-1)"
                    >
                        &larr;
                    </button>
                    <span class="text-sm font-semibold text-slate-700">{{ t('finance.year') }}: {{ year }}</span>
                    <button
                        class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200"
                        @click="changeYear(1)"
                    >
                        &rarr;
                    </button>
                </div>
                <button
                    v-if="can.create && availableMonths.length > 0"
                    class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-600"
                    @click="openAddForm"
                >
                    {{ t('finance.add_entry') }}
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-5 py-3">{{ t('finance.col.month') }}</th>
                            <th class="px-5 py-3 text-right">{{ t('finance.col.target') }}</th>
                            <th class="px-5 py-3 text-right">{{ t('finance.col.actual') }}</th>
                            <th class="px-5 py-3 text-right">{{ t('finance.col.diff') }}</th>
                            <th class="px-5 py-3 text-right w-28"></th>
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
                            <td class="px-5 py-2"></td>
                            <td class="px-5 py-2 text-right">
                                <button class="mr-2 text-indigo-600 hover:text-indigo-800" @click="submitAdd">Save</button>
                                <button class="text-slate-500 hover:text-slate-700" @click="showAddForm = false">Cancel</button>
                            </td>
                        </tr>

                        <tr
                            v-for="entry in entries"
                            :key="entry.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-5 py-2.5 font-medium text-slate-700">{{ monthName(entry.month) }}</td>
                            <template v-if="editingId === entry.id">
                                <td class="px-5 py-2">
                                    <input v-model.number="editForm.target" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" />
                                </td>
                                <td class="px-5 py-2">
                                    <input v-model.number="editForm.actual" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" />
                                </td>
                                <td class="px-5 py-2.5 text-right"></td>
                                <td class="px-5 py-2 text-right">
                                    <button class="mr-2 text-indigo-600 hover:text-indigo-800" @click="saveEdit(entry)">Save</button>
                                    <button class="text-slate-500 hover:text-slate-700" @click="cancelEdit">Cancel</button>
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
                                        class="mr-2 text-slate-400 hover:text-indigo-600"
                                        @click="startEdit(entry)"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        v-if="can.delete"
                                        class="text-slate-400 hover:text-rose-600"
                                        @click="confirmDelete(entry.id)"
                                    >
                                        &times;
                                    </button>
                                </td>
                            </template>
                        </tr>

                        <tr v-if="entries.length === 0 && !showAddForm">
                            <td colspan="5" class="px-5 py-8 text-center text-slate-400">No data</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delete confirmation -->
        <ConfirmDialog
            :show="deletingId !== null"
            :title="t('finance.title')"
            :message="t('risks.delete_confirm')"
            @confirm="doDelete"
            @cancel="deletingId = null"
        />
    </AppLayout>
</template>
