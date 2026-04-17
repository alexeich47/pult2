<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Employee, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';
import SearchableSelect from './SearchableSelect.vue';

interface Props {
    show: boolean;
    employee: Employee | null;
    units: Unit[];
    managers: Employee[];
    departments: string[];
    statuses: string[];
    workStages?: string[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const form = useForm({
    unit_id: '',
    manager_id: null as number | null,
    name: '',
    position: '',
    department: '',
    email: '',
    telegram: '',
    status: 'active',
    work_stage: 'employee',
});

const isEdit = computed(() => props.employee !== null);
const title = computed(() =>
    isEdit.value ? t('personnel.edit') : t('personnel.add'),
);

watch(
    () => [props.show, props.employee] as const,
    ([show, employee]) => {
        if (!show) return;
        if (employee) {
            form.unit_id = employee.unit_id;
            form.manager_id = (employee as Employee & { manager_id?: number | null }).manager_id ?? null;
            form.name = employee.name ?? '';
            form.position = employee.position;
            form.department = employee.department;
            form.email = employee.email ?? '';
            form.telegram = employee.telegram ?? '';
            form.status = employee.status;
            form.work_stage = employee.work_stage ?? 'employee';
        } else {
            form.reset();
            form.unit_id = props.units[0]?.id ?? '';
            form.manager_id = null;
            form.department = props.departments[0] ?? '';
            form.status = 'active';
            form.work_stage = 'employee';
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
    if (isEdit.value && props.employee) {
        form.put(`/personnel/${props.employee.id}`, opts);
    } else {
        form.post('/personnel', opts);
    }
}

const isVacancy = computed(() => form.status === 'vacancy');
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="emit('close')"
        >
            <div class="w-full max-w-lg rounded-xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ title }}
                    </h2>
                    <button
                        type="button"
                        class="text-slate-400 hover:text-slate-600"
                        @click="emit('close')"
                    >
                        ✕
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-4 px-5 py-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('modal.field.status') }}
                        </label>
                        <SearchableSelect
                            v-model="form.status"
                            :options="statuses.map(s => ({ value: s, label: t(`status.${s}`) }))"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('modal.field.name') }}
                            <span v-if="!isVacancy" class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            :disabled="isVacancy"
                            :placeholder="t('modal.field.name_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-400"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('modal.field.position') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.position"
                            type="text"
                            :placeholder="t('modal.field.position_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.position" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.position }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('modal.field.company') }} <span class="text-rose-500">*</span>
                            </label>
                            <SearchableSelect
                                v-model="form.unit_id"
                                :options="units.map(u => ({ value: u.id, label: u.name, color: u.color }))"
                            />
                            <div v-if="form.errors.unit_id" class="mt-1 text-xs text-rose-600">
                                {{ form.errors.unit_id }}
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('modal.field.department') }} <span class="text-rose-500">*</span>
                            </label>
                            <SearchableSelect
                                v-model="form.department"
                                :options="departments.map(d => ({ value: d, label: d }))"
                            />
                            <div v-if="form.errors.department" class="mt-1 text-xs text-rose-600">
                                {{ form.errors.department }}
                            </div>
                        </div>
                    </div>

                    <div v-if="workStages?.length" class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('table.manager') }}
                            </label>
                            <SearchableSelect
                                v-model="form.manager_id"
                                :options="[{ value: null, label: '—' }, ...managers.map(m => ({ value: m.id, label: `${m.name} — ${m.position}` }))]"
                            />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('work_stage.label') }}
                            </label>
                            <SearchableSelect
                                v-model="form.work_stage"
                                :options="workStages.map(ws => ({ value: ws, label: t(`work_stage.${ws}`) }))"
                            />
                        </div>
                    </div>

                    <div v-else>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('table.manager') }}
                        </label>
                        <SearchableSelect
                            v-model="form.manager_id"
                            :options="[{ value: null, label: '—' }, ...managers.map(m => ({ value: m.id, label: `${m.name} — ${m.position}` }))]"
                        />
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('modal.field.email') }}
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            :disabled="isVacancy"
                            :placeholder="t('modal.field.email_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-400"
                        />
                        <div v-if="form.errors.email" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('modal.field.telegram') }}
                        </label>
                        <input
                            v-model="form.telegram"
                            type="text"
                            :disabled="isVacancy"
                            :placeholder="t('modal.field.telegram_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-slate-100 disabled:text-slate-400"
                        />
                        <div v-if="form.errors.telegram" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.telegram }}
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-slate-200 pt-4">
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50"
                            @click="emit('close')"
                        >
                            {{ t('modal.btn.cancel') }}
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ t('modal.btn.save') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Teleport>
</template>
