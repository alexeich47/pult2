<script setup lang="ts">
import { ref, watch, computed, reactive } from 'vue';
import { useTranslations } from '../../Composables/useTranslations';

interface FieldDef {
    id: string;
    type: 'text' | 'date' | 'number' | 'textarea' | 'select';
    labelKey: string;
    phKey?: string;
    options?: string[];
    required?: boolean;
}

interface Props {
    show: boolean;
    ticketId: 'dayoff' | 'server' | 'domain' | 'payment' | 'contractor' | 'raise' | null;
    ticketColor: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const success = ref(false);
const errors = reactive<Record<string, boolean>>({});
const formData = reactive<Record<string, string>>({});

const FIELDS: Record<string, FieldDef[]> = {
    dayoff: [
        { id: 'date_from', type: 'date', labelKey: 'ticket.field.date_from', required: true },
        { id: 'date_to', type: 'date', labelKey: 'ticket.field.date_to', required: true },
        { id: 'reason', type: 'textarea', labelKey: 'ticket.field.reason', phKey: 'ticket.field.reason_ph' },
        { id: 'backup', type: 'text', labelKey: 'ticket.field.backup', phKey: 'ticket.field.backup_ph' },
    ],
    server: [
        { id: 'project', type: 'text', labelKey: 'ticket.field.project', phKey: undefined, required: true },
        { id: 'server_type', type: 'select', labelKey: 'ticket.field.server_type', options: ['VPS', 'Dedicated', 'Cloud (AWS)', 'Cloud (GCP)', 'Cloud (DO)'], required: true },
        { id: 'config', type: 'textarea', labelKey: 'ticket.field.config', phKey: 'ticket.field.config_ph' },
        { id: 'deadline', type: 'date', labelKey: 'ticket.field.deadline' },
    ],
    domain: [
        { id: 'domain_name', type: 'text', labelKey: 'ticket.field.domain_name', phKey: 'ticket.field.domain_name_ph', required: true },
        { id: 'tld', type: 'select', labelKey: 'ticket.field.tld', options: ['.com', '.io', '.net', '.org', '.ru', '.ua', '.co'], required: true },
        { id: 'for_project', type: 'text', labelKey: 'ticket.field.for_project', phKey: 'ticket.field.for_project_ph' },
        { id: 'period', type: 'select', labelKey: 'ticket.field.period', options: ['ticket.period.1y', 'ticket.period.2y', 'ticket.period.3y'] },
    ],
    payment: [
        { id: 'amount', type: 'number', labelKey: 'ticket.field.amount', phKey: 'ticket.field.amount_ph', required: true },
        { id: 'currency', type: 'select', labelKey: 'ticket.field.currency', options: ['USD', 'EUR', 'UAH', 'RUB', 'GBP'], required: true },
        { id: 'recipient', type: 'text', labelKey: 'ticket.field.recipient', phKey: 'ticket.field.recipient_ph', required: true },
        { id: 'purpose', type: 'textarea', labelKey: 'ticket.field.purpose', phKey: 'ticket.field.purpose_ph', required: true },
        { id: 'invoice', type: 'text', labelKey: 'ticket.field.invoice', phKey: 'ticket.field.invoice_ph' },
    ],
    contractor: [
        { id: 'position', type: 'text', labelKey: 'ticket.field.con_position', phKey: 'ticket.field.con_position_ph', required: true },
        { id: 'company', type: 'text', labelKey: 'ticket.field.con_company', phKey: 'ticket.field.con_company_ph', required: true },
        { id: 'period', type: 'select', labelKey: 'ticket.field.con_period', options: ['ticket.period.1m', 'ticket.period.3m', 'ticket.period.6m', 'ticket.period.12m'] },
        { id: 'rate', type: 'number', labelKey: 'ticket.field.con_rate', phKey: 'ticket.field.con_rate_ph', required: true },
        { id: 'currency', type: 'select', labelKey: 'ticket.field.currency', options: ['USD', 'EUR', 'UAH', 'RUB'], required: true },
        { id: 'scope', type: 'textarea', labelKey: 'ticket.field.con_scope', phKey: 'ticket.field.con_scope_ph', required: true },
    ],
    raise: [
        { id: 'employee_name', type: 'text', labelKey: 'ticket.field.raise_employee', phKey: 'ticket.field.raise_employee_ph', required: true },
        { id: 'current_salary', type: 'number', labelKey: 'ticket.field.raise_current', phKey: 'ticket.field.raise_current_ph', required: true },
        { id: 'proposed_salary', type: 'number', labelKey: 'ticket.field.raise_proposed', phKey: 'ticket.field.raise_proposed_ph', required: true },
        { id: 'currency', type: 'select', labelKey: 'ticket.field.currency', options: ['USD', 'EUR', 'UAH', 'RUB'], required: true },
        { id: 'justification', type: 'textarea', labelKey: 'ticket.field.raise_justification', phKey: 'ticket.field.raise_justification_ph', required: true },
    ],
};

const fields = computed<FieldDef[]>(() => (props.ticketId ? FIELDS[props.ticketId] : []));

const titleKey = computed(() => `ticket.${props.ticketId}.title`);

function optionLabel(field: FieldDef, opt: string): string {
    if (field.id === 'period') return t(opt);
    return opt;
}

watch(
    () => props.show,
    (show) => {
        if (!show) return;
        success.value = false;
        Object.keys(errors).forEach((k) => delete errors[k]);
        Object.keys(formData).forEach((k) => delete formData[k]);
        fields.value.forEach((f) => {
            formData[f.id] = f.type === 'select' && f.options?.length ? f.options[0] : '';
        });
    },
);

function handleSubmit() {
    let valid = true;
    fields.value.forEach((f) => {
        if (f.required && !formData[f.id]?.trim()) {
            errors[f.id] = true;
            valid = false;
        } else {
            errors[f.id] = false;
        }
    });
    if (!valid) return;
    success.value = true;
}

function close() {
    emit('close');
}

const inputClass = 'w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500';
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="close"
        >
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white shadow-2xl dark:bg-slate-900">
                <!-- Header -->
                <div class="flex items-center gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                    <div
                        v-if="ticketId"
                        class="flex h-8 w-8 items-center justify-center rounded-md text-sm font-bold text-white"
                        :style="{ backgroundColor: ticketColor }"
                    >
                        {{ ticketId[0].toUpperCase() }}
                    </div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
                        {{ t(titleKey) }}
                    </h2>
                    <button type="button" class="ml-auto text-slate-400 hover:text-slate-600" @click="close">✕</button>
                </div>

                <!-- Success state -->
                <template v-if="success">
                    <div class="px-5 py-12 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full text-2xl font-bold text-white"
                            :style="{ backgroundColor: ticketColor }"
                        >
                            ✓
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-slate-900 dark:text-slate-100">
                            {{ t('ticket.success.title') }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                            {{ t('ticket.success.sub') }}
                        </p>
                    </div>
                    <div class="flex justify-center border-t border-slate-200 px-5 py-4 dark:border-slate-700">
                        <button
                            type="button"
                            class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            @click="close"
                        >
                            {{ t('ticket.success.close') }}
                        </button>
                    </div>
                </template>

                <!-- Form -->
                <template v-else>
                    <form @submit.prevent="handleSubmit" class="space-y-4 px-5 py-4">
                        <div v-for="field in fields" :key="field.id">
                            <label :for="'tf-' + field.id" class="mb-1 block text-xs font-medium text-slate-700 dark:text-slate-300">
                                {{ t(field.labelKey) }}
                                <span v-if="field.required" class="text-rose-500">*</span>
                            </label>

                            <textarea
                                v-if="field.type === 'textarea'"
                                :id="'tf-' + field.id"
                                v-model="formData[field.id]"
                                rows="3"
                                :placeholder="field.phKey ? t(field.phKey) : ''"
                                :class="[inputClass, errors[field.id] ? 'border-rose-400' : '']"
                                @input="errors[field.id] = false"
                            />
                            <select
                                v-else-if="field.type === 'select'"
                                :id="'tf-' + field.id"
                                v-model="formData[field.id]"
                                :class="[inputClass, errors[field.id] ? 'border-rose-400' : '']"
                                @change="errors[field.id] = false"
                            >
                                <option v-for="opt in field.options" :key="opt" :value="opt">
                                    {{ optionLabel(field, opt) }}
                                </option>
                            </select>
                            <input
                                v-else
                                :id="'tf-' + field.id"
                                v-model="formData[field.id]"
                                :type="field.type"
                                :placeholder="field.phKey ? t(field.phKey) : ''"
                                :class="[inputClass, errors[field.id] ? 'border-rose-400' : '']"
                                @input="errors[field.id] = false"
                            />
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-200 pt-4 dark:border-slate-700">
                            <button
                                type="button"
                                class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                @click="close"
                            >
                                {{ t('modal.btn.cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                {{ t('ticket.submit') }}
                            </button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </Teleport>
</template>
