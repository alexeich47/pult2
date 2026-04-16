<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Employee, RndPriority, RndProject, RndStatus, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';

interface Props {
    show: boolean;
    project: RndProject | null;
    units: Unit[];
    employees: Employee[];
    statuses: RndStatus[];
    priorities: RndPriority[];
    currencies: string[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const form = useForm({
    unit_id: '' as string | null,
    title: '',
    description: '' as string | null,
    owner_id: 0,
    priority: 'medium' as RndPriority,
    status: 'idea' as RndStatus,
    budget: null as number | null,
    currency: 'USD',
    deadline: '' as string | null,
    success_criteria: '',
    kill_criteria: '',
    started_at: '' as string | null,
});

const isEdit = computed(() => props.project !== null);
const headerTitle = computed(() => (isEdit.value ? t('rnd.col.title') : t('rnd.add')));

watch(
    () => [props.show, props.project] as const,
    ([show, project]) => {
        if (!show) return;
        if (project) {
            form.unit_id = project.unit_id;
            form.title = project.title;
            form.description = project.description ?? '';
            form.owner_id = project.owner_id;
            form.priority = project.priority;
            form.status = project.status;
            form.budget = project.budget ? parseFloat(project.budget) : null;
            form.currency = project.currency;
            form.deadline = project.deadline?.slice(0, 10) ?? '';
            form.success_criteria = project.success_criteria;
            form.kill_criteria = project.kill_criteria;
            form.started_at = project.started_at?.slice(0, 10) ?? '';
        } else {
            form.reset();
            form.unit_id = props.units[0]?.id ?? null;
            form.owner_id = props.employees[0]?.id ?? 0;
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
    if (isEdit.value && props.project) {
        form.put(`/rnd/${props.project.id}`, opts);
    } else {
        form.post('/rnd', opts);
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
                    <h2 class="text-lg font-semibold text-slate-900">{{ headerTitle }}</h2>
                    <button type="button" class="text-slate-400 hover:text-slate-600" @click="emit('close')">✕</button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 px-5 py-4">
                    <!-- Title -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.title') }} <span class="text-rose-500">*</span></label>
                        <input v-model="form.title" type="text" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.title" class="mt-1 text-xs text-rose-600">{{ form.errors.title }}</div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.description') }}</label>
                        <textarea v-model="form.description" rows="2" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <!-- Unit + Owner -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.unit') }}</label>
                            <select v-model="form.unit_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option :value="null">—</option>
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.owner') }} <span class="text-rose-500">*</span></label>
                            <select v-model="form.owner_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name ?? e.position }}</option>
                            </select>
                            <div v-if="form.errors.owner_id" class="mt-1 text-xs text-rose-600">{{ form.errors.owner_id }}</div>
                        </div>
                    </div>

                    <!-- Priority + Status -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.priority') }}</label>
                            <select v-model="form.priority" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="p in priorities" :key="p" :value="p">{{ t(`rnd.priority.${p}`) }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.status') }}</label>
                            <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="s in statuses" :key="s" :value="s">{{ t(`rnd.status.${s}`) }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Budget + Currency -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.budget') }}</label>
                            <input v-model.number="form.budget" type="number" min="0" step="0.01" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <div v-if="form.errors.budget" class="mt-1 text-xs text-rose-600">{{ form.errors.budget }}</div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.currency') }}</label>
                            <select v-model="form.currency" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                    </div>

                    <!-- Deadline + Started at -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.deadline') }}</label>
                            <input v-model="form.deadline" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.started_at') }}</label>
                            <input v-model="form.started_at" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <!-- Success criteria -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.success_criteria') }} <span class="text-rose-500">*</span></label>
                        <textarea v-model="form.success_criteria" rows="3" :placeholder="t('rnd.field.success_criteria_ph')" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.success_criteria" class="mt-1 text-xs text-rose-600">{{ form.errors.success_criteria }}</div>
                    </div>

                    <!-- Kill criteria -->
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('rnd.field.kill_criteria') }} <span class="text-rose-500">*</span></label>
                        <textarea v-model="form.kill_criteria" rows="3" :placeholder="t('rnd.field.kill_criteria_ph')" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.kill_criteria" class="mt-1 text-xs text-rose-600">{{ form.errors.kill_criteria }}</div>
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
