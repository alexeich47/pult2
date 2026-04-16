<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import TicketFormModal from '../Components/Pult/TicketFormModal.vue';
import { useTranslations } from '../Composables/useTranslations';

type TicketId = 'dayoff' | 'server' | 'domain' | 'payment';

interface Stats {
    personnel: { total: number; active: number; vacancies: number };
    ideas: {
        total: number;
        by_status: Record<string, number>;
        by_priority: Record<string, number>;
    };
    risks: {
        total: number;
        by_type: Record<string, number>;
        open: number;
    };
    services: { total: number; active: number; mrr: number };
}

interface ActivityEntry {
    id: number;
    description: string;
    log_name: string;
    subject_type: string;
    subject_id: number | null;
    properties: Record<string, unknown>;
    created_at: string;
}

interface Props {
    stats: Stats;
    recentActivity: ActivityEntry[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

// Ticket modal
const TICKETS: { id: TicketId; color: string }[] = [
    { id: 'dayoff', color: '#22c55e' },
    { id: 'server', color: '#3b82f6' },
    { id: 'domain', color: '#f59e0b' },
    { id: 'payment', color: '#ec4899' },
];

const showTicketModal = ref(false);
const activeTicket = ref<TicketId | null>(null);
const activeColor = ref('#6b7280');

function openTicket(ticket: { id: TicketId; color: string }) {
    activeTicket.value = ticket.id;
    activeColor.value = ticket.color;
    showTicketModal.value = true;
}

function formatTimeAgo(iso: string): string {
    const diff = Date.now() - new Date(iso).getTime();
    const mins = Math.floor(diff / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    const hours = Math.floor(mins / 60);
    if (hours < 24) return `${hours}h ago`;
    const days = Math.floor(hours / 24);
    return `${days}d ago`;
}

function activityIcon(logName: string): string {
    const icons: Record<string, string> = {
        employee: '👤',
        idea: '💡',
        risk_entry: '⚠️',
        service: '📦',
        default: '📝',
    };
    return icons[logName] ?? icons.default;
}
</script>

<template>
    <Head :title="t('dashboard.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900">{{ t('dashboard.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ t('dashboard.sub') }}</p>
            </div>

            <!-- KPI cards -->
            <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ t('dashboard.section.overview') }}
            </div>
            <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Personnel -->
                <Link href="/personnel" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
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
                <Link href="/ideas" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ t('dashboard.ideas.title') }}
                    </div>
                    <div class="text-3xl font-bold text-slate-900">{{ stats.ideas.total }}</div>
                    <div class="mt-2 flex gap-3 text-xs text-slate-500">
                        <span>{{ t('dashboard.ideas.new') }}: {{ stats.ideas.by_status.new ?? 0 }}</span>
                        <span>{{ t('dashboard.ideas.in_progress') }}: {{ stats.ideas.by_status.in_progress ?? 0 }}</span>
                        <span>{{ t('dashboard.ideas.approved') }}: {{ stats.ideas.by_status.approved ?? 0 }}</span>
                    </div>
                </Link>

                <!-- Risks -->
                <Link href="/risks" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                        {{ t('dashboard.risks.title') }}
                    </div>
                    <div class="text-3xl font-bold" :class="stats.risks.open > 0 ? 'text-rose-600' : 'text-slate-900'">
                        {{ stats.risks.open }}
                    </div>
                    <div class="mt-2 flex gap-3 text-xs text-slate-500">
                        <span>{{ t('dashboard.risks.open') }}</span>
                        <span class="text-slate-400">·</span>
                        <span>{{ t('dashboard.risks.total') }}: {{ stats.risks.total }}</span>
                    </div>
                </Link>

                <!-- Services -->
                <Link href="/services" class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
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

            <!-- Recent Activity -->
            <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ t('dashboard.section.activity') }}
            </div>
            <div class="mb-8 rounded-xl border border-slate-200 bg-white shadow-sm">
                <div v-if="recentActivity.length === 0" class="px-5 py-8 text-center text-sm text-slate-400">
                    {{ t('dashboard.activity_empty') }}
                </div>
                <div v-else class="divide-y divide-slate-100">
                    <div
                        v-for="entry in recentActivity"
                        :key="entry.id"
                        class="flex items-start gap-3 px-5 py-3"
                    >
                        <span class="mt-0.5 text-base">{{ activityIcon(entry.log_name) }}</span>
                        <div class="flex-1 text-sm">
                            <span class="font-medium text-slate-800">{{ entry.subject_type }}</span>
                            <span class="mx-1 text-slate-400">·</span>
                            <span class="text-slate-600">{{ entry.description }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ formatTimeAgo(entry.created_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Ticket cards -->
            <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ t('dashboard.section.tickets') }}
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="ticket in TICKETS"
                    :key="ticket.id"
                    class="cursor-pointer rounded-lg border border-slate-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-[var(--tc)] hover:shadow-lg"
                    :style="{ '--tc': ticket.color }"
                    @click="openTicket(ticket)"
                >
                    <div
                        class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-md text-white"
                        :style="{ backgroundColor: ticket.color }"
                    >
                        <span class="text-lg font-bold">{{ ticket.id[0].toUpperCase() }}</span>
                    </div>
                    <div class="text-base font-semibold text-slate-900">
                        {{ t(`ticket.${ticket.id}.title`) }}
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        {{ t(`ticket.${ticket.id}.desc`) }}
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-sm font-medium" :style="{ color: ticket.color }">
                        {{ t('ticket.cta') }}
                        <span>→</span>
                    </div>
                </div>
            </div>
        </div>

        <TicketFormModal
            :show="showTicketModal"
            :ticket-id="activeTicket"
            :ticket-color="activeColor"
            @close="showTicketModal = false; activeTicket = null"
        />
    </AppLayout>
</template>
