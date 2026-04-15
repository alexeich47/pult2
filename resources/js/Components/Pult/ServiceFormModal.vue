<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Service, ServiceBilling, ServiceCurrency, ServiceStatus, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';

interface Props {
    show: boolean;
    service: Service | null;
    units: Unit[];
    categories: string[];
    currencies: ServiceCurrency[];
    billingCycles: ServiceBilling[];
    statuses: ServiceStatus[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const form = useForm({
    name: '',
    url: '',
    category: 'Инструменты',
    unit_id: '',
    cost: 0,
    currency: 'USD' as ServiceCurrency,
    billing: 'monthly' as ServiceBilling,
    next_payment: '' as string | null,
    status: 'active' as ServiceStatus,
});

const isEdit = computed(() => props.service !== null);
const headerTitle = computed(() => (isEdit.value ? t('services.btn.edit') : t('services.btn.add')));

watch(
    () => [props.show, props.service] as const,
    ([show, service]) => {
        if (!show) return;
        if (service) {
            form.name = service.name;
            form.url = service.url ?? '';
            form.category = service.category;
            form.unit_id = service.unit_id;
            form.cost = parseFloat(service.cost);
            form.currency = service.currency;
            form.billing = service.billing;
            form.next_payment = service.next_payment?.slice(0, 10) ?? '';
            form.status = service.status;
        } else {
            form.reset();
            form.unit_id = props.units[0]?.id ?? '';
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
    if (isEdit.value && props.service) {
        form.put(`/services/${props.service.id}`, opts);
    } else {
        form.post('/services', opts);
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
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.name') }} <span class="text-rose-500">*</span></label>
                        <input v-model="form.name" type="text" placeholder="Notion" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        <div v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.url') }}</label>
                        <input v-model="form.url" type="text" placeholder="notion.so" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.category') }}</label>
                            <select v-model="form.category" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.company') }}</label>
                            <select v-model="form.unit_id" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <div v-if="form.errors.unit_id" class="mt-1 text-xs text-rose-600">{{ form.errors.unit_id }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.cost') }}</label>
                            <input v-model.number="form.cost" type="number" min="0" step="0.01" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <div v-if="form.errors.cost" class="mt-1 text-xs text-rose-600">{{ form.errors.cost }}</div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.currency') }}</label>
                            <select v-model="form.currency" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="c in currencies" :key="c" :value="c">{{ c }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.billing') }}</label>
                            <select v-model="form.billing" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option v-for="b in billingCycles" :key="b" :value="b">{{ t(`services.billing.${b}`) }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.next_payment') }}</label>
                            <input v-model="form.next_payment" type="date" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('services.field.status') }}</label>
                        <select v-model="form.status" class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            <option v-for="s in statuses" :key="s" :value="s">{{ t(`services.status.${s}`) }}</option>
                        </select>
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
