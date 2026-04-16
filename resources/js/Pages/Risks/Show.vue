<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import RiskEntryFormModal from '../../Components/Pult/RiskEntryFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, RiskEntry, RiskType } from '../../types';

interface Props {
    entry: RiskEntry;
    statuses: string[];
    employees: Employee[];
    can: {
        update: boolean | null;
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

const statusesByType: Record<RiskType, string[]> = {
    risk: ['open', 'in_progress', 'mitigated', 'closed'],
    issue: ['open', 'in_progress', 'closed'],
    fuckup: ['open', 'closed'],
    workaround: ['active', 'resolved'],
};

function destroy() {
    if (!confirm(t('risks.delete_confirm'))) return;
    router.delete(`/risks/${props.entry.display_id}`);
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="entry.name" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-6 py-8">
            <Link
                href="/risks"
                class="mb-6 inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900"
            >
                ← {{ t('risks.btn.back') }}
            </Link>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                :style="{ borderTopColor: TYPE_COLORS[entry.type], borderTopWidth: '3px' }"
            >
                <div class="px-6 py-5">
                    <div class="mb-2 flex items-center gap-2 text-xs">
                        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-slate-600">{{ entry.display_id }}</span>
                        <Badge :color="TYPE_COLORS[entry.type]">{{ t(`risks.log.${entry.type}.title`) }}</Badge>
                        <Badge :color="STATUS_COLORS[entry.status] ?? '#6b7280'">{{ t(`risks.status.${entry.status}`) }}</Badge>
                        <span class="ml-auto text-slate-500">{{ formatDate(entry.entry_date) }}</span>
                    </div>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900">{{ entry.name }}</h1>
                    <div class="mt-1 text-sm text-slate-500">{{ t('risks.col.owner') }}: {{ entry.owner_name }}</div>
                </div>

                <div class="px-6 pb-6">
                    <h2 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('risks.detail.desc') }}</h2>
                    <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">{{ entry.description }}</p>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button
                        v-if="can.delete"
                        type="button"
                        class="rounded-md border border-rose-300 bg-white px-3 py-1.5 text-sm text-rose-600 hover:bg-rose-50"
                        @click="destroy"
                    >
                        {{ t('risks.btn.delete') }}
                    </button>
                    <button
                        v-if="can.update"
                        type="button"
                        class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
                        @click="showModal = true"
                    >
                        {{ t('risks.btn.edit') }}
                    </button>
                </div>
            </div>
        </div>

        <RiskEntryFormModal
            :show="showModal"
            :entry="entry"
            :types="['risk', 'issue', 'fuckup', 'workaround']"
            :statuses-by-type="statusesByType"
            :employees="employees"
            @close="showModal = false"
        />
    </AppLayout>
</template>
