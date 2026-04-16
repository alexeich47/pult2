<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Unit } from '../../types';

interface UnitWithMeta extends Unit {
    employees_count: number;
    children: UnitWithMeta[];
    head?: Employee;
    deputy?: Employee;
}

interface EmployeeOption {
    id: number;
    name: string;
    position: string;
    unit_id: string;
}

interface Props {
    units: UnitWithMeta[];
    employees: EmployeeOption[];
    stages: string[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

const showModal = ref(false);
const editingUnit = ref<UnitWithMeta | null>(null);
const confirmDeleteUnit = ref<UnitWithMeta | null>(null);
const detailUnit = ref<UnitWithMeta | null>(null);

const form = useForm({
    id: '',
    name: '',
    color: '#3b82f6',
    unit_type: 'revenue' as 'revenue' | 'service' | null,
    parent_id: null as string | null,
    sort_order: 0,
    head_id: null as number | null,
    deputy_id: null as number | null,
    founded_at: '' as string,
    website: '' as string,
    stage: null as string | null,
    description: '' as string,
    legal_name: '' as string,
    inn: '' as string,
});

/** Root unit (unit_type null) */
const rootUnit = computed(() => props.units.find((u) => u.unit_type === null));

/** Direct children of a unit */
function childrenOf(parentId: string): UnitWithMeta[] {
    return props.units.filter((u) => u.parent_id === parentId);
}

/** All units that can serve as parents (for the dropdown) */
const parentOptions = computed(() =>
    props.units.filter((u) => u.id !== editingUnit.value?.id),
);

function openCreate() {
    editingUnit.value = null;
    form.reset();
    form.clearErrors();
    form.color = '#3b82f6';
    form.unit_type = 'revenue';
    form.parent_id = rootUnit.value?.id ?? null;
    showModal.value = true;
}

function openEdit(unit: UnitWithMeta) {
    editingUnit.value = unit;
    form.id = unit.id;
    form.name = unit.name;
    form.color = unit.color;
    form.unit_type = unit.unit_type;
    form.parent_id = unit.parent_id;
    form.sort_order = 0;
    form.head_id = unit.head_id;
    form.deputy_id = unit.deputy_id;
    form.founded_at = unit.founded_at ?? '';
    form.website = unit.website ?? '';
    form.stage = unit.stage;
    form.description = unit.description ?? '';
    form.legal_name = unit.legal_name ?? '';
    form.inn = unit.inn ?? '';
    form.clearErrors();
    showModal.value = true;
}

function openDetail(unit: UnitWithMeta) {
    detailUnit.value = unit;
}

function submit() {
    if (editingUnit.value) {
        form.put(`/structure/${editingUnit.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        form.post('/structure', {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

function confirmDelete(unit: UnitWithMeta) {
    confirmDeleteUnit.value = unit;
}

function doDelete() {
    if (!confirmDeleteUnit.value) return;
    router.delete(`/structure/${confirmDeleteUnit.value.id}`, {
        onFinish: () => {
            confirmDeleteUnit.value = null;
        },
    });
}

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '\u2014';
    const d = new Date(dateStr);
    return d.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
}

function employeeName(emp: Employee | undefined): string {
    if (!emp) return '\u2014';
    return `${emp.name ?? '\u2014'} (${emp.position})`;
}

const TYPE_LABELS: Record<string, string> = {
    revenue: 'structure.type.revenue',
    service: 'structure.type.service',
};

const STAGE_COLORS: Record<string, string> = {
    startup: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    growth: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    maturity: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    restructuring: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
};
</script>

<template>
    <AppLayout>
        <Head :title="t('structure.title')" />

        <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 dark:text-white">
                        {{ t('structure.title') }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ t('structure.sub') }}
                    </p>
                </div>
                <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-500"
                    @click="openCreate"
                >
                    {{ t('structure.add') }}
                </button>
            </div>

            <!-- Org chart -->
            <div v-if="rootUnit" class="space-y-6">
                <!-- Root card -->
                <div class="rounded-lg border-2 border-indigo-300 bg-indigo-50 p-4 dark:border-indigo-700 dark:bg-indigo-900/30">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg text-lg font-bold text-white"
                                :style="{ backgroundColor: rootUnit.color }"
                            >
                                {{ rootUnit.name.charAt(0) }}
                            </div>
                            <div>
                                <button
                                    type="button"
                                    class="text-lg font-semibold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                                    @click="openDetail(rootUnit)"
                                >
                                    {{ rootUnit.name }}
                                </button>
                                <div class="text-xs text-slate-500">
                                    {{ rootUnit.employees_count }} {{ t('structure.field.name') === 'Название' ? 'сотр.' : 'emp.' }}
                                </div>
                                <div v-if="rootUnit.head || rootUnit.deputy" class="mt-1 space-y-0.5 text-xs text-slate-500 dark:text-slate-400">
                                    <div v-if="rootUnit.head">
                                        {{ t('structure.detail.head') }}: {{ employeeName(rootUnit.head) }}
                                    </div>
                                    <div v-if="rootUnit.deputy">
                                        {{ t('structure.detail.deputy') }}: {{ employeeName(rootUnit.deputy) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button
                                class="rounded px-2 py-1 text-xs text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700"
                                @click="openEdit(rootUnit)"
                            >
                                {{ t('structure.edit') }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Children level 1 -->
                <div class="ml-8 space-y-3 border-l-2 border-slate-300 pl-6 dark:border-slate-600">
                    <template v-for="unit in childrenOf(rootUnit.id)" :key="unit.id">
                        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-8 w-8 items-center justify-center rounded text-sm font-bold text-white"
                                        :style="{ backgroundColor: unit.color }"
                                    >
                                        {{ unit.unit_type === 'revenue' ? '$' : 'S' }}
                                    </div>
                                    <div>
                                        <button
                                            type="button"
                                            class="font-medium text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                                            @click="openDetail(unit)"
                                        >
                                            {{ unit.name }}
                                        </button>
                                        <div class="flex gap-2 text-xs text-slate-500">
                                            <span v-if="unit.unit_type" class="rounded bg-slate-100 px-1.5 py-0.5 dark:bg-slate-700">
                                                {{ t(TYPE_LABELS[unit.unit_type] ?? '') }}
                                            </span>
                                            <span>{{ unit.employees_count }} {{ t('structure.field.name') === 'Название' ? 'сотр.' : 'emp.' }}</span>
                                        </div>
                                        <div v-if="unit.head || unit.deputy" class="mt-1 space-y-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            <div v-if="unit.head">
                                                {{ t('structure.detail.head') }}: {{ employeeName(unit.head) }}
                                            </div>
                                            <div v-if="unit.deputy">
                                                {{ t('structure.detail.deputy') }}: {{ employeeName(unit.deputy) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        class="rounded px-2 py-1 text-xs text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700"
                                        @click="openEdit(unit)"
                                    >
                                        {{ t('structure.edit') }}
                                    </button>
                                    <button
                                        class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30"
                                        @click="confirmDelete(unit)"
                                    >
                                        {{ t('structure.delete') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Sub-children (level 2) -->
                            <div
                                v-if="childrenOf(unit.id).length > 0"
                                class="ml-6 mt-3 space-y-2 border-l-2 border-slate-200 pl-4 dark:border-slate-600"
                            >
                                <div
                                    v-for="child in childrenOf(unit.id)"
                                    :key="child.id"
                                    class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-3 dark:border-slate-600 dark:bg-slate-700/50"
                                >
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="h-6 w-6 rounded text-center text-xs font-bold leading-6 text-white"
                                            :style="{ backgroundColor: child.color }"
                                        >
                                            S
                                        </div>
                                        <div>
                                            <button
                                                type="button"
                                                class="text-sm font-medium text-slate-800 hover:text-indigo-600 dark:text-slate-200 dark:hover:text-indigo-400"
                                                @click="openDetail(child)"
                                            >
                                                {{ child.name }}
                                            </button>
                                            <div class="text-xs text-slate-500">
                                                {{ child.employees_count }} {{ t('structure.field.name') === 'Название' ? 'сотр.' : 'emp.' }}
                                            </div>
                                            <div v-if="child.head || child.deputy" class="mt-0.5 space-y-0.5 text-xs text-slate-500 dark:text-slate-400">
                                                <div v-if="child.head">
                                                    {{ t('structure.detail.head') }}: {{ employeeName(child.head) }}
                                                </div>
                                                <div v-if="child.deputy">
                                                    {{ t('structure.detail.deputy') }}: {{ employeeName(child.deputy) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <button
                                            class="rounded px-2 py-1 text-xs text-slate-500 hover:bg-slate-200 dark:hover:bg-slate-700"
                                            @click="openEdit(child)"
                                        >
                                            {{ t('structure.edit') }}
                                        </button>
                                        <button
                                            class="rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30"
                                            @click="confirmDelete(child)"
                                        >
                                            {{ t('structure.delete') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Unit Detail Modal -->
        <Teleport to="body">
            <div
                v-if="detailUnit"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="detailUnit = null"
            >
                <div class="w-full max-w-lg rounded-lg bg-white shadow-xl dark:bg-slate-800">
                    <!-- Color header -->
                    <div
                        class="flex items-center gap-3 rounded-t-lg px-6 py-4"
                        :style="{ backgroundColor: detailUnit.color }"
                    >
                        <div class="text-xl font-bold text-white">
                            {{ detailUnit.name }}
                        </div>
                        <span
                            v-if="detailUnit.unit_type"
                            class="rounded bg-white/20 px-2 py-0.5 text-xs font-medium text-white"
                        >
                            {{ t(TYPE_LABELS[detailUnit.unit_type] ?? '') }}
                        </span>
                    </div>

                    <div class="space-y-3 px-6 py-5">
                        <!-- Head -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.head') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ employeeName(detailUnit.head) }}</span>
                        </div>

                        <!-- Deputy -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.deputy') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ employeeName(detailUnit.deputy) }}</span>
                        </div>

                        <!-- Founded -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.founded_at') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ formatDate(detailUnit.founded_at) }}</span>
                        </div>

                        <!-- Website -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.website') }}</span>
                            <a
                                v-if="detailUnit.website"
                                :href="detailUnit.website.startsWith('http') ? detailUnit.website : `https://${detailUnit.website}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="font-medium text-indigo-600 hover:underline dark:text-indigo-400"
                            >
                                {{ detailUnit.website }}
                            </a>
                            <span v-else class="font-medium text-slate-900 dark:text-white">&mdash;</span>
                        </div>

                        <!-- Stage -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.stage') }}</span>
                            <span
                                v-if="detailUnit.stage"
                                class="rounded px-2 py-0.5 text-xs font-medium"
                                :class="STAGE_COLORS[detailUnit.stage] ?? ''"
                            >
                                {{ t(`structure.stage.${detailUnit.stage}`) }}
                            </span>
                            <span v-else class="font-medium text-slate-900 dark:text-white">&mdash;</span>
                        </div>

                        <!-- Description -->
                        <div v-if="detailUnit.description" class="text-sm">
                            <div class="mb-1 text-slate-500 dark:text-slate-400">{{ t('structure.detail.description') }}</div>
                            <p class="text-slate-700 dark:text-slate-300">{{ detailUnit.description }}</p>
                        </div>

                        <!-- Legal name -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.legal_name') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ detailUnit.legal_name ?? '\u2014' }}</span>
                        </div>

                        <!-- INN -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.inn') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ detailUnit.inn ?? '\u2014' }}</span>
                        </div>

                        <!-- Employees count -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.employees') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ detailUnit.employees_count }}</span>
                        </div>

                        <!-- Children count -->
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500 dark:text-slate-400">{{ t('structure.detail.subdivisions') }}</span>
                            <span class="font-medium text-slate-900 dark:text-white">{{ childrenOf(detailUnit.id).length }}</span>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-200 px-6 py-3 dark:border-slate-700">
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
                            @click="detailUnit = null"
                        >
                            {{ t('modal.btn.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- CRUD Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="showModal = false"
            >
                <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-slate-800">
                    <h2 class="mb-4 text-lg font-semibold text-slate-900 dark:text-white">
                        {{ editingUnit ? t('structure.edit') : t('structure.add') }}
                    </h2>

                    <form @submit.prevent="submit" class="space-y-4">
                        <!-- ID (only for create) -->
                        <div v-if="!editingUnit">
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">ID (slug)</label>
                            <input
                                v-model="form.id"
                                type="text"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                placeholder="my-company"
                            />
                            <p v-if="form.errors.id" class="mt-1 text-xs text-red-500">{{ form.errors.id }}</p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.name') }}
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                        </div>

                        <!-- Unit type -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.unit_type') }}
                            </label>
                            <select
                                v-model="form.unit_type"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            >
                                <option :value="null">--</option>
                                <option value="revenue">{{ t('structure.type.revenue') }}</option>
                                <option value="service">{{ t('structure.type.service') }}</option>
                            </select>
                        </div>

                        <!-- Parent -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.parent') }}
                            </label>
                            <select
                                v-model="form.parent_id"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            >
                                <option :value="null">--</option>
                                <option v-for="u in parentOptions" :key="u.id" :value="u.id">
                                    {{ u.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Color -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.color') }}
                            </label>
                            <div class="flex items-center gap-3">
                                <input v-model="form.color" type="color" class="h-8 w-8 cursor-pointer rounded border-0" />
                                <input
                                    v-model="form.color"
                                    type="text"
                                    class="w-24 rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                />
                            </div>
                        </div>

                        <!-- Head -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.head') }}
                            </label>
                            <select
                                v-model="form.head_id"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            >
                                <option :value="null">--</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }} ({{ emp.position }})
                                </option>
                            </select>
                            <p v-if="form.errors.head_id" class="mt-1 text-xs text-red-500">{{ form.errors.head_id }}</p>
                        </div>

                        <!-- Deputy -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.deputy') }}
                            </label>
                            <select
                                v-model="form.deputy_id"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            >
                                <option :value="null">--</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">
                                    {{ emp.name }} ({{ emp.position }})
                                </option>
                            </select>
                            <p v-if="form.errors.deputy_id" class="mt-1 text-xs text-red-500">{{ form.errors.deputy_id }}</p>
                        </div>

                        <!-- Founded at -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.founded_at') }}
                            </label>
                            <input
                                v-model="form.founded_at"
                                type="date"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            />
                            <p v-if="form.errors.founded_at" class="mt-1 text-xs text-red-500">{{ form.errors.founded_at }}</p>
                        </div>

                        <!-- Website -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.website') }}
                            </label>
                            <input
                                v-model="form.website"
                                type="text"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                                placeholder="example.com"
                            />
                            <p v-if="form.errors.website" class="mt-1 text-xs text-red-500">{{ form.errors.website }}</p>
                        </div>

                        <!-- Stage -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.stage') }}
                            </label>
                            <select
                                v-model="form.stage"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            >
                                <option :value="null">--</option>
                                <option v-for="s in stages" :key="s" :value="s">
                                    {{ t(`structure.stage.${s}`) }}
                                </option>
                            </select>
                            <p v-if="form.errors.stage" class="mt-1 text-xs text-red-500">{{ form.errors.stage }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.description') }}
                            </label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            />
                            <p v-if="form.errors.description" class="mt-1 text-xs text-red-500">{{ form.errors.description }}</p>
                        </div>

                        <!-- Legal name -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.legal_name') }}
                            </label>
                            <input
                                v-model="form.legal_name"
                                type="text"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            />
                            <p v-if="form.errors.legal_name" class="mt-1 text-xs text-red-500">{{ form.errors.legal_name }}</p>
                        </div>

                        <!-- INN -->
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700 dark:text-slate-300">
                                {{ t('structure.field.inn') }}
                            </label>
                            <input
                                v-model="form.inn"
                                type="text"
                                class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-700 dark:text-white"
                            />
                            <p v-if="form.errors.inn" class="mt-1 text-xs text-red-500">{{ form.errors.inn }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end gap-3 pt-2">
                            <button
                                type="button"
                                class="rounded-md px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
                                @click="showModal = false"
                            >
                                {{ t('modal.btn.cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
                                :disabled="form.processing"
                            >
                                {{ t('modal.btn.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Delete confirmation -->
        <Teleport to="body">
            <div
                v-if="confirmDeleteUnit"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="confirmDeleteUnit = null"
            >
                <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl dark:bg-slate-800">
                    <h3 class="mb-2 text-lg font-semibold text-slate-900 dark:text-white">
                        {{ t('structure.delete') }}
                    </h3>
                    <p class="mb-4 text-sm text-slate-600 dark:text-slate-400">
                        {{ t('structure.delete_confirm') }}
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-md px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-700"
                            @click="confirmDeleteUnit = null"
                        >
                            {{ t('modal.btn.cancel') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500"
                            @click="doDelete"
                        >
                            {{ t('modal.btn.delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
