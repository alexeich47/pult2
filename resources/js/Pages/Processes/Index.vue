<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import ProcessFormModal from '../../Components/Pult/ProcessFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Paginated, Process, ProcessMaturity, Unit } from '../../types';

interface Props {
    processes: Paginated<Process>;
    allUnits: Unit[];
    employees: Employee[];
    categories: string[];
    maturityLevels: ProcessMaturity[];
    filters: Partial<Record<string, string>>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const MATURITY_COLORS: Record<ProcessMaturity, string> = {
    not_documented: '#ef4444',
    documented_testing: '#f59e0b',
    documented_digitized: '#3b82f6',
    fully_automated: '#22c55e',
};

const MATURITY_LABELS: Record<ProcessMaturity, { emoji: string; key: string }> = {
    not_documented: { emoji: '\uD83D\uDD34', key: 'processes.maturity.not_documented' },
    documented_testing: { emoji: '\uD83D\uDFE1', key: 'processes.maturity.documented_testing' },
    documented_digitized: { emoji: '\uD83D\uDD35', key: 'processes.maturity.documented_digitized' },
    fully_automated: { emoji: '\uD83D\uDFE2', key: 'processes.maturity.fully_automated' },
};

// Stats — count per maturity
const maturityCounts = computed(() => {
    const counts: Record<ProcessMaturity, number> = {
        not_documented: 0,
        documented_testing: 0,
        documented_digitized: 0,
        fully_automated: 0,
    };
    // Use total from paginated data — this only counts current page.
    // For accurate stats we count from loaded data.
    props.processes.data.forEach((p) => {
        if (counts[p.maturity] !== undefined) counts[p.maturity]++;
    });
    return counts;
});

const activeMaturityFilter = computed<ProcessMaturity | null>(() => {
    const m = props.filters.maturity;
    if (m && (m as string) in MATURITY_COLORS) return m as ProcessMaturity;
    return null;
});

function filterByMaturity(maturity: ProcessMaturity) {
    if (activeMaturityFilter.value === maturity) {
        // Clear filter
        router.get('/processes', {}, { preserveScroll: true, preserveState: true });
    } else {
        router.get('/processes', { filter: { maturity } }, { preserveScroll: true, preserveState: true });
    }
}

// Modal
const showModal = ref(false);
const editing = ref<Process | null>(null);

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(process: Process) {
    editing.value = process;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function destroy(process: Process) {
    if (!confirm(t('bulk.confirm_title'))) return;
    router.delete(`/processes/${process.id}`, { preserveScroll: true });
}

// Bulk selection
const selected = ref(new Set<number>());
const showBulkConfirm = ref(false);

const allOnPageSelected = computed(() => {
    const items = props.processes.data;
    return items.length > 0 && items.every((p) => selected.value.has(p.id));
});

function toggleSelectAll() {
    if (allOnPageSelected.value) {
        props.processes.data.forEach((p) => selected.value.delete(p.id));
    } else {
        props.processes.data.forEach((p) => selected.value.add(p.id));
    }
}

function toggleSelect(id: number) {
    if (selected.value.has(id)) {
        selected.value.delete(id);
    } else {
        selected.value.add(id);
    }
}

function clearSelection() {
    selected.value = new Set();
}

function confirmBulkDelete() {
    router.post('/processes/bulk-delete', { ids: [...selected.value] }, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = new Set();
            showBulkConfirm.value = false;
        },
    });
}

function unitFor(process: Process): Unit | undefined {
    return process.unit ?? props.allUnits.find((u) => u.id === process.unit_id);
}

function ownerName(process: Process): string {
    if (process.owner?.name) return process.owner.name;
    if (!process.owner_id) return '\u2014';
    const emp = props.employees.find((e) => e.id === process.owner_id);
    return emp?.name ?? '\u2014';
}
</script>

<template>
    <Head :title="t('processes.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('processes.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('processes.sub') }}</p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('processes.add') }}
                </button>
            </div>

            <!-- Stats bar -->
            <div class="mb-4 grid grid-cols-2 gap-3 md:grid-cols-4">
                <button
                    v-for="level in (['not_documented', 'documented_testing', 'documented_digitized', 'fully_automated'] as ProcessMaturity[])"
                    :key="level"
                    type="button"
                    :class="[
                        'rounded-lg border px-4 py-3 text-left transition-colors',
                        activeMaturityFilter === level
                            ? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500'
                            : 'border-slate-200 bg-white hover:bg-slate-50',
                    ]"
                    @click="filterByMaturity(level)"
                >
                    <div class="text-lg font-bold" :style="{ color: MATURITY_COLORS[level] }">
                        {{ MATURITY_LABELS[level].emoji }} {{ maturityCounts[level] }}
                    </div>
                    <div class="mt-0.5 text-xs text-slate-600">{{ t(MATURITY_LABELS[level].key) }}</div>
                </button>
            </div>

            <!-- Bulk action bar -->
            <div
                v-if="selected.size > 0"
                class="mb-3 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-2"
            >
                <span class="text-sm font-medium text-red-800">{{ t('bulk.selected', { count: selected.size }) }}</span>
                <button
                    type="button"
                    class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                    @click="showBulkConfirm = true"
                >
                    {{ t('bulk.delete') }}
                </button>
                <button
                    type="button"
                    class="text-xs text-slate-600 underline hover:text-slate-900"
                    @click="clearSelection"
                >
                    {{ t('bulk.clear') }}
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th v-if="can.delete" class="w-10 px-3 py-3">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300"
                                    :checked="allOnPageSelected"
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('processes.col.title') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('processes.col.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('processes.col.category') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('processes.col.owner') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('processes.col.maturity') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('processes.col.tool') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="processes.data.length === 0">
                            <td :colspan="can.delete ? 8 : 7" class="px-4 py-12 text-center">
                                <div class="text-4xl">&#x1F501;</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('processes.empty') }}</div>
                            </td>
                        </tr>
                        <tr
                            v-for="process in processes.data"
                            :key="process.id"
                            class="hover:bg-slate-50"
                        >
                            <td v-if="can.delete" class="w-10 px-3 py-3" @click.stop>
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300"
                                    :checked="selected.has(process.id)"
                                    @change="toggleSelect(process.id)"
                                />
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-slate-900">{{ process.title }}</div>
                                <div v-if="process.description" class="mt-0.5 text-xs text-slate-500 line-clamp-1">{{ process.description }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="unitFor(process)" :color="unitFor(process)!.color">{{ unitFor(process)!.name }}</Badge>
                                <span v-else class="text-xs text-slate-400">&#x2014;</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ process.category }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ ownerName(process) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :color="MATURITY_COLORS[process.maturity]">{{ t(`processes.maturity.${process.maturity}`) }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">
                                <template v-if="process.tool">{{ process.tool }}</template>
                                <span v-else class="text-xs text-slate-400">&#x2014;</span>
                            </td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        v-if="can.create"
                                        type="button"
                                        class="text-xs text-slate-500 hover:text-indigo-600"
                                        @click="openEdit(process)"
                                    >
                                        &#x270E;
                                    </button>
                                    <button
                                        v-if="can.delete"
                                        type="button"
                                        class="text-xs text-rose-500 hover:text-rose-700"
                                        @click="destroy(process)"
                                    >
                                        &#x2715;
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination
                    :links="processes.links"
                    :from="processes.from"
                    :to="processes.to"
                    :total="processes.total"
                />
            </div>
        </div>

        <ProcessFormModal
            :show="showModal"
            :process="editing"
            :units="allUnits"
            :employees="employees"
            :categories="categories"
            :maturity-levels="maturityLevels"
            @close="closeModal"
        />

        <ConfirmDialog
            :show="showBulkConfirm"
            :title="t('bulk.confirm_title')"
            :message="t('bulk.confirm_message', { count: selected.size })"
            :confirm-label="t('bulk.confirm_btn')"
            variant="danger"
            @confirm="confirmBulkDelete"
            @cancel="showBulkConfirm = false"
        />
    </AppLayout>
</template>
