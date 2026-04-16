<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Idea, Service, Unit } from '../../types';

interface Stats {
    personnel: { total: number; active: number; vacancies: number; fired: number };
    ideas: { total: number; by_status: Record<string, number> };
    services: { total: number; active: number; mrr: number };
}

interface Props {
    unit: Unit;
    stats: Stats;
    employees: Employee[];
    ideas: Idea[];
    services: Service[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}

function formatCost(svc: Service): string {
    const cost = parseFloat(svc.cost);
    if (cost === 0) return t('services.free');
    return `${svc.currency} ${cost.toFixed(2)}`;
}
</script>

<template>
    <Head :title="unit.name" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <!-- Header -->
            <div class="mb-8 flex items-center gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-xl text-lg font-bold text-white"
                    :style="{ backgroundColor: unit.color }"
                >
                    {{ unit.unit_type === 'revenue' ? '$' : '🛠' }}
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ unit.name }}</h1>
                    <p class="mt-0.5 text-sm text-slate-600">
                        {{ t('unit_dashboard.sub') }}
                        <Badge :color="unit.color" variant="soft">
                            {{ unit.unit_type === 'revenue' ? 'Revenue' : 'Service' }}
                        </Badge>
                    </p>
                </div>
            </div>

            <!-- KPI cards -->
            <div class="mb-8 grid gap-4 sm:grid-cols-3">
                <!-- Personnel -->
                <Link
                    href="/personnel"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ t('dashboard.personnel.title') }}
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ stats.personnel.total }}</div>
                    <div class="mt-2 flex gap-3 text-xs text-slate-500">
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded-full bg-emerald-500"></span>
                            {{ t('dashboard.personnel.active') }}: {{ stats.personnel.active }}
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="inline-block h-2 w-2 rounded-full bg-amber-500"></span>
                            {{ t('dashboard.personnel.vacancies') }}: {{ stats.personnel.vacancies }}
                        </span>
                    </div>
                </Link>

                <!-- Ideas -->
                <Link
                    href="/ideas"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ t('dashboard.ideas.title') }}
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ stats.ideas.total }}</div>
                    <div class="mt-2 flex gap-3 text-xs text-slate-500">
                        <span>{{ t('dashboard.ideas.new') }}: {{ stats.ideas.by_status.new ?? 0 }}</span>
                        <span>{{ t('dashboard.ideas.in_progress') }}: {{ stats.ideas.by_status.in_progress ?? 0 }}</span>
                    </div>
                </Link>

                <!-- Services -->
                <Link
                    href="/services"
                    class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ t('dashboard.services.title') }}
                    </div>
                    <div class="text-3xl font-bold text-slate-900">
                        ${{ stats.services.mrr.toFixed(0) }}
                    </div>
                    <div class="mt-2 flex gap-3 text-xs text-slate-500">
                        <span>{{ t('dashboard.services.mrr') }}</span>
                        <span class="text-slate-400">·</span>
                        <span>{{ t('dashboard.services.active') }}: {{ stats.services.active }}</span>
                    </div>
                </Link>
            </div>

            <!-- Content sections -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Employees -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h2 class="text-sm font-semibold text-slate-800">{{ t('unit_dashboard.employees_title') }}</h2>
                        <Link href="/personnel" class="text-xs text-indigo-600 hover:underline">{{ t('unit_dashboard.view_all') }}</Link>
                    </div>
                    <div v-if="employees.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">
                        {{ t('unit_dashboard.no_data') }}
                    </div>
                    <div v-else class="divide-y divide-slate-100">
                        <div
                            v-for="emp in employees"
                            :key="emp.id"
                            class="flex items-center gap-3 px-5 py-2.5"
                        >
                            <div class="flex-1">
                                <div class="text-sm font-medium text-slate-900">{{ emp.name }}</div>
                                <div class="text-xs text-slate-500">{{ emp.position }} · {{ emp.department }}</div>
                            </div>
                            <span v-if="emp.telegram" class="text-xs text-slate-400">{{ emp.telegram }}</span>
                        </div>
                    </div>
                </div>

                <!-- Ideas -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h2 class="text-sm font-semibold text-slate-800">{{ t('unit_dashboard.ideas_title') }}</h2>
                        <Link href="/ideas" class="text-xs text-indigo-600 hover:underline">{{ t('unit_dashboard.view_all') }}</Link>
                    </div>
                    <div v-if="ideas.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">
                        {{ t('unit_dashboard.no_data') }}
                    </div>
                    <div v-else class="divide-y divide-slate-100">
                        <Link
                            v-for="idea in ideas"
                            :key="idea.id"
                            :href="`/ideas/${idea.display_id}`"
                            class="block px-5 py-2.5 hover:bg-slate-50"
                        >
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-[10px] text-slate-400">{{ idea.display_id }}</span>
                                <Badge :color="idea.status === 'approved' ? '#22c55e' : idea.status === 'in_progress' ? '#8b5cf6' : idea.status === 'rejected' ? '#ef4444' : '#6c63ff'">
                                    {{ t(`ideas.status.${idea.status}`) }}
                                </Badge>
                            </div>
                            <div class="mt-1 text-sm text-slate-900">{{ idea.title }}</div>
                            <div class="mt-0.5 text-xs text-slate-500">{{ idea.author?.name ?? '—' }} · {{ formatDate(idea.created_at) }}</div>
                        </Link>
                    </div>
                </div>

                <!-- Services -->
                <div class="rounded-xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h2 class="text-sm font-semibold text-slate-800">{{ t('unit_dashboard.services_title') }}</h2>
                        <Link href="/services" class="text-xs text-indigo-600 hover:underline">{{ t('unit_dashboard.view_all') }}</Link>
                    </div>
                    <div v-if="services.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">
                        {{ t('unit_dashboard.no_data') }}
                    </div>
                    <table v-else class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.name') }}</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.category') }}</th>
                                <th class="px-5 py-2 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.cost') }}</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.billing') }}</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="svc in services" :key="svc.id">
                                <td class="px-5 py-2 text-sm">
                                    <div class="font-medium text-slate-900">{{ svc.name }}</div>
                                    <div v-if="svc.url" class="text-xs text-slate-500">{{ svc.url }}</div>
                                </td>
                                <td class="px-5 py-2 text-sm text-slate-700">{{ svc.category }}</td>
                                <td class="px-5 py-2 text-right font-mono text-sm text-slate-700">{{ formatCost(svc) }}</td>
                                <td class="px-5 py-2 text-sm">
                                    <Badge :color="svc.billing === 'monthly' ? '#3b82f6' : svc.billing === 'yearly' ? '#8b5cf6' : '#64748b'">
                                        {{ t(`services.billing.${svc.billing}`) }}
                                    </Badge>
                                </td>
                                <td class="px-5 py-2 text-sm">
                                    <Badge :color="svc.status === 'active' ? '#22c55e' : svc.status === 'trial' ? '#f59e0b' : '#94a3b8'">
                                        {{ t(`services.status.${svc.status}`) }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
