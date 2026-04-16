<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Unit } from '../../types';

interface UnitWithMeta extends Unit {
    employees_count: number;
    children: UnitWithMeta[];
}

interface Props {
    units: UnitWithMeta[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

const showModal = ref(false);
const editingUnit = ref<UnitWithMeta | null>(null);
const confirmDeleteUnit = ref<UnitWithMeta | null>(null);

const form = useForm({
    id: '',
    name: '',
    color: '#3b82f6',
    unit_type: 'revenue' as 'revenue' | 'service' | null,
    parent_id: null as string | null,
    sort_order: 0,
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
    form.clearErrors();
    showModal.value = true;
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

const TYPE_LABELS: Record<string, string> = {
    revenue: 'structure.type.revenue',
    service: 'structure.type.service',
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
                                <div class="text-lg font-semibold text-slate-900 dark:text-white">
                                    {{ rootUnit.name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ rootUnit.employees_count }} {{ t('structure.field.name') === 'Название' ? 'сотр.' : 'emp.' }}
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
                                        <div class="font-medium text-slate-900 dark:text-white">
                                            {{ unit.name }}
                                        </div>
                                        <div class="flex gap-2 text-xs text-slate-500">
                                            <span v-if="unit.unit_type" class="rounded bg-slate-100 px-1.5 py-0.5 dark:bg-slate-700">
                                                {{ t(TYPE_LABELS[unit.unit_type] ?? '') }}
                                            </span>
                                            <span>{{ unit.employees_count }} {{ t('structure.field.name') === 'Название' ? 'сотр.' : 'emp.' }}</span>
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
                                            <div class="text-sm font-medium text-slate-800 dark:text-slate-200">
                                                {{ child.name }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ child.employees_count }} {{ t('structure.field.name') === 'Название' ? 'сотр.' : 'emp.' }}
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

        <!-- CRUD Modal -->
        <Teleport to="body">
            <div
                v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                @click.self="showModal = false"
            >
                <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-slate-800">
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
