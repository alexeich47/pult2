<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Paginated } from '../../types';

type ArchiveTab = 'ideas' | 'risks' | 'employees';

interface Props {
    items: Paginated<Record<string, unknown>>;
    tab: ArchiveTab;
    counts: Record<ArchiveTab, number>;
}

const props = defineProps<Props>();
const { t } = useTranslations();

const TABS: { id: ArchiveTab; icon: string; color: string; label: string }[] = [
    { id: 'ideas', icon: '💡', color: '#6c63ff', label: 'Ideas' },
    { id: 'risks', icon: '⚠️', color: '#ef4444', label: 'Risks' },
    { id: 'employees', icon: '👤', color: '#94a3b8', label: 'Employees' },
];

function switchTab(tab: ArchiveTab) {
    router.get('/archive', { tab }, { preserveState: true, preserveScroll: true });
}

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}
</script>

<template>
    <Head :title="t('archive.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-slate-900">{{ t('archive.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600">{{ t('archive.sub') }}</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <!-- Tabs -->
                <div class="flex border-b border-slate-200">
                    <button
                        v-for="tb in TABS"
                        :key="tb.id"
                        type="button"
                        class="relative flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors"
                        :style="{
                            backgroundColor: tab === tb.id ? 'var(--tab-active-bg, white)' : 'var(--tab-bg, #f8fafc)',
                            color: tab === tb.id ? tb.color : '#94a3b8',
                        }"
                        @click="switchTab(tb.id)"
                    >
                        <span>{{ tb.icon }}</span>
                        {{ t(`archive.tab.${tb.id}`) }}
                        <span
                            class="rounded-full px-1.5 py-0.5 text-[10px] font-semibold"
                            :style="{
                                backgroundColor: tab === tb.id ? tb.color + '22' : 'rgba(148,163,184,0.15)',
                                color: tab === tb.id ? tb.color : '#94a3b8',
                            }"
                        >
                            {{ counts[tb.id] }}
                        </span>
                        <span v-if="tab === tb.id" class="absolute bottom-0 left-0 right-0 h-0.5" :style="{ backgroundColor: tb.color }"></span>
                    </button>
                </div>

                <!-- Ideas table -->
                <table v-if="tab === 'ideas'" class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('ideas.col.idea') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('ideas.col.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('ideas.col.status') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('archive.deleted_at') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="items.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400">{{ t('archive.empty') }}</td>
                        </tr>
                        <tr v-for="item in items.data" :key="(item.id as number)" class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ item.display_id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ item.title }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="item.unit" :color="(item.unit as Record<string, string>).color">{{ (item.unit as Record<string, string>).name }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ item.status }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ formatDate(item.deleted_at as string) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Risks table -->
                <table v-else-if="tab === 'risks'" class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.field.type') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('risks.col.owner') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('archive.deleted_at') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="items.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-400">{{ t('archive.empty') }}</td>
                        </tr>
                        <tr v-for="item in items.data" :key="(item.id as number)" class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ item.display_id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-900">{{ item.name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ item.type }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ item.owner_name }}</td>
                            <td class="px-4 py-3 text-xs text-slate-400">{{ formatDate(item.deleted_at as string) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Employees table -->
                <table v-else class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.name') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.position') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.department') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="items.data.length === 0">
                            <td colspan="4" class="px-4 py-12 text-center text-sm text-slate-400">{{ t('archive.empty') }}</td>
                        </tr>
                        <tr v-for="item in items.data" :key="(item.id as number)" class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ item.name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ item.position }}</td>
                            <td class="px-4 py-3 text-sm">
                                <Badge v-if="item.unit" :color="(item.unit as Record<string, string>).color">{{ (item.unit as Record<string, string>).name }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ item.department }}</td>
                        </tr>
                    </tbody>
                </table>

                <Pagination :links="items.links" :from="items.from" :to="items.to" :total="items.total" />
            </div>
        </div>
    </AppLayout>
</template>
