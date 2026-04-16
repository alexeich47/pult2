<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Meeting, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';

interface Props {
    show: boolean;
    meeting: Meeting | null;
    units: Unit[];
    types: string[];
    statuses: string[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();
const { t } = useTranslations();

const form = useForm({
    unit_id: '' as string | null,
    title: '',
    type: 'weekly',
    scheduled_at: '',
    duration_minutes: 30,
    location: '',
    agenda: '',
    notes: '',
    status: 'scheduled',
});

const isEdit = computed(() => props.meeting !== null);

watch(
    () => [props.show, props.meeting] as const,
    ([show, meeting]) => {
        if (!show) return;
        if (meeting) {
            form.unit_id = meeting.unit_id;
            form.title = meeting.title;
            form.type = meeting.type;
            form.scheduled_at = meeting.scheduled_at?.slice(0, 16) ?? '';
            form.duration_minutes = meeting.duration_minutes;
            form.location = meeting.location ?? '';
            form.agenda = meeting.agenda ?? '';
            form.notes = meeting.notes ?? '';
            form.status = meeting.status;
        } else {
            form.reset();
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
    if (isEdit.value && props.meeting) {
        form.put(`/meetings/${props.meeting.id}`, opts);
    } else {
        form.post('/meetings', opts);
    }
}
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
                        {{ isEdit ? t('meetings.btn.edit') : t('meetings.btn.add') }}
                    </h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">&#10005;</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 px-5 py-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.title') }} <span class="text-rose-500">*</span></label>
                        <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.type') }}</label>
                            <select v-model="form.type" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="tp in types" :key="tp" :value="tp">{{ t(`meetings.type.${tp}`) }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.company') }}</label>
                            <select v-model="form.unit_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option :value="null">{{ t('meetings.field.holding_wide') }}</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.scheduled_at') }} <span class="text-rose-500">*</span></label>
                            <input v-model="form.scheduled_at" type="datetime-local" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <div v-if="form.errors.scheduled_at" class="mt-1 text-xs text-rose-600">{{ form.errors.scheduled_at }}</div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.duration') }}</label>
                            <input v-model.number="form.duration_minutes" type="number" min="5" max="480" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.location') }}</label>
                            <input v-model="form.location" type="text" placeholder="Zoom / Office / URL" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.status') }}</label>
                            <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="s in statuses" :key="s" :value="s">{{ t(`meetings.status.${s}`) }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.agenda') }}</label>
                        <textarea v-model="form.agenda" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('meetings.field.notes') }}</label>
                        <textarea v-model="form.notes" rows="3" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
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
