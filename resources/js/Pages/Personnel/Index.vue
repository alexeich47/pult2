<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import EmployeeFormModal from '../../Components/Pult/EmployeeFormModal.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Paginated, Unit, WorkStage } from '../../types';

interface Props {
    employees: Paginated<Employee>;
    stageCounts: Record<WorkStage, number>;
    totalActive: number;
    firedCount: number;
    tab: WorkStage;
    allUnits: Unit[];
    managers: Employee[];
    departments: string[];
    statuses: string[];
    workStages: WorkStage[];
    filters: Record<string, string>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const TABS: { id: WorkStage; color: string }[] = [
    { id: 'employee', color: '#3b82f6' },
    { id: 'onboarding', color: '#22c55e' },
    { id: 'probation', color: '#f59e0b' },
    { id: 'offboarding', color: '#ef4444' },
];

const rows = computed(() => props.employees.data);

function switchTab(stage: WorkStage) {
    router.get('/personnel', { tab: stage }, { preserveState: true, preserveScroll: true });
}

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

function managerName(employee: Employee): string {
    return (employee as Employee & { manager?: Employee }).manager?.name ?? '—';
}
</script>

<template>
    <Head :title="t('personnel.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('personnel.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ t('personnel.subtitle', { count: totalActive }) }}
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

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <!-- 4 work_stage tabs -->
                <div class="flex border-b border-slate-200">
                    <button
                        v-for="tb in TABS"
                        :key="tb.id"
                        type="button"
                        class="relative flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors"
                        :style="{
                            backgroundColor: tab === tb.id ? 'var(--tab-active-bg, white)' : 'var(--tab-bg, #f8fafc)',
                            color: tab === tb.id ? tb.color : '#94a3b8',
                        }"
                        @click="switchTab(tb.id)"
                    >
                        <span class="inline-block h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: tb.color }"></span>
                        {{ t(`work_stage.${tb.id}`) }}
                        <span
                            class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold"
                            :style="{
                                backgroundColor: tab === tb.id ? tb.color + '22' : 'rgba(148,163,184,0.15)',
                                color: tab === tb.id ? tb.color : '#94a3b8',
                            }"
                        >
                            {{ stageCounts[tb.id] }}
                        </span>
                        <span v-if="tab === tb.id" class="absolute bottom-0 left-0 right-0 h-0.5" :style="{ backgroundColor: tb.color }"></span>
                    </button>
                </div>

                <!-- Table -->
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.position') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.department') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.manager') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.telegram') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.email') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.actions') }}</th>
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
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ emp.name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ emp.position }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="unitFor(emp)" :color="unitFor(emp)!.color">{{ unitFor(emp)!.name }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ emp.department }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ managerName(emp) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <span v-if="emp.telegram">{{ emp.telegram }}</span>
                                <span v-else class="text-slate-300">—</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">
                                <span v-if="emp.email">{{ emp.email }}</span>
                                <span v-else class="text-slate-300">—</span>
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
                <Pagination :links="employees.links" :from="employees.from" :to="employees.to" :total="employees.total" />
            </div>

        </div>

        <EmployeeFormModal
            :show="showModal"
            :employee="editing"
            :units="allUnits"
            :managers="managers"
            :departments="departments"
            :statuses="statuses"
            :work-stages="workStages"
            @close="closeModal"
        />
    </AppLayout>
</template>
