<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import RiskEntryFormModal from '../../Components/Pult/RiskEntryFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { RiskEntry, RiskType } from '../../types';

interface Props {
    entriesByType: Record<RiskType, RiskEntry[]>;
    types: RiskType[];
    statusesByType: Record<RiskType, string[]>;
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

const showModal = ref(false);
const defaultType = ref<RiskType>('risk');

function openCreate(type: RiskType) {
    defaultType.value = type;
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
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900">{{ t('risks.title') }}</h1>
                <p class="mt-1 max-w-2xl text-sm text-slate-600">{{ t('risks.subtitle') }}</p>
            </div>

            <div class="space-y-6">
                <section
                    v-for="type in types"
                    :key="type"
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                >
                    <header
                        class="flex items-center gap-3 border-b px-5 py-3"
                        :style="{
                            borderTopColor: TYPE_COLORS[type],
                            borderTopWidth: '3px',
                            backgroundColor: TYPE_COLORS[type] + '0d',
                        }"
                    >
                        <span
                            class="inline-block h-2 w-2 rounded-full"
                            :style="{ backgroundColor: TYPE_COLORS[type] }"
                        ></span>
                        <h2 class="text-sm font-semibold text-slate-800">{{ t(`risks.log.${type}.title`) }}</h2>
                        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-600">
                            {{ entriesByType[type].length }}
                        </span>
                        <button
                            v-if="can.create"
                            type="button"
                            class="ml-auto rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs text-slate-700 hover:bg-slate-50"
                            @click="openCreate(type)"
                        >
                            + {{ t('risks.btn.add') }}
                        </button>
                    </header>
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="w-24 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.id') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.name') }}</th>
                                <th class="w-32 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.date') }}</th>
                                <th class="w-40 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.owner') }}</th>
                                <th class="w-40 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            <tr v-if="entriesByType[type].length === 0">
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">{{ t('risks.empty') }}</td>
                            </tr>
                            <tr
                                v-for="entry in entriesByType[type]"
                                :key="entry.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-4 py-2 font-mono text-xs text-slate-500">{{ entry.display_id }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <Link
                                        :href="`/risks/${entry.display_id}`"
                                        class="font-medium text-slate-900 hover:text-indigo-700"
                                    >
                                        {{ entry.name }}
                                    </Link>
                                </td>
                                <td class="px-4 py-2 text-xs text-slate-500">{{ formatDate(entry.entry_date) }}</td>
                                <td class="px-4 py-2 text-sm text-slate-700">{{ entry.owner_name }}</td>
                                <td class="px-4 py-2 text-sm">
                                    <Badge :color="STATUS_COLORS[entry.status] ?? '#6b7280'">
                                        {{ t(`risks.status.${entry.status}`) }}
                                    </Badge>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>
        </div>

        <RiskEntryFormModal
            :show="showModal"
            :entry="null"
            :default-type="defaultType"
            :types="types"
            :statuses-by-type="statusesByType"
            @close="closeModal"
        />
    </AppLayout>
</template>
