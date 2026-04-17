<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import IdeaFormModal from '../../Components/Pult/IdeaFormModal.vue';
import InlineSelect from '../../Components/Pult/InlineSelect.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import SearchableSelect from '../../Components/Pult/SearchableSelect.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Idea, IdeaPriority, IdeaStatus, Paginated, Unit } from '../../types';

type FilterCol = 'unit_id' | 'status' | 'priority' | 'author_id' | 'title';

interface Props {
    ideas: Paginated<Idea>;
    allUnits: Unit[];
    authors: Employee[];
    statuses: IdeaStatus[];
    priorities: IdeaPriority[];
    operators: string[];
    filters: Partial<Record<FilterCol, string>>;
    sort: string;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

// ── Filter UI state ────────────────────────────────────────────────
const filterPanelOpen = ref(false);
const pendingCol = ref<FilterCol>('unit_id');
const pendingValue = ref<string>('');

const STATUS_COLORS: Record<IdeaStatus, string> = {
    new: '#6c63ff',
    under_review: '#f59e0b',
    approved: '#22c55e',
    rejected: '#ef4444',
    in_progress: '#8b5cf6',
    done: '#22c55e',
};

const PRIORITY_COLORS: Record<IdeaPriority, string> = {
    high: '#ef4444',
    medium: '#f59e0b',
    low: '#94a3b8',
};

const FILTER_COLS: { id: FilterCol; labelKey: string; type: 'select' | 'text' }[] = [
    { id: 'title', labelKey: 'ideas.col.idea', type: 'text' },
    { id: 'unit_id', labelKey: 'ideas.col.company', type: 'select' },
    { id: 'status', labelKey: 'ideas.col.status', type: 'select' },
    { id: 'priority', labelKey: 'ideas.col.priority', type: 'select' },
    { id: 'author_id', labelKey: 'ideas.col.author', type: 'select' },
];

const SORTABLE_COLS: { id: string; labelKey: string }[] = [
    { id: 'display_id', labelKey: 'ideas.col.id' },
    { id: 'unit_id', labelKey: 'ideas.col.company' },
    { id: 'title', labelKey: 'ideas.col.idea' },
    { id: 'thrice_score', labelKey: 'ideas.col.score' },
    { id: 'status', labelKey: 'ideas.col.status' },
    { id: 'author_id', labelKey: 'ideas.col.author' },
    { id: 'created_at', labelKey: 'ideas.col.created' },
];

function thriceBadge(score: number | null | undefined): { text: string; cls: string } {
    if (score === null || score === undefined) return { text: '—', cls: 'bg-slate-100 text-slate-400' };
    if (score >= 45) return { text: String(score), cls: 'bg-emerald-100 text-emerald-800' };
    if (score >= 30) return { text: String(score), cls: 'bg-lime-100 text-lime-800' };
    if (score >= 18) return { text: String(score), cls: 'bg-amber-100 text-amber-800' };
    return { text: String(score), cls: 'bg-rose-100 text-rose-800' };
}

const pendingColType = computed(() => FILTER_COLS.find((c) => c.id === pendingCol.value)?.type ?? 'text');

const activeFilters = computed(() => {
    return Object.entries(props.filters)
        .filter(([, value]) => value !== null && value !== undefined && value !== '')
        .map(([col, value]) => ({ col: col as FilterCol, value: String(value) }));
});

const filterCount = computed(() => activeFilters.value.length);

function togglePanel() {
    filterPanelOpen.value = !filterPanelOpen.value;
    if (filterPanelOpen.value) {
        pendingCol.value = 'unit_id';
        pendingValue.value = '';
    }
}

function applyFilter() {
    if (!pendingValue.value) return;
    const nextFilters = { ...props.filters, [pendingCol.value]: pendingValue.value };
    navigate(nextFilters, currentSort.value);
    filterPanelOpen.value = false;
}

function removeFilter(col: FilterCol) {
    const nextFilters = { ...props.filters };
    delete nextFilters[col];
    navigate(nextFilters, currentSort.value);
}

function clearAllFilters() {
    navigate({}, currentSort.value);
}

function navigate(filters: Partial<Record<FilterCol, string>>, sort: string) {
    router.get(
        '/ideas',
        {
            filter: filters,
            sort,
        },
        {
            preserveScroll: true,
            preserveState: true,
            only: ['ideas', 'filters', 'sort'],
        },
    );
}

// ── Sort state ─────────────────────────────────────────────────────
const currentSort = computed(() => props.sort || '-created_at');

function sortDir(colId: string): 'asc' | 'desc' | null {
    const s = currentSort.value;
    if (s === colId) return 'asc';
    if (s === `-${colId}`) return 'desc';
    return null;
}

function toggleSort(colId: string) {
    const current = sortDir(colId);
    const next = current === 'asc' ? `-${colId}` : colId;
    navigate(props.filters, next);
}

// ── Bulk selection ────────────────────────────────────────────────
const selected = ref(new Set<number>());
const showBulkConfirm = ref(false);

const allOnPageSelected = computed(() => {
    const items = props.ideas.data;
    return items.length > 0 && items.every((i) => selected.value.has(i.id));
});

function toggleSelectAll() {
    if (allOnPageSelected.value) {
        props.ideas.data.forEach((i) => selected.value.delete(i.id));
    } else {
        props.ideas.data.forEach((i) => selected.value.add(i.id));
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
    router.post('/ideas/bulk-delete', { ids: [...selected.value] }, {
        preserveScroll: true,
        onSuccess: () => {
            selected.value = new Set();
            showBulkConfirm.value = false;
        },
    });
}

// ── Form modal ─────────────────────────────────────────────────────
const showModal = ref(false);
const editingIdea = ref<Idea | null>(null);

function openCreate() {
    editingIdea.value = null;
    showModal.value = true;
}

function openEdit(idea: Idea) {
    editingIdea.value = idea;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editingIdea.value = null;
}

// ── Single-row delete ──────────────────────────────────────────────
const deletingIdea = ref<Idea | null>(null);
function doDelete() {
    const idea = deletingIdea.value;
    if (!idea) return;
    router.delete(`/ideas/${idea.display_id}`, { preserveScroll: true, onFinish: () => { deletingIdea.value = null; } });
}

function inlinePatch(idea: Idea, field: string, value: string | number) {
    router.patch(`/ideas/${idea.display_id}`, { field, value }, { preserveScroll: true, preserveState: true });
}

const unitOptions = computed(() => props.allUnits.map((u) => ({ value: u.id, label: u.name, color: u.color })));
const statusOptions = computed(() => props.statuses.map((s) => ({ value: s, label: t(`ideas.status.${s}`), color: STATUS_COLORS[s] })));
const priorityOptions = computed(() => props.priorities.map((p) => ({ value: p, label: t(`ideas.priority.${p}`), color: PRIORITY_COLORS[p] })));
const authorOptions = computed(() => props.authors.map((a) => ({ value: a.id, label: a.name ?? a.position })));

// Filter panel option arrays — values are strings to match pendingValue
const filterColOptions = computed(() => FILTER_COLS.map((c) => ({ value: c.id, label: t(c.labelKey) })));
const filterUnitOptions = computed(() => [
    { value: '', label: '—' },
    ...props.allUnits.map((u) => ({ value: u.id, label: u.name, color: u.color })),
]);
const filterStatusOptions = computed(() => [
    { value: '', label: '—' },
    ...props.statuses.map((s) => ({ value: s, label: t(`ideas.status.${s}`), color: STATUS_COLORS[s] })),
]);
const filterPriorityOptions = computed(() => [
    { value: '', label: '—' },
    ...props.priorities.map((p) => ({ value: p, label: t(`ideas.priority.${p}`), color: PRIORITY_COLORS[p] })),
]);
const filterAuthorOptions = computed(() => [
    { value: '', label: '—' },
    ...props.authors.map((a) => ({ value: String(a.id), label: a.name ?? `[${t('table.vacancy')}] ${a.position}` })),
]);

function unitFor(idea: Idea): Unit | undefined {
    return idea.unit ?? props.allUnits.find((u) => u.id === idea.unit_id);
}

function authorFor(idea: Idea): Employee | undefined {
    return idea.author ?? props.authors.find((a) => a.id === idea.author_id);
}

function authorLabel(idea: Idea): string {
    const a = authorFor(idea);
    if (!a) return '—';
    return a.name ?? `[${t('table.vacancy')}]`;
}

function unitLabelFor(id: string): string {
    return props.allUnits.find((u) => u.id === id)?.name ?? id;
}

function filterLabel(col: FilterCol, value: string): string {
    if (col === 'unit_id') return unitLabelFor(value);
    if (col === 'status') return t(`ideas.status.${value}`);
    if (col === 'priority') return t(`ideas.priority.${value}`);
    if (col === 'author_id') {
        const a = props.authors.find((x) => x.id === Number(value));
        return a?.name ?? a?.position ?? value;
    }
    return value;
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="t('ideas.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ t('ideas.title') }}
                    </h1>
                    <p class="mt-1 max-w-2xl text-sm text-slate-600">
                        {{ t('ideas.subtitle') }}
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        :class="[
                            'relative rounded-md border px-3 py-2 text-sm font-medium transition-colors',
                            filterCount
                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                : 'border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
                        ]"
                        @click="togglePanel"
                    >
                        ⚙ {{ t('ideas.filter.btn') }}<span v-if="filterCount"> ({{ filterCount }})</span>
                    </button>
                    <button
                        v-if="can.create"
                        type="button"
                        class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        @click="openCreate"
                    >
                        + {{ t('ideas.btn.add') }}
                    </button>
                </div>
            </div>

            <!-- Filter panel -->
            <div
                v-if="filterPanelOpen"
                class="mb-3 flex flex-wrap items-end gap-2 rounded-lg border border-slate-200 bg-white p-3 shadow-sm"
            >
                <div class="flex flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase text-slate-500">Column</span>
                    <SearchableSelect
                        :model-value="pendingCol"
                        :options="filterColOptions"
                        size="sm"
                        @update:model-value="(v) => { pendingCol = v as FilterCol; pendingValue = ''; }"
                    />
                </div>

                <div class="flex flex-1 flex-col gap-1">
                    <span class="text-[11px] font-semibold uppercase text-slate-500">
                        {{ pendingColType === 'text' ? t('ideas.filter.op.contains') : t('ideas.filter.op.is') }}
                    </span>
                    <input
                        v-if="pendingCol === 'title'"
                        v-model="pendingValue"
                        type="text"
                        placeholder="..."
                        class="rounded-md border border-slate-300 px-2 py-1 text-sm"
                    />
                    <SearchableSelect
                        v-else-if="pendingCol === 'unit_id'"
                        :model-value="pendingValue"
                        :options="filterUnitOptions"
                        size="sm"
                        @update:model-value="(v) => pendingValue = String(v ?? '')"
                    />
                    <SearchableSelect
                        v-else-if="pendingCol === 'status'"
                        :model-value="pendingValue"
                        :options="filterStatusOptions"
                        size="sm"
                        @update:model-value="(v) => pendingValue = String(v ?? '')"
                    />
                    <SearchableSelect
                        v-else-if="pendingCol === 'priority'"
                        :model-value="pendingValue"
                        :options="filterPriorityOptions"
                        size="sm"
                        @update:model-value="(v) => pendingValue = String(v ?? '')"
                    />
                    <SearchableSelect
                        v-else-if="pendingCol === 'author_id'"
                        :model-value="pendingValue"
                        :options="filterAuthorOptions"
                        size="sm"
                        @update:model-value="(v) => pendingValue = String(v ?? '')"
                    />
                </div>

                <button
                    type="button"
                    :disabled="!pendingValue"
                    class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm text-white hover:bg-indigo-700 disabled:opacity-40"
                    @click="applyFilter"
                >
                    {{ t('ideas.filter.add') }}
                </button>
            </div>

            <!-- Active filter chips -->
            <div v-if="activeFilters.length" class="mb-4 flex flex-wrap items-center gap-2">
                <div
                    v-for="f in activeFilters"
                    :key="f.col"
                    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-3 py-1 text-xs text-indigo-700"
                >
                    <span class="font-medium">{{ t(`ideas.col.${f.col === 'author_id' ? 'author' : f.col === 'unit_id' ? 'company' : f.col === 'title' ? 'idea' : f.col}`) }}</span>
                    <span class="text-indigo-500">{{ f.col === 'title' ? t('ideas.filter.op.contains') : t('ideas.filter.op.is') }}</span>
                    <span>{{ filterLabel(f.col, f.value) }}</span>
                    <button type="button" class="ml-1 text-indigo-500 hover:text-indigo-900" @click="removeFilter(f.col)">✕</button>
                </div>
                <button type="button" class="text-xs text-slate-500 underline hover:text-slate-800" @click="clearAllFilters">
                    {{ t('ideas.filter.clear') }}
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
                            <th
                                v-for="col in SORTABLE_COLS"
                                :key="col.id"
                                class="cursor-pointer select-none px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600 hover:bg-slate-100"
                                @click="toggleSort(col.id)"
                            >
                                {{ t(col.labelKey) }}
                                <span v-if="sortDir(col.id)" class="ml-1 text-indigo-500">
                                    {{ sortDir(col.id) === 'asc' ? '↑' : '↓' }}
                                </span>
                            </th>
                            <th class="w-24 px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="ideas.data.length === 0">
                            <td :colspan="can.delete ? 9 : 8" class="px-4 py-12 text-center">
                                <div class="text-4xl">💡</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('ideas.empty') }}</div>
                            </td>
                        </tr>
                        <tr
                            v-for="idea in ideas.data"
                            :key="idea.id"
                            class="cursor-pointer hover:bg-slate-50"
                        >
                            <td v-if="can.delete" class="w-10 px-3 py-3" @click.stop>
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300"
                                    :checked="selected.has(idea.id)"
                                    @change="toggleSelect(idea.id)"
                                />
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <Link :href="`/ideas/${idea.display_id}`" class="font-mono text-xs text-indigo-600 hover:text-indigo-800">
                                    {{ idea.display_id }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-sm" @click.stop>
                                <InlineSelect
                                    :model-value="idea.unit_id"
                                    :options="unitOptions"
                                    @update:model-value="(v: string | number) => inlinePatch(idea, 'unit_id', v)"
                                />
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-900">
                                <Link :href="`/ideas/${idea.display_id}`" class="font-medium hover:text-indigo-700">
                                    {{ idea.title }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex min-w-[2.75rem] items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold" :class="thriceBadge(idea.thrice_score).cls" :title="'THRICE: ' + thriceBadge(idea.thrice_score).text + ' / 60'">
                                    {{ thriceBadge(idea.thrice_score).text }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm" @click.stop>
                                <InlineSelect
                                    :model-value="idea.status"
                                    :options="statusOptions"
                                    @update:model-value="(v: string | number) => inlinePatch(idea, 'status', v)"
                                />
                            </td>
                            <td class="px-4 py-3 text-sm" @click.stop>
                                <InlineSelect
                                    :model-value="idea.author_id"
                                    :options="authorOptions"
                                    @update:model-value="(v: string | number) => inlinePatch(idea, 'author_id', v)"
                                />
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ formatDate(idea.created_at) }}</td>
                            <td class="px-4 py-3 text-right" @click.stop>
                                <div class="flex items-center justify-end gap-3">
                                    <button v-if="can.create" class="text-slate-400 hover:text-indigo-600" title="Редактировать" @click="openEdit(idea)">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button v-if="can.delete" class="text-slate-400 hover:text-rose-600" title="Удалить" @click="deletingIdea = idea">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination
                    :links="ideas.links"
                    :from="ideas.from"
                    :to="ideas.to"
                    :total="ideas.total"
                />
            </div>
        </div>

        <IdeaFormModal
            :show="showModal"
            :idea="editingIdea"
            :units="allUnits"
            :authors="authors"
            :statuses="statuses"
            :priorities="priorities"
            @close="closeModal"
        />

        <ConfirmDialog
            :show="deletingIdea !== null"
            :title="t('ideas.btn.delete')"
            :message="deletingIdea?.title ?? ''"
            :confirm-label="t('ideas.btn.delete')"
            variant="danger"
            @confirm="doDelete"
            @cancel="deletingIdea = null"
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
