<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import ServiceFormModal from '../../Components/Pult/ServiceFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Service, ServiceBilling, ServiceCurrency, ServiceStatus, Unit } from '../../types';

interface Totals {
    total: number;
    active: number;
    subs: number;
    monthly_total: number;
}

interface Props {
    services: Service[];
    allUnits: Unit[];
    categories: string[];
    currencies: ServiceCurrency[];
    billingCycles: ServiceBilling[];
    statuses: ServiceStatus[];
    totals: Totals;
    filters: Partial<Record<string, string>>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const STATUS_COLORS: Record<ServiceStatus, string> = {
    active: '#22c55e',
    trial: '#f59e0b',
    inactive: '#94a3b8',
};

const BILLING_COLORS: Record<ServiceBilling, string> = {
    monthly: '#3b82f6',
    yearly: '#8b5cf6',
    once: '#64748b',
};

type FilterChip = 'all' | 'active' | 'subscription' | 'once';

const activeChip = computed<FilterChip>(() => {
    if (props.filters.status === 'active') return 'active';
    if (props.filters.billing === 'monthly') return 'subscription';
    if (props.filters.billing === 'once') return 'once';
    return 'all';
});

function applyChip(chip: FilterChip) {
    const filters: Record<string, string> = {};
    if (chip === 'active') filters.status = 'active';
    if (chip === 'subscription') filters.billing = 'monthly';
    if (chip === 'once') filters.billing = 'once';
    router.get('/services', { filter: filters }, { preserveScroll: true, preserveState: true });
}

const showModal = ref(false);
const editing = ref<Service | null>(null);

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(service: Service) {
    editing.value = service;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function destroy(service: Service) {
    if (!confirm(t('services.delete_confirm'))) return;
    router.delete(`/services/${service.id}`, { preserveScroll: true });
}

function unitFor(service: Service): Unit | undefined {
    return service.unit ?? props.allUnits.find((u) => u.id === service.unit_id);
}

function formatCost(service: Service): string {
    const cost = parseFloat(service.cost);
    if (cost === 0) return t('services.free');
    return `${service.currency} ${cost.toFixed(2)}`;
}

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}

const CHIPS: { id: FilterChip; key: string }[] = [
    { id: 'all', key: 'services.filter.all' },
    { id: 'active', key: 'services.filter.active' },
    { id: 'subscription', key: 'services.filter.subscription' },
    { id: 'once', key: 'services.filter.once' },
];
</script>

<template>
    <Head :title="t('services.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('services.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">
                        {{ t('services.subtitle', { total: totals.total, active: totals.active, subs: totals.subs }) }}
                    </p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('services.btn.add') }}
                </button>
            </div>

            <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="chip in CHIPS"
                        :key="chip.id"
                        type="button"
                        :class="[
                            'rounded-full border px-3 py-1 text-xs font-medium transition-colors',
                            activeChip === chip.id
                                ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                                : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50',
                        ]"
                        @click="applyChip(chip.id)"
                    >
                        {{ t(chip.key) }}
                    </button>
                </div>
                <div class="text-sm text-slate-600">
                    {{ t('services.monthly_total') }}:
                    <strong class="font-mono text-slate-900">${{ totals.monthly_total.toFixed(2) }}</strong>
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.category') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.company') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.cost') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.billing') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.next_payment') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('services.col.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="services.length === 0">
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="text-4xl">📦</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('services.empty') }}</div>
                            </td>
                        </tr>
                        <tr
                            v-for="svc in services"
                            :key="svc.id"
                            class="cursor-pointer hover:bg-slate-50"
                            @click="openEdit(svc)"
                        >
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-slate-900">{{ svc.name }}</div>
                                <div v-if="svc.url" class="text-xs text-slate-500">{{ svc.url }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ svc.category }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="unitFor(svc)" :color="unitFor(svc)!.color">{{ unitFor(svc)!.name }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-slate-700">{{ formatCost(svc) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :color="BILLING_COLORS[svc.billing]">{{ t(`services.billing.${svc.billing}`) }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ formatDate(svc.next_payment) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :color="STATUS_COLORS[svc.status]">{{ t(`services.status.${svc.status}`) }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    v-if="can.delete"
                                    type="button"
                                    class="text-xs text-rose-500 hover:text-rose-700"
                                    @click.stop="destroy(svc)"
                                >
                                    ✕
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ServiceFormModal
            :show="showModal"
            :service="editing"
            :units="allUnits"
            :categories="categories"
            :currencies="currencies"
            :billing-cycles="billingCycles"
            :statuses="statuses"
            @close="closeModal"
        />
    </AppLayout>
</template>
