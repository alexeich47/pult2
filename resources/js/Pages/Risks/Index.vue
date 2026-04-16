<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import RiskEntryFormModal from '../../Components/Pult/RiskEntryFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, RiskEntry, RiskType } from '../../types';

interface Props {
    entriesByType: Record<RiskType, RiskEntry[]>;
    types: RiskType[];
    statusesByType: Record<RiskType, string[]>;
    employees: Employee[];
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const TYPE_COLORS: Record<RiskType, string> = {
    risk: '#ef4444',
    issue: '#f59e0b',
    fuckup: '#8b5cf6',
    workaround: '#06b6d4',
};

const STATUS_COLORS: Record<string, string> = {
    open: '#ef4444',
    in_progress: '#f59e0b',
    mitigated: '#06b6d4',
    closed: '#22c55e',
    active: '#f59e0b',
    resolved: '#22c55e',
};

// ── Active tab ─────────────────────────────────────────────────────
const activeTab = ref<RiskType>('risk');

const activeEntries = computed(() => props.entriesByType[activeTab.value] ?? []);
const activeCount = computed(() => activeEntries.value.length);

// ── Form modal ─────────────────────────────────────────────────────
const showModal = ref(false);

function openCreate() {
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="t('risks.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('risks.title') }}</h1>
                    <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ t('risks.subtitle') }}</p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('risks.btn.add') }}
                </button>
            </div>

            <!-- Google-Sheets-style tab bar -->
            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex border-b border-slate-200">
                    <button
                        v-for="type in types"
                        :key="type"
                        type="button"
                        :class="[
                            'relative flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors',
                            activeTab === type
                                ? 'bg-white text-slate-900'
                                : 'bg-slate-50 text-slate-500 hover:bg-slate-100 hover:text-slate-700',
                        ]"
                        @click="activeTab = type"
                    >
                        <!-- Color dot -->
                        <span
                            class="inline-block h-2.5 w-2.5 rounded-full"
                            :style="{ backgroundColor: TYPE_COLORS[type] }"
                        ></span>
                        {{ t(`risks.log.${type}.title`) }}
                        <!-- Count badge -->
                        <span
                            :class="[
                                'rounded-full px-1.5 py-0.5 text-[10px] font-semibold',
                                activeTab === type
                                    ? 'bg-slate-200 text-slate-700'
                                    : 'bg-slate-200/60 text-slate-500',
                            ]"
                        >
                            {{ entriesByType[type].length }}
                        </span>
                        <!-- Active indicator bar -->
                        <span
                            v-if="activeTab === type"
                            class="absolute bottom-0 left-0 right-0 h-0.5"
                            :style="{ backgroundColor: TYPE_COLORS[type] }"
                        ></span>
                    </button>
                </div>

                <!-- Table for active tab -->
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="w-24 px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.id') }}</th>
                            <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.name') }}</th>
                            <th class="w-32 px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.date') }}</th>
                            <th class="w-40 px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.owner') }}</th>
                            <th class="w-40 px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="activeEntries.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400">{{ t('risks.empty') }}</td>
                        </tr>
                        <tr
                            v-for="entry in activeEntries"
                            :key="entry.id"
                            class="hover:bg-slate-50"
                        >
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ entry.display_id }}</td>
                            <td class="px-4 py-2.5 text-sm">
                                <Link
                                    :href="`/risks/${entry.display_id}`"
                                    class="font-medium text-slate-900 hover:text-indigo-700"
                                >
                                    {{ entry.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-slate-500">{{ formatDate(entry.entry_date) }}</td>
                            <td class="px-4 py-2.5 text-sm text-slate-700">{{ entry.owner_name }}</td>
                            <td class="px-4 py-2.5 text-sm">
                                <Badge :color="STATUS_COLORS[entry.status] ?? '#6b7280'">
                                    {{ t(`risks.status.${entry.status}`) }}
                                </Badge>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <RiskEntryFormModal
            :show="showModal"
            :entry="null"
            :default-type="activeTab"
            :types="types"
            :statuses-by-type="statusesByType"
            :employees="employees"
            @close="closeModal"
        />
    </AppLayout>
</template>
