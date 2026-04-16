<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import EmployeeFormModal from '../../Components/Pult/EmployeeFormModal.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Paginated, Unit } from '../../types';

interface Props {
    employees: Paginated<Employee>;
    allUnits: Unit[];
    departments: string[];
    statuses: string[];
    filters: Record<string, string>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const showModal = ref(false);
const editing = ref<Employee | null>(null);

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(employee: Employee) {
    editing.value = employee;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function destroy(employee: Employee) {
    if (!confirm(t('personnel.delete_confirm'))) return;
    router.delete(`/personnel/${employee.id}`, { preserveScroll: true });
}

function unitFor(employee: Employee): Unit | undefined {
    return employee.unit ?? props.allUnits.find((u) => u.id === employee.unit_id);
}

const count = computed(() => props.employees.total);
const rows = computed(() => props.employees.data);
</script>

<template>
    <Head :title="t('personnel.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">
                        {{ t('personnel.title') }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ t('personnel.subtitle', { count }) }}
                    </p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('personnel.add') }}
                </button>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.name') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.position') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.company') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.department') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.email') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.telegram') }}
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.status') }}
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">
                                {{ t('table.actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="rows.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="text-4xl">👤</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('table.empty') }}</div>
                            </td>
                        </tr>
                        <tr
                            v-for="emp in rows"
                            :key="emp.id"
                            class="cursor-pointer hover:bg-slate-50"
                            @click="openEdit(emp)"
                        >
                            <td class="px-4 py-3 text-sm">
                                <span v-if="emp.status === 'vacancy'" class="italic text-slate-400">
                                    {{ t('table.vacancy') }}
                                </span>
                                <span v-else class="font-medium text-slate-900">{{ emp.name }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ emp.position }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="unitFor(emp)" :color="unitFor(emp)!.color">
                                    {{ unitFor(emp)!.name }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ emp.department }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <span v-if="emp.email">{{ emp.email }}</span>
                                <span v-else class="text-slate-300">—</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <span v-if="emp.telegram">{{ emp.telegram }}</span>
                                <span v-else class="text-slate-300">—</span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :color="emp.status === 'active' ? '#22c55e' : '#f59e0b'">
                                    {{ t(`status.${emp.status}`) }}
                                </Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    v-if="can.delete"
                                    type="button"
                                    class="text-xs text-rose-500 hover:text-rose-700"
                                    @click.stop="destroy(emp)"
                                >
                                    ✕
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination
                    :links="employees.links"
                    :from="employees.from"
                    :to="employees.to"
                    :total="employees.total"
                />
            </div>
        </div>

        <EmployeeFormModal
            :show="showModal"
            :employee="editing"
            :units="allUnits"
            :departments="departments"
            :statuses="statuses"
            @close="closeModal"
        />
    </AppLayout>
</template>
