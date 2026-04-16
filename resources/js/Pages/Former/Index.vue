<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Paginated, Unit } from '../../types';

interface Props {
    employees: Paginated<Employee>;
    allUnits: Unit[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

function unitFor(emp: Employee): Unit | undefined {
    return emp.unit ?? props.allUnits.find((u) => u.id === emp.unit_id);
}
</script>

<template>
    <Head :title="t('personnel_tabs.fired')" />

    <AppLayout>
        <div class="mx-auto max-w-5xl px-6 py-8">
            <Link href="/personnel" class="mb-6 inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
                ← {{ t('personnel.title') }}
            </Link>

            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">{{ t('personnel_tabs.fired') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ employees.total }} человек</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.position') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.department') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="employees.data.length === 0">
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="text-4xl">✅</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('table.empty') }}</div>
                            </td>
                        </tr>
                        <tr v-for="emp in employees.data" :key="emp.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ emp.name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ emp.position }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="unitFor(emp)" :color="unitFor(emp)!.color">{{ unitFor(emp)!.name }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ emp.department }}</td>
                        </tr>
                    </tbody>
                </table>
                <Pagination :links="employees.links" :from="employees.from" :to="employees.to" :total="employees.total" />
            </div>
        </div>
    </AppLayout>
</template>
