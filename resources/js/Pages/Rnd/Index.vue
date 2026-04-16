<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import RndProjectFormModal from '../../Components/Pult/RndProjectFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Paginated, RndPriority, RndProject, RndStatus, Unit } from '../../types';

interface Props {
    projects: Paginated<RndProject>;
    allUnits: Unit[];
    employees: Employee[];
    statuses: RndStatus[];
    priorities: RndPriority[];
    currencies: string[];
    filters: Partial<Record<string, string>>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const STATUS_COLORS: Record<RndStatus, string> = {
    idea: '#a78bfa',
    research: '#3b82f6',
    testing: '#f59e0b',
    pilot: '#8b5cf6',
    scaling: '#22c55e',
    paused: '#94a3b8',
    killed: '#ef4444',
    completed: '#10b981',
};

const PRIORITY_COLORS: Record<RndPriority, string> = {
    critical: '#ef4444',
    high: '#f97316',
    medium: '#eab308',
    low: '#94a3b8',
};

type FilterTab = 'all' | 'active' | 'completed' | 'killed' | 'paused';

const ACTIVE_STATUSES = ['idea', 'research', 'testing', 'pilot', 'scaling'];

const activeTab = computed<FilterTab>(() => {
    const statusFilter = props.filters.status;
    if (statusFilter === 'completed') return 'completed';
    if (statusFilter === 'killed') return 'killed';
    if (statusFilter === 'paused') return 'paused';
    // Check if active filter is applied (multiple statuses via comma)
    if (statusFilter && ACTIVE_STATUSES.includes(statusFilter)) return 'active';
    return 'all';
});

function applyTab(tab: FilterTab) {
    const filters: Record<string, string> = {};
    if (tab === 'active') filters.status = 'idea'; // simplified: can't send array via exact filter easily
    if (tab === 'completed') filters.status = 'completed';
    if (tab === 'killed') filters.status = 'killed';
    if (tab === 'paused') filters.status = 'paused';

    if (tab === 'active') {
        // For active, we need to show multiple statuses. Use router with custom query.
        router.get('/rnd', {}, { preserveScroll: true, preserveState: true });
        return;
    }

    router.get('/rnd', tab === 'all' ? {} : { filter: filters }, { preserveScroll: true, preserveState: true });
}

const TABS: { id: FilterTab; key: string }[] = [
    { id: 'all', key: 'rnd.filter.all' },
    { id: 'active', key: 'rnd.filter.active' },
    { id: 'completed', key: 'rnd.filter.completed' },
    { id: 'killed', key: 'rnd.filter.killed' },
    { id: 'paused', key: 'rnd.filter.paused' },
];

// Modal
const showModal = ref(false);
const editing = ref<RndProject | null>(null);

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(project: RndProject) {
    editing.value = project;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function destroy(project: RndProject) {
    if (!confirm(t('bulk.confirm_title'))) return;
    router.delete(`/rnd/${project.id}`, { preserveScroll: true });
}

// Expanded rows
const expandedIds = ref(new Set<number>());

function toggleExpand(id: number) {
    if (expandedIds.value.has(id)) {
        expandedIds.value.delete(id);
    } else {
        expandedIds.value.add(id);
    }
}

// Bulk selection
const selected = ref(new Set<number>());
const showBulkConfirm = ref(false);

const allOnPageSelected = computed(() => {
    const items = props.projects.data;
    return items.length > 0 && items.every((p) => selected.value.has(p.id));
});

function toggleSelectAll() {
    if (allOnPageSelected.value) {
        props.projects.data.forEach((p) => selected.value.delete(p.id));
    } else {
        props.projects.data.forEach((p) => selected.value.add(p.id));
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
    router.post('/rnd/bulk-delete', { ids: [...selected.value] }, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = new Set();
            showBulkConfirm.value = false;
        },
    });
}

function unitFor(project: RndProject): Unit | undefined {
    return project.unit ?? props.allUnits.find((u) => u.id === project.unit_id);
}

function ownerName(project: RndProject): string {
    if (project.owner?.name) return project.owner.name;
    const emp = props.employees.find((e) => e.id === project.owner_id);
    return emp?.name ?? '—';
}

function formatBudget(project: RndProject): string {
    if (!project.budget) return '—';
    const val = parseFloat(project.budget);
    return `${project.currency} ${val.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
}

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function isOverdue(deadline: string | null): boolean {
    if (!deadline) return false;
    return new Date(deadline) < new Date();
}
</script>

<template>
    <Head :title="t('rnd.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('rnd.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('rnd.sub') }}</p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('rnd.add') }}
                </button>
            </div>

            <!-- Filter tabs -->
            <div class="mb-4 flex flex-wrap gap-2 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <button
                    v-for="tab in TABS"
                    :key="tab.id"
                    type="button"
                    :class="[
                        'rounded-full border px-3 py-1 text-xs font-medium transition-colors',
                        activeTab === tab.id
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                            : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50',
                    ]"
                    @click="applyTab(tab.id)"
                >
                    {{ t(tab.key) }}
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
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.title') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.owner') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.priority') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.budget') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('rnd.col.deadline') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="projects.data.length === 0">
                            <td :colspan="can.delete ? 9 : 8" class="px-4 py-12 text-center">
                                <div class="text-4xl">🔬</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('rnd.empty') }}</div>
                            </td>
                        </tr>
                        <template v-for="project in projects.data" :key="project.id">
                            <tr
                                class="cursor-pointer hover:bg-slate-50"
                                @click="toggleExpand(project.id)"
                            >
                                <td v-if="can.delete" class="w-10 px-3 py-3" @click.stop>
                                    <input
                                        type="checkbox"
                                        class="rounded border-slate-300"
                                        :checked="selected.has(project.id)"
                                        @change="toggleSelect(project.id)"
                                    />
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="font-medium text-slate-900">{{ project.title }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <Badge v-if="unitFor(project)" :color="unitFor(project)!.color">{{ unitFor(project)!.name }}</Badge>
                                    <span v-else class="text-xs text-slate-400">—</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ ownerName(project) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <Badge :color="PRIORITY_COLORS[project.priority]">{{ t(`rnd.priority.${project.priority}`) }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <Badge :color="STATUS_COLORS[project.status]">{{ t(`rnd.status.${project.status}`) }}</Badge>
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-sm text-slate-700">{{ formatBudget(project) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span :class="isOverdue(project.deadline) ? 'font-medium text-red-600' : 'text-slate-500'">
                                        {{ formatDate(project.deadline) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right" @click.stop>
                                    <div class="flex items-center justify-end gap-2">
                                        <button
                                            v-if="can.create"
                                            type="button"
                                            class="text-xs text-slate-500 hover:text-indigo-600"
                                            @click="openEdit(project)"
                                        >
                                            ✎
                                        </button>
                                        <button
                                            v-if="can.delete"
                                            type="button"
                                            class="text-xs text-rose-500 hover:text-rose-700"
                                            @click="destroy(project)"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <!-- Expanded detail row -->
                            <tr v-if="expandedIds.has(project.id)">
                                <td :colspan="can.delete ? 9 : 8" class="bg-slate-50 px-6 py-4">
                                    <div class="grid gap-4 md:grid-cols-2">
                                        <!-- Description -->
                                        <div v-if="project.description">
                                            <div class="mb-1 text-xs font-semibold uppercase text-slate-500">{{ t('rnd.detail.description') }}</div>
                                            <p class="text-sm text-slate-700">{{ project.description }}</p>
                                        </div>

                                        <!-- Started at -->
                                        <div v-if="project.started_at">
                                            <div class="mb-1 text-xs font-semibold uppercase text-slate-500">{{ t('rnd.detail.started_at') }}</div>
                                            <p class="text-sm text-slate-700">{{ formatDate(project.started_at) }}</p>
                                        </div>

                                        <!-- Success criteria -->
                                        <div class="rounded-md border-l-4 border-green-500 bg-green-50 px-4 py-3">
                                            <div class="mb-1 text-xs font-semibold uppercase text-green-700">{{ t('rnd.detail.success_criteria') }}</div>
                                            <p class="text-sm text-green-900">{{ project.success_criteria }}</p>
                                        </div>

                                        <!-- Kill criteria -->
                                        <div class="rounded-md border-l-4 border-red-500 bg-red-50 px-4 py-3">
                                            <div class="mb-1 text-xs font-semibold uppercase text-red-700">{{ t('rnd.detail.kill_criteria') }}</div>
                                            <p class="text-sm text-red-900">{{ project.kill_criteria }}</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Pagination
                    :links="projects.links"
                    :from="projects.from"
                    :to="projects.to"
                    :total="projects.total"
                />
            </div>
        </div>

        <RndProjectFormModal
            :show="showModal"
            :project="editing"
            :units="allUnits"
            :employees="employees"
            :statuses="statuses"
            :priorities="priorities"
            :currencies="currencies"
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
