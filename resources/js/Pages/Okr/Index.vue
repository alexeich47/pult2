<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import OkrFormModal from '../../Components/Pult/OkrFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Paginated, OkrEntry, Unit } from '../../types';

interface Props {
    entries: Paginated<OkrEntry>;
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

const STATUS_COLORS: Record<string, string> = {
    active: '#22c55e',
    completed: '#3b82f6',
    cancelled: '#94a3b8',
};

const showModal = ref(false);
const editing = ref<OkrEntry | null>(null);
const parentIdForNew = ref<number | null>(null);
const expanded = ref<Set<number>>(new Set());

function openCreate(parentId: number | null = null) {
    editing.value = null;
    parentIdForNew.value = parentId;
    showModal.value = true;
}

function openEdit(entry: OkrEntry) {
    editing.value = entry;
    parentIdForNew.value = null;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
    parentIdForNew.value = null;
}

function toggleExpand(id: number) {
    if (expanded.value.has(id)) {
        expanded.value.delete(id);
    } else {
        expanded.value.add(id);
    }
}

function destroy(entry: OkrEntry) {
    if (!confirm(t('okr.delete_confirm'))) return;
    router.delete(`/okr/${entry.id}`, { preserveScroll: true });
}

function progressColor(progress: number): string {
    if (progress >= 70) return '#22c55e';
    if (progress >= 40) return '#f59e0b';
    return '#ef4444';
}

const northStars = () => props.entries.data.filter((e) => e.type === 'north_star');
const objectives = () => props.entries.data.filter((e) => e.type === 'objective');
</script>

<template>
    <Head :title="t('okr.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('okr.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('okr.subtitle') }}</p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate()"
                >
                    + {{ t('okr.btn.add') }}
                </button>
            </div>

            <!-- North Star section -->
            <div v-for="ns in northStars()" :key="ns.id" class="mb-6 rounded-xl border-2 border-amber-300 bg-gradient-to-r from-amber-50 to-yellow-50 p-6 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="mb-1 text-xs font-bold uppercase tracking-wider text-amber-600">{{ t('okr.north_star') }}</div>
                        <h2 class="text-xl font-bold text-slate-900">{{ ns.title }}</h2>
                        <p v-if="ns.description" class="mt-1 text-sm text-slate-600">{{ ns.description }}</p>
                        <div class="mt-2 flex items-center gap-3">
                            <Badge :color="STATUS_COLORS[ns.status]">{{ t(`okr.status.${ns.status}`) }}</Badge>
                            <span class="text-xs text-slate-500">{{ ns.period }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button v-if="can.create" type="button" class="text-xs text-indigo-600 hover:text-indigo-800" @click="openEdit(ns)">{{ t('okr.btn.edit') }}</button>
                        <button v-if="can.delete" type="button" class="text-xs text-rose-500 hover:text-rose-700" @click="destroy(ns)">&#10005;</button>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-700">{{ t('okr.progress') }}</span>
                        <span class="font-mono text-sm font-bold" :style="{ color: progressColor(ns.progress) }">{{ ns.progress }}%</span>
                    </div>
                    <div class="mt-1 h-3 w-full overflow-hidden rounded-full bg-slate-200">
                        <div
                            class="h-full rounded-full transition-all"
                            :style="{ width: `${ns.progress}%`, backgroundColor: progressColor(ns.progress) }"
                        />
                    </div>
                </div>
            </div>

            <!-- Objectives -->
            <div class="space-y-3">
                <div
                    v-for="obj in objectives()"
                    :key="obj.id"
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
                >
                    <div
                        class="flex cursor-pointer items-center justify-between px-5 py-4 hover:bg-slate-50"
                        @click="toggleExpand(obj.id)"
                    >
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-semibold text-slate-900">{{ obj.title }}</span>
                                <Badge :color="STATUS_COLORS[obj.status]">{{ t(`okr.status.${obj.status}`) }}</Badge>
                                <span class="text-xs text-slate-500">{{ obj.period }}</span>
                            </div>
                            <div class="mt-2 flex items-center gap-2">
                                <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-200">
                                    <div
                                        class="h-full rounded-full transition-all"
                                        :style="{ width: `${obj.progress}%`, backgroundColor: progressColor(obj.progress) }"
                                    />
                                </div>
                                <span class="text-xs font-mono" :style="{ color: progressColor(obj.progress) }">{{ obj.progress }}%</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-400">{{ obj.children?.length ?? 0 }} KR</span>
                            <button v-if="can.create" type="button" class="text-xs text-indigo-600 hover:text-indigo-800" @click.stop="openEdit(obj)">{{ t('okr.btn.edit') }}</button>
                            <button v-if="can.delete" type="button" class="text-xs text-rose-500 hover:text-rose-700" @click.stop="destroy(obj)">&#10005;</button>
                        </div>
                    </div>

                    <!-- Key Results -->
                    <div v-if="expanded.has(obj.id) && obj.children?.length" class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                        <div class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('okr.key_results') }}</div>
                        <div v-for="kr in obj.children" :key="kr.id" class="mb-2 flex items-center justify-between rounded-md bg-white px-4 py-2.5 border border-slate-100">
                            <div class="flex-1">
                                <span class="text-sm text-slate-800">{{ kr.title }}</span>
                                <div class="mt-1 flex items-center gap-2">
                                    <div class="h-1.5 w-24 overflow-hidden rounded-full bg-slate-200">
                                        <div
                                            class="h-full rounded-full"
                                            :style="{ width: `${kr.progress}%`, backgroundColor: progressColor(kr.progress) }"
                                        />
                                    </div>
                                    <span class="text-xs font-mono" :style="{ color: progressColor(kr.progress) }">{{ kr.progress }}%</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button v-if="can.create" type="button" class="text-xs text-indigo-600 hover:text-indigo-800" @click="openEdit(kr)">{{ t('okr.btn.edit') }}</button>
                                <button v-if="can.delete" type="button" class="text-xs text-rose-500 hover:text-rose-700" @click="destroy(kr)">&#10005;</button>
                            </div>
                        </div>
                        <button
                            v-if="can.create"
                            type="button"
                            class="mt-1 text-xs text-indigo-600 hover:text-indigo-800"
                            @click="openCreate(obj.id)"
                        >
                            + {{ t('okr.btn.add_kr') }}
                        </button>
                    </div>
                    <div v-else-if="expanded.has(obj.id)" class="border-t border-slate-100 bg-slate-50 px-5 py-3">
                        <p class="text-sm text-slate-500">{{ t('okr.empty_kr') }}</p>
                        <button
                            v-if="can.create"
                            type="button"
                            class="mt-1 text-xs text-indigo-600 hover:text-indigo-800"
                            @click="openCreate(obj.id)"
                        >
                            + {{ t('okr.btn.add_kr') }}
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="entries.data.length === 0" class="mt-8 text-center">
                <div class="text-4xl">&#127919;</div>
                <div class="mt-2 text-sm text-slate-500">{{ t('okr.empty') }}</div>
            </div>

            <Pagination
                :links="entries.links"
                :from="entries.from"
                :to="entries.to"
                :total="entries.total"
            />
        </div>

        <OkrFormModal
            :show="showModal"
            :entry="editing"
            :units="allUnits"
            :types="types"
            :statuses="statuses"
            :parent-id="parentIdForNew"
            @close="closeModal"
        />
    </AppLayout>
</template>
