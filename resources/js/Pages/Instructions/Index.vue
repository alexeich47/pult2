<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import Pagination from '../../Components/Pult/Pagination.vue';
import InstructionFormModal from '../../Components/Pult/InstructionFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Paginated, Instruction, Unit } from '../../types';

interface Props {
    instructions: Paginated<Instruction>;
    allUnits: Unit[];
    types: string[];
    filters: Partial<Record<string, string>>;
    can: {
        create: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

type Tab = 'instruction' | 'checklist';
const activeTab = computed<Tab>(() => {
    return (props.filters.type as Tab) ?? 'instruction';
});

function switchTab(tab: Tab) {
    const filters: Record<string, string> = { type: tab };
    router.get('/instructions', { filter: filters }, { preserveScroll: true, preserveState: true });
}

const showModal = ref(false);
const editing = ref<Instruction | null>(null);
const expanded = ref<Set<number>>(new Set());

function openCreate() {
    editing.value = null;
    showModal.value = true;
}

function openEdit(instr: Instruction) {
    editing.value = instr;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    editing.value = null;
}

function toggleExpand(id: number) {
    if (expanded.value.has(id)) {
        expanded.value.delete(id);
    } else {
        expanded.value.add(id);
    }
}

function destroy(instr: Instruction) {
    if (!confirm(t('instructions.delete_confirm'))) return;
    router.delete(`/instructions/${instr.id}`, { preserveScroll: true });
}

function unitFor(instr: Instruction): Unit | undefined {
    return props.allUnits.find((u) => u.id === instr.unit_id);
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}

const TABS: { id: Tab; key: string }[] = [
    { id: 'instruction', key: 'instructions.type.instruction' },
    { id: 'checklist', key: 'instructions.type.checklist' },
];
</script>

<template>
    <Head :title="t('instructions.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('instructions.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('instructions.subtitle') }}</p>
                </div>
                <button
                    v-if="can.create"
                    type="button"
                    class="shrink-0 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    @click="openCreate"
                >
                    + {{ t('instructions.btn.add') }}
                </button>
            </div>

            <!-- Tabs -->
            <div class="mb-4 flex gap-2">
                <button
                    v-for="tab in TABS"
                    :key="tab.id"
                    type="button"
                    :class="[
                        'rounded-full border px-4 py-1.5 text-xs font-medium transition-colors',
                        activeTab === tab.id
                            ? 'border-indigo-500 bg-indigo-50 text-indigo-700'
                            : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50',
                    ]"
                    @click="switchTab(tab.id)"
                >
                    {{ t(tab.key) }}
                </button>
            </div>

            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('instructions.col.title') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('instructions.col.company') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('instructions.col.author') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('instructions.col.date') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-600">{{ t('table.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <tr v-if="instructions.data.length === 0">
                            <td colspan="5" class="px-4 py-12 text-center">
                                <div class="text-4xl">&#128196;</div>
                                <div class="mt-2 text-sm text-slate-500">{{ t('instructions.empty') }}</div>
                            </td>
                        </tr>
                        <template v-for="instr in instructions.data" :key="instr.id">
                            <tr
                                class="cursor-pointer hover:bg-slate-50"
                                @click="toggleExpand(instr.id)"
                            >
                                <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ instr.title }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <Badge v-if="unitFor(instr)" :color="unitFor(instr)!.color">{{ unitFor(instr)!.name }}</Badge>
                                    <span v-else class="text-xs text-slate-500">{{ t('instructions.field.holding_wide') }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-700">{{ instr.creator?.name ?? '---' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ formatDate(instr.created_at) }}</td>
                                <td class="px-4 py-3 text-right">
                                    <button
                                        v-if="can.create"
                                        type="button"
                                        class="mr-2 text-xs text-indigo-600 hover:text-indigo-800"
                                        @click.stop="openEdit(instr)"
                                    >
                                        {{ t('instructions.btn.edit') }}
                                    </button>
                                    <button
                                        v-if="can.delete"
                                        type="button"
                                        class="text-xs text-rose-500 hover:text-rose-700"
                                        @click.stop="destroy(instr)"
                                    >
                                        &#10005;
                                    </button>
                                </td>
                            </tr>
                            <!-- Expanded content -->
                            <tr v-if="expanded.has(instr.id)">
                                <td colspan="5" class="bg-slate-50 px-6 py-4">
                                    <div v-if="instr.type === 'instruction' && instr.content" class="whitespace-pre-wrap text-sm text-slate-700">{{ instr.content }}</div>
                                    <div v-if="instr.type === 'checklist' && instr.checklist_items" class="space-y-1">
                                        <div v-for="(item, idx) in instr.checklist_items" :key="idx" class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" :checked="item.checked" disabled class="rounded border-slate-300" />
                                            <span :class="item.checked ? 'text-slate-400 line-through' : 'text-slate-700'">{{ item.text }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <Pagination
                    :links="instructions.links"
                    :from="instructions.from"
                    :to="instructions.to"
                    :total="instructions.total"
                />
            </div>
        </div>

        <InstructionFormModal
            :show="showModal"
            :instruction="editing"
            :units="allUnits"
            :types="types"
            @close="closeModal"
        />
    </AppLayout>
</template>
