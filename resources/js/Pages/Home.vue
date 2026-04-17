<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import { useTranslations } from '../Composables/useTranslations';

interface ActivityEntry {
    id: number;
    description: string;
    log_name: string;
    subject_type: string;
    created_at: string;
}

interface Props {
    recentActivity: ActivityEntry[];
}

const props = defineProps<Props>();
const { t } = useTranslations();

const quickLinks = [
    { key: 'nav.dashboard', href: '/dashboard', icon: '📊' },
    { key: 'nav.personnel', href: '/personnel', icon: '👥' },
    { key: 'nav.ideas', href: '/ideas', icon: '💡' },
    { key: 'nav.services', href: '/services', icon: '📦' },
    { key: 'nav.finance', href: '/finance', icon: '💰' },
];

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
        mvr_entry: '💰',
        default: '📝',
    };
    return icons[logName] ?? icons.default;
}
</script>

<template>
    <Head :title="t('home_page.title')" />

    <AppLayout>
        <div class="mx-auto max-w-4xl px-6 py-12">
            <!-- Hero -->
            <div class="mb-10 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500 text-2xl font-bold text-white shadow-lg">
                    P
                </div>
                <h1 class="text-3xl font-bold text-slate-900">{{ t('home_page.title') }}</h1>
                <p class="mt-2 text-base text-slate-500">{{ t('home_page.sub') }}</p>
            </div>

            <!-- Quick links -->
            <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ t('home_page.quick_links') }}
            </div>
            <div class="mb-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <Link
                    v-for="link in quickLinks"
                    :key="link.href"
                    :href="link.href"
                    class="group flex flex-col items-center gap-2 rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md"
                >
                    <span class="text-2xl">{{ link.icon }}</span>
                    <span class="text-sm font-medium text-slate-700 group-hover:text-indigo-600">{{ t(link.key) }}</span>
                </Link>
            </div>

            <!-- Recent activity -->
            <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ t('home_page.recent_activity') }}
            </div>
            <div class="rounded-xl border border-slate-200 bg-white shadow-sm">
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
                            <span class="mx-1 text-slate-400">&middot;</span>
                            <span class="text-slate-600">{{ entry.description }}</span>
                        </div>
                        <span class="shrink-0 text-xs text-slate-400">{{ formatTimeAgo(entry.created_at) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
