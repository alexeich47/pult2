<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import SearchableSelect from '../../Components/Pult/SearchableSelect.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface Unit { id: string; name: string; color: string; unit_type: string | null }
interface Employee { id: number; name: string | null; position: string; unit_id: string | null }
interface User { id: number; name: string; email: string | null }
interface Task {
    id: number;
    unit_id: string | null;
    assignee_id: number | null;
    created_by: number | null;
    title: string;
    description: string | null;
    status: 'todo' | 'in_progress' | 'done' | 'blocked';
    priority: 'low' | 'medium' | 'high' | 'critical';
    due_date: string | null;
    completed_at: string | null;
    created_at: string;
    unit: Unit | null;
    assignee: Employee | null;
    creator: User | null;
}

const props = defineProps<{
    tasks: Task[];
    allUnits: Unit[];
    allEmployees: Employee[];
    statuses: string[];
    priorities: string[];
}>();

const { t } = useTranslations();

const STATUS_LABELS: Record<string, string> = {
    todo: 'В плане',
    in_progress: 'В работе',
    blocked: 'Заблокирована',
    done: 'Выполнено',
};
const STATUS_COLORS: Record<string, string> = {
    todo: 'bg-slate-100 text-slate-700',
    in_progress: 'bg-sky-50 text-sky-700',
    blocked: 'bg-rose-50 text-rose-700',
    done: 'bg-emerald-50 text-emerald-700',
};
const PRIORITY_LABELS: Record<string, string> = {
    low: 'Низкий',
    medium: 'Средний',
    high: 'Высокий',
    critical: 'Критичный',
};
const PRIORITY_COLORS: Record<string, string> = {
    low: 'text-slate-400',
    medium: 'text-indigo-500',
    high: 'text-amber-600',
    critical: 'text-rose-600',
};

// ── Form state ────────────────────────────────────────────────────
type FormShape = {
    unit_id: string | null;
    assignee_id: number | null;
    title: string;
    description: string;
    status: Task['status'];
    priority: Task['priority'];
    due_date: string;
};

const editingId = ref<number | null>(null);
const showForm = ref(false);
const form = useForm<FormShape>({
    unit_id: null,
    assignee_id: null,
    title: '',
    description: '',
    status: 'todo',
    priority: 'medium',
    due_date: '',
});

function openAdd() {
    editingId.value = null;
    form.reset();
    form.status = 'todo';
    form.priority = 'medium';
    showForm.value = true;
}

function openEdit(t0: Task) {
    editingId.value = t0.id;
    form.unit_id = t0.unit_id;
    form.assignee_id = t0.assignee_id;
    form.title = t0.title;
    form.description = t0.description ?? '';
    form.status = t0.status;
    form.priority = t0.priority;
    form.due_date = t0.due_date ?? '';
    showForm.value = true;
}

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; form.reset(); editingId.value = null; } };
    if (editingId.value !== null) {
        form.put(`/tasks/${editingId.value}`, opts);
    } else {
        form.post('/tasks', opts);
    }
}

const deletingId = ref<number | null>(null);
function doDelete() {
    if (!deletingId.value) return;
    router.delete(`/tasks/${deletingId.value}`, { preserveScroll: true, onFinish: () => { deletingId.value = null; } });
}

// Quick status toggle via checkbox
function toggleDone(taskRow: Task) {
    const nextStatus = taskRow.status === 'done' ? 'todo' : 'done';
    router.put(`/tasks/${taskRow.id}`, {
        unit_id: taskRow.unit_id,
        assignee_id: taskRow.assignee_id,
        title: taskRow.title,
        description: taskRow.description ?? '',
        status: nextStatus,
        priority: taskRow.priority,
        due_date: taskRow.due_date ?? '',
    }, { preserveScroll: true });
}

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()}`;
}

const openTasks = computed(() => props.tasks.filter(t0 => t0.status !== 'done'));
const doneTasks = computed(() => props.tasks.filter(t0 => t0.status === 'done'));
</script>

<template>
    <Head :title="t('nav.tasks')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ t('nav.tasks') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Таски и дела по холдингу</p>
                </div>
                <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                    @click="openAdd"
                >
                    + Добавить таску
                </button>
            </div>

            <!-- Form -->
            <div v-if="showForm" class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 shadow-sm dark:border-indigo-900 dark:bg-slate-800">
                <form class="grid gap-3 md:grid-cols-3" @submit.prevent="submit">
                    <label class="block md:col-span-3">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Название <span class="text-rose-500">*</span></span>
                        <input v-model="form.title" type="text" required class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>

                    <label class="block md:col-span-3">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Описание</span>
                        <textarea v-model="form.description" rows="3" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm"></textarea>
                    </label>

                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Статус</span>
                        <SearchableSelect
                            v-model="form.status"
                            :options="statuses.map(s => ({ value: s, label: STATUS_LABELS[s] ?? s }))"
                            size="sm"
                        />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Приоритет</span>
                        <SearchableSelect
                            v-model="form.priority"
                            :options="priorities.map(p => ({ value: p, label: PRIORITY_LABELS[p] ?? p }))"
                            size="sm"
                        />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Срок</span>
                        <input v-model="form.due_date" type="date" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>

                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Исполнитель</span>
                        <SearchableSelect
                            v-model="form.assignee_id"
                            :options="[{ value: null, label: '— Не назначен' }, ...allEmployees.map(e => ({ value: e.id, label: `${e.name} — ${e.position}` }))]"
                            size="sm"
                        />
                    </label>
                    <label class="block md:col-span-2">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Компания</span>
                        <SearchableSelect
                            v-model="form.unit_id"
                            :options="[{ value: null, label: '— Холдинг' }, ...allUnits.map(u => ({ value: u.id, label: u.name, color: u.color }))]"
                            size="sm"
                        />
                    </label>

                    <div class="flex items-center justify-end gap-3 md:col-span-3">
                        <button type="button" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" @click="showForm = false">Отмена</button>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:bg-slate-300">
                            {{ editingId !== null ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Open tasks -->
            <div class="mb-6 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-900">
                    Активные ({{ openTasks.length }})
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="w-10 px-4 py-2.5"></th>
                            <th class="px-4 py-2.5">Название</th>
                            <th class="px-4 py-2.5">Исполнитель</th>
                            <th class="px-4 py-2.5">Компания</th>
                            <th class="px-4 py-2.5">Приоритет</th>
                            <th class="px-4 py-2.5">Статус</th>
                            <th class="px-4 py-2.5">Срок</th>
                            <th class="w-20 px-4 py-2.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="task in openTasks" :key="task.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-2.5">
                                <input type="checkbox" :checked="false" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @change="toggleDone(task)" />
                            </td>
                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200">
                                {{ task.title }}
                                <div v-if="task.description" class="mt-0.5 line-clamp-1 text-[11px] text-slate-500">{{ task.description }}</div>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-400">{{ task.assignee?.name ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span v-if="task.unit" class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="inline-block h-2 w-2 rounded-full" :style="{ backgroundColor: task.unit.color }"></span>
                                    {{ task.unit.name }}
                                </span>
                                <span v-else class="text-xs text-slate-400">Холдинг</span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center gap-1 text-xs font-medium" :class="PRIORITY_COLORS[task.priority]">
                                    ●&nbsp;{{ PRIORITY_LABELS[task.priority] }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="STATUS_COLORS[task.status]">
                                    {{ STATUS_LABELS[task.status] }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ formatDate(task.due_date) }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button class="text-slate-400 hover:text-indigo-600" title="Редактировать" @click="openEdit(task)">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button class="text-slate-400 hover:text-rose-600" title="Удалить" @click="deletingId = task.id">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="openTasks.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center text-sm text-slate-400">Активных задач нет</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Done tasks -->
            <div v-if="doneTasks.length > 0" class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 bg-slate-50 px-5 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:border-slate-700 dark:bg-slate-900">
                    Выполнено ({{ doneTasks.length }})
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="task in doneTasks" :key="task.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="w-10 px-4 py-2">
                                <input type="checkbox" :checked="true" class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @change="toggleDone(task)" />
                            </td>
                            <td class="px-4 py-2 text-sm text-slate-400 line-through">{{ task.title }}</td>
                            <td class="px-4 py-2 text-xs text-slate-400">{{ task.assignee?.name ?? '—' }}</td>
                            <td class="px-4 py-2 text-xs text-slate-400">{{ task.unit?.name ?? 'Холдинг' }}</td>
                            <td class="w-24 px-4 py-2 text-right">
                                <button class="text-slate-300 hover:text-rose-600" title="Удалить" @click="deletingId = task.id">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ConfirmDialog :show="deletingId !== null" title="Удалить таску?" message="Это действие нельзя отменить." @confirm="doDelete" @cancel="deletingId = null" />
    </AppLayout>
</template>
