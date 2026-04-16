<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Paginated, Unit } from '../../types';

interface Props {
    vacancies: Paginated<Employee>;
    allUnits: Unit[];
    departments: string[];
    can: { create: boolean | null };
}

const props = defineProps<Props>();
const { t } = useTranslations();

function unitFor(emp: Employee): Unit | undefined {
    return emp.unit ?? props.allUnits.find((u) => u.id === emp.unit_id);
}
</script>

<template>
    <Head :title="t('nav.hiring')" />

    <AppLayout>
        <div class="mx-auto max-w-5xl px-6 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">{{ t('nav.hiring') }}</h1>
                <p class="mt-1 text-sm text-slate-600">
                    {{ t('personnel.subtitle', { count: vacancies.total }) }}
                </p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.position') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.department') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="vacancies.data.length === 0">
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="text-4xl">🎉</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('table.empty') }}</div>
                            </td>
                        </tr>
                        <tr v-for="vac in vacancies.data" :key="vac.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ vac.position }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="unitFor(vac)" :color="unitFor(vac)!.color">{{ unitFor(vac)!.name }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ vac.department }}</td>
                        </tr>
                    </tbody>
                </table>
                <Pagination :links="vacancies.links" :from="vacancies.from" :to="vacancies.to" :total="vacancies.total" />
            </div>
        </div>
    </AppLayout>
</template>
