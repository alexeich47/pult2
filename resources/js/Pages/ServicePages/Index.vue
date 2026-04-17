<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { PageProps } from '../../types';

interface AllowedEmployee { id: number; name: string | null; position: string; email: string | null }
interface AllEmployee { id: number; name: string | null; position: string; email: string | null; unit_id: string | null }

interface ServicePage {
    slug: string;
    title: string;
    description: string;
    icon: string;
    unit_id: string | null;
    employee_ids: number[];
    allowed_employees: AllowedEmployee[];
}

const props = defineProps<{
    pages: ServicePage[];
    canEditAccess: boolean;
    allEmployees: AllEmployee[];
}>();

function pageHref(p: ServicePage): string {
    return p.unit_id ? `/service-pages/daily/${p.unit_id}` : `/service-pages/${p.slug}`;
}

const { t } = useTranslations();
const page = usePage<PageProps>();

const userEmail = page.props.auth?.user?.email ?? '';
const userIsSuperAdmin = (page.props.auth?.user?.roles ?? []).includes('super-admin');

function canAccess(p: ServicePage): boolean {
    if (userIsSuperAdmin) return true;
    return p.allowed_employees.some(e => e.email === userEmail);
}

function employeeLabel(e: { name: string | null; position: string }): string {
    return e.name ?? e.position;
}

// ── Access editor modal ───────────────────────────────────────────
const editingPage = ref<ServicePage | null>(null);
const editForm = useForm<{ employee_ids: number[] }>({ employee_ids: [] });
const filterText = ref('');

function openEditAccess(p: ServicePage) {
    editingPage.value = p;
    editForm.employee_ids = [...p.employee_ids];
    filterText.value = '';
}

function closeEditAccess() {
    editingPage.value = null;
    filterText.value = '';
}

const selectedSet = computed(() => new Set(editForm.employee_ids));

function toggleEmployee(id: number) {
    if (selectedSet.value.has(id)) {
        editForm.employee_ids = editForm.employee_ids.filter(x => x !== id);
    } else {
        editForm.employee_ids = [...editForm.employee_ids, id];
    }
}

const filteredEmployees = computed(() => {
    const q = filterText.value.trim().toLowerCase();
    if (!q) return props.allEmployees;
    return props.allEmployees.filter(e => {
        const haystack = [e.name ?? '', e.position, e.email ?? '', e.unit_id ?? ''].join(' ').toLowerCase();
        return haystack.includes(q);
    });
});

function saveAccess() {
    if (!editingPage.value) return;
    const slug = editingPage.value.slug;
    editForm.put(`/service-pages/${slug}/access`, {
        preserveScroll: true,
        onSuccess: () => closeEditAccess(),
    });
}
</script>

<template>
    <Head :title="t('service_pages.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ t('service_pages.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ t('service_pages.sub') }}</p>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="px-5 py-3">{{ t('service_pages.col.page') }}</th>
                            <th class="px-5 py-3">{{ t('service_pages.col.description') }}</th>
                            <th class="px-5 py-3">{{ t('service_pages.col.access') }}</th>
                            <th class="w-32 px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="p in pages" :key="p.slug" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-lg">{{ p.icon }}</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ p.title }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-slate-600 dark:text-slate-400">
                                {{ p.description }}
                            </td>
                            <td class="px-5 py-3">
                                <div v-if="p.allowed_employees.length === 0" class="text-xs italic text-slate-400">
                                    {{ t('service_pages.no_one_assigned') }}
                                </div>
                                <div v-else class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="e in p.allowed_employees"
                                        :key="e.id"
                                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-300"
                                        :title="e.email ?? ''"
                                    >
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        {{ employeeLabel(e) }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button
                                        v-if="canEditAccess"
                                        type="button"
                                        class="text-slate-400 hover:text-indigo-600"
                                        :title="t('service_pages.access.edit_btn')"
                                        @click="openEditAccess(p)"
                                    >
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="3"/>
                                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                                        </svg>
                                    </button>
                                    <Link
                                        v-if="canAccess(p)"
                                        :href="pageHref(p)"
                                        class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                                    >
                                        {{ t('service_pages.open') }} <span>&rarr;</span>
                                    </Link>
                                    <span v-else class="text-xs text-slate-400">{{ t('service_pages.no_access') }}</span>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="pages.length === 0">
                            <td colspan="4" class="px-5 py-12 text-center text-sm text-slate-400">{{ t('service_pages.empty') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Access editor modal -->
        <div
            v-if="editingPage"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"
            @click.self="closeEditAccess"
        >
            <div class="w-full max-w-2xl rounded-xl bg-white shadow-2xl dark:bg-slate-800">
                <div class="flex items-start justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ t('service_pages.access.modal_title') }}
                        </h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            <span class="mr-1">{{ editingPage.icon }}</span>{{ editingPage.title }}
                        </p>
                    </div>
                    <button class="text-slate-400 hover:text-slate-600" @click="closeEditAccess">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <div class="px-6 py-4">
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <input
                            v-model="filterText"
                            type="text"
                            :placeholder="t('service_pages.access.filter_placeholder')"
                            class="w-full rounded-md border border-slate-300 px-3 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                        />
                        <span class="shrink-0 text-xs text-slate-500">
                            {{ editForm.employee_ids.length }} {{ t('service_pages.access.selected') }}
                        </span>
                    </div>

                    <div class="max-h-[420px] overflow-y-auto rounded-md border border-slate-200 dark:border-slate-700">
                        <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                            <li
                                v-for="e in filteredEmployees"
                                :key="e.id"
                                class="flex cursor-pointer items-center gap-3 px-3 py-2 hover:bg-slate-50 dark:hover:bg-slate-700/30"
                                @click="toggleEmployee(e.id)"
                            >
                                <input
                                    type="checkbox"
                                    :checked="selectedSet.has(e.id)"
                                    class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
                                    @click.stop="toggleEmployee(e.id)"
                                />
                                <div class="flex-1 text-sm">
                                    <div class="font-medium text-slate-800 dark:text-slate-200">{{ e.name ?? '—' }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ e.position }}
                                        <span v-if="e.unit_id" class="ml-1 text-slate-400">· {{ e.unit_id }}</span>
                                    </div>
                                </div>
                                <div class="text-[11px] text-slate-400">{{ e.email }}</div>
                            </li>
                            <li v-if="filteredEmployees.length === 0" class="px-3 py-6 text-center text-sm text-slate-400">
                                {{ t('service_pages.access.no_results') }}
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-3 dark:border-slate-700">
                    <button
                        type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700"
                        @click="closeEditAccess"
                    >
                        {{ t('service_pages.access.cancel') }}
                    </button>
                    <button
                        type="button"
                        :disabled="editForm.processing"
                        class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                        @click="saveAccess"
                    >
                        {{ t('service_pages.access.save') }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
