<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Employee, Process, ProcessMaturity, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';
import SearchableSelect from './SearchableSelect.vue';

interface Props {
    show: boolean;
    process: Process | null;
    units: Unit[];
    employees: Employee[];
    categories: string[];
    maturityLevels: ProcessMaturity[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const form = useForm({
    unit_id: '' as string | null,
    title: '',
    description: '' as string | null,
    owner_id: null as number | null,
    category: '',
    maturity: 'not_documented' as ProcessMaturity,
    document_url: '' as string | null,
    tool: '' as string | null,
    sort_order: 0,
});

const isEdit = computed(() => props.process !== null);
const headerTitle = computed(() => (isEdit.value ? t('processes.col.title') : t('processes.add')));

watch(
    () => [props.show, props.process] as const,
    ([show, process]) => {
        if (!show) return;
        if (process) {
            form.unit_id = process.unit_id;
            form.title = process.title;
            form.description = process.description ?? '';
            form.owner_id = process.owner_id;
            form.category = process.category;
            form.maturity = process.maturity;
            form.document_url = process.document_url ?? '';
            form.tool = process.tool ?? '';
            form.sort_order = process.sort_order;
        } else {
            form.reset();
            form.unit_id = props.units[0]?.id ?? null;
            form.owner_id = props.employees[0]?.id ?? null;
            form.category = props.categories[0] ?? '';
        }
        form.clearErrors();
    },
    { immediate: true },
);

function submit() {
    const opts = {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    };
    if (isEdit.value && props.process) {
        form.put(`/processes/${props.process.id}`, opts);
    } else {
        form.post('/processes', opts);
    }
}

const unitOptions = computed(() => [
    { value: null, label: '---' },
    ...props.units.map(u => ({ value: u.id, label: u.name, color: u.color })),
]);
const ownerOptions = computed(() => [
    { value: null, label: '---' },
    ...props.employees.map(e => ({ value: e.id, label: e.name ?? e.position })),
]);
const categoryOptions = computed(() => props.categories.map(c => ({ value: c, label: c })));
const maturityOptions = computed(() => props.maturityLevels.map(m => ({ value: m, label: t(`processes.maturity.${m}`) })));
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="emit('close')"
        >
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">{{ headerTitle }}</h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">&#x2715;</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 px-5 py-4">
                    <!-- Title -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.title') }} <span class="text-rose-500">*</span></label>
                        <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.description') }}</label>
                        <textarea v-model="form.description" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <!-- Unit + Owner -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.unit') }}</label>
                            <SearchableSelect v-model="form.unit_id" :options="unitOptions" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.owner') }}</label>
                            <SearchableSelect v-model="form.owner_id" :options="ownerOptions" />
                        </div>
                    </div>

                    <!-- Category + Maturity -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.category') }} <span class="text-rose-500">*</span></label>
                            <SearchableSelect v-model="form.category" :options="categoryOptions" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.maturity') }} <span class="text-rose-500">*</span></label>
                            <SearchableSelect v-model="form.maturity" :options="maturityOptions" />
                        </div>
                    </div>

                    <!-- Document URL -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.document_url') }}</label>
                        <input v-model="form.document_url" type="text" :placeholder="t('processes.field.document_url_ph')" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.document_url" class="mt-1 text-xs text-rose-600">{{ form.errors.document_url }}</div>
                    </div>

                    <!-- Tool -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('processes.field.tool') }}</label>
                        <input v-model="form.tool" type="text" :placeholder="t('processes.field.tool_ph')" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
                        <button type="button" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="emit('close')">{{ t('modal.btn.cancel') }}</button>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">{{ t('modal.btn.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
