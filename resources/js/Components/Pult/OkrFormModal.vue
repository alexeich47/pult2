<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import type { OkrEntry, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';
import SearchableSelect from './SearchableSelect.vue';

interface Props {
    show: boolean;
    entry: OkrEntry | null;
    units: Unit[];
    types: string[];
    statuses: string[];
    parentId?: number | null;
}

const props = withDefaults(defineProps<Props>(), { parentId: null });
const emit = defineEmits<{ close: [] }>();
const { t } = useTranslations();

const form = reactive({
    unit_id: '' as string | null,
    type: 'objective',
    title: '',
    description: '',
    period: 'Q2 2026',
    progress: 0,
    status: 'active',
    parent_id: null as number | null,
    sort_order: 0,
    processing: false,
    errors: {} as Record<string, string>,
});

const isEdit = computed(() => props.entry !== null);

watch(
    () => [props.show, props.entry] as const,
    ([show, entry]) => {
        if (!show) return;
        if (entry) {
            form.unit_id = entry.unit_id;
            form.type = entry.type;
            form.title = entry.title;
            form.description = entry.description ?? '';
            form.period = entry.period;
            form.progress = entry.progress;
            form.status = entry.status;
            form.parent_id = entry.parent_id;
            form.sort_order = entry.sort_order;
        } else {
            form.unit_id = null;
            form.type = props.parentId ? 'key_result' : 'objective';
            form.title = '';
            form.description = '';
            form.period = 'Q2 2026';
            form.progress = 0;
            form.status = 'active';
            form.parent_id = props.parentId;
            form.sort_order = 0;
        }
        form.errors = {};
    },
    { immediate: true },
);

function submit() {
    const data = {
        unit_id: form.unit_id,
        type: form.type,
        title: form.title,
        description: form.description,
        period: form.period,
        progress: form.progress,
        status: form.status,
        parent_id: form.parent_id,
        sort_order: form.sort_order,
    };

    form.processing = true;
    const opts = {
        preserveScroll: true,
        onSuccess: () => { form.processing = false; emit('close'); },
        onError: (errors: Record<string, string>) => { form.processing = false; form.errors = errors; },
    };

    if (isEdit.value && props.entry) {
        router.put(`/okr/${props.entry.id}`, data, opts);
    } else {
        router.post('/okr', data, opts);
    }
}

const typeOptions = computed(() => props.types.map(tp => ({ value: tp, label: t(`okr.type.${tp}`) })));
const unitOptions = computed(() => [
    { value: null, label: t('okr.field.holding_wide') },
    ...props.units.map(u => ({ value: u.id, label: u.name, color: u.color })),
]);
const statusOptions = computed(() => props.statuses.map(s => ({ value: s, label: t(`okr.status.${s}`) })));
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
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ isEdit ? t('okr.btn.edit') : t('okr.btn.add') }}
                    </h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">&#10005;</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 px-5 py-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.title') }} <span class="text-rose-500">*</span></label>
                        <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.type') }}</label>
                            <SearchableSelect v-model="form.type" :options="typeOptions" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.company') }}</label>
                            <SearchableSelect v-model="form.unit_id" :options="unitOptions" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.description') }}</label>
                        <textarea v-model="form.description" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.period') }}</label>
                            <input v-model="form.period" type="text" placeholder="Q2 2026" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.status') }}</label>
                            <SearchableSelect v-model="form.status" :options="statusOptions" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('okr.field.progress') }}: {{ form.progress }}%</label>
                        <input v-model.number="form.progress" type="range" min="0" max="100" class="w-full" />
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
