<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import type { Instruction, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';
import SearchableSelect from './SearchableSelect.vue';

interface Props {
    show: boolean;
    instruction: Instruction | null;
    units: Unit[];
    types: string[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();
const { t } = useTranslations();

const form = useForm({
    unit_id: '' as string | null,
    title: '',
    type: 'instruction',
    content: '',
    checklist_items: [] as { text: string; checked: boolean }[],
});

const isEdit = computed(() => props.instruction !== null);

watch(
    () => [props.show, props.instruction] as const,
    ([show, instr]) => {
        if (!show) return;
        if (instr) {
            form.unit_id = instr.unit_id;
            form.title = instr.title;
            form.type = instr.type;
            form.content = instr.content ?? '';
            form.checklist_items = instr.checklist_items ?? [];
        } else {
            form.reset();
            form.checklist_items = [];
        }
        form.clearErrors();
    },
    { immediate: true },
);

function addChecklistItem() {
    form.checklist_items.push({ text: '', checked: false });
}

function removeChecklistItem(idx: number) {
    form.checklist_items.splice(idx, 1);
}

function submit() {
    const opts = {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    };
    if (isEdit.value && props.instruction) {
        form.put(`/instructions/${props.instruction.id}`, opts);
    } else {
        form.post('/instructions', opts);
    }
}

const typeOptions = computed(() => props.types.map(tp => ({ value: tp, label: t(`instructions.type.${tp}`) })));
const unitOptions = computed(() => [
    { value: null, label: t('instructions.field.holding_wide') },
    ...props.units.map(u => ({ value: u.id, label: u.name, color: u.color })),
]);
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
                        {{ isEdit ? t('instructions.btn.edit') : t('instructions.btn.add') }}
                    </h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">&#10005;</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 px-5 py-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('instructions.field.title') }} <span class="text-rose-500">*</span></label>
                        <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('instructions.field.type') }}</label>
                            <SearchableSelect v-model="form.type" :options="typeOptions" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('instructions.field.company') }}</label>
                            <SearchableSelect v-model="form.unit_id" :options="unitOptions" />
                        </div>
                    </div>

                    <!-- Content for instructions -->
                    <div v-if="form.type === 'instruction'">
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('instructions.field.content') }}</label>
                        <textarea v-model="form.content" rows="8" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <!-- Checklist builder for checklists -->
                    <div v-if="form.type === 'checklist'">
                        <label class="mb-2 block text-xs font-medium text-slate-700">{{ t('instructions.field.checklist') }}</label>
                        <div v-for="(item, idx) in form.checklist_items" :key="idx" class="mb-2 flex items-center gap-2">
                            <input v-model="item.checked" type="checkbox" class="rounded border-slate-300" />
                            <input v-model="item.text" type="text" class="flex-1 rounded-md border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <button type="button" class="text-xs text-rose-500 hover:text-rose-700" @click="removeChecklistItem(idx)">&#10005;</button>
                        </div>
                        <button type="button" class="text-xs text-indigo-600 hover:text-indigo-800" @click="addChecklistItem">+ {{ t('instructions.field.add_item') }}</button>
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
