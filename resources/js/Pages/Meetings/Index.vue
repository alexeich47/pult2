<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import MeetingFormModal from '../../Components/Pult/MeetingFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Paginated, Meeting, Unit } from '../../types';

interface Props {
    meetings: Paginated<Meeting>;
    allUnits: Unit[];
    types: string[];
    statuses: string[];
    filters: Partial<Record<string, string>>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const TYPE_COLORS: Record<string, string> = {
    standup: '#22c55e',
    weekly: '#3b82f6',
    monthly: '#8b5cf6',
    one_on_one: '#f59e0b',
    retro: '#ec4899',
    planning: '#06b6d4',
    other: '#64748b',
};

const STATUS_COLORS: Record<string, string> = {
    scheduled: '#3b82f6',
    completed: '#22c55e',
    cancelled: '#94a3b8',
};

type FilterTab = 'upcoming' | 'past' | 'all';
const activeFilter = computed<FilterTab>(() => {
    if (props.filters.status === 'completed') return 'past';
    if (props.filters.status === 'scheduled') return 'upcoming';
    return 'all';
});

function applyFilter(tab: FilterTab) {
    const filters: Record<string, string> = {};
    if (tab === 'upcoming') filters.status = 'scheduled';
    if (tab === 'past') filters.status = 'completed';
    router.get('/meetings', { filter: filters }, { preserveScroll: true, preserveState: true });
}

const showModal = ref(false);
const editing = ref<Meeting | null>(null);

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(meeting: Meeting) {
    editing.value = meeting;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function destroy(meeting: Meeting) {
    if (!confirm(t('meetings.delete_confirm'))) return;
    router.delete(`/meetings/${meeting.id}`, { preserveScroll: true });
}

function unitFor(meeting: Meeting): Unit | undefined {
    return props.allUnits.find((u) => u.id === meeting.unit_id);
}

function formatDateTime(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleString(undefined, {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatDuration(minutes: number): string {
    if (minutes < 60) return `${minutes} min`;
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return m > 0 ? `${h}h ${m}m` : `${h}h`;
}

const FILTER_TABS: { id: FilterTab; key: string }[] = [
    { id: 'upcoming', key: 'meetings.filter.upcoming' },
    { id: 'past', key: 'meetings.filter.past' },
    { id: 'all', key: 'meetings.filter.all' },
];
</script>

<template>
    <Head :title="t('meetings.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('meetings.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('meetings.subtitle') }}</p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('meetings.btn.add') }}
                </button>
            </div>

            <!-- Filter tabs -->
            <div class="mb-4 flex gap-2">
                <button
                    v-for="tab in FILTER_TABS"
                    :key="tab.id"
                    type="button"
                    :class="[
                        'rounded-full border px-4 py-1.5 text-xs font-medium transition-colors',
                        activeFilter === tab.id
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                            : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50',
                    ]"
                    @click="applyFilter(tab.id)"
                >
                    {{ t(tab.key) }}
                </button>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('meetings.col.title') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('meetings.col.type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('meetings.col.datetime') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('meetings.col.duration') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('meetings.col.location') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('meetings.col.status') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="meetings.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center">
                                <div class="text-4xl">&#128197;</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('meetings.empty') }}</div>
                            </td>
                        </tr>
                        <tr
                            v-for="meeting in meetings.data"
                            :key="meeting.id"
                            class="cursor-pointer hover:bg-slate-50"
                            @click="openEdit(meeting)"
                        >
                            <td class="px-4 py-3 text-sm">
                                <div class="font-medium text-slate-900">{{ meeting.title }}</div>
                                <div v-if="unitFor(meeting)" class="mt-0.5">
                                    <Badge :color="unitFor(meeting)!.color">{{ unitFor(meeting)!.name }}</Badge>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :color="TYPE_COLORS[meeting.type]">{{ t(`meetings.type.${meeting.type}`) }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ formatDateTime(meeting.scheduled_at) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ formatDuration(meeting.duration_minutes) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-500">{{ meeting.location ?? '---' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge :color="STATUS_COLORS[meeting.status]">{{ t(`meetings.status.${meeting.status}`) }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button
                                    v-if="can.delete"
                                    type="button"
                                    class="text-xs text-rose-500 hover:text-rose-700"
                                    @click.stop="destroy(meeting)"
                                >
                                    &#10005;
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <Pagination
                    :links="meetings.links"
                    :from="meetings.from"
                    :to="meetings.to"
                    :total="meetings.total"
                />
            </div>
        </div>

        <MeetingFormModal
            :show="showModal"
            :meeting="editing"
            :units="allUnits"
            :types="types"
            :statuses="statuses"
            @close="closeModal"
        />
    </AppLayout>
</template>
