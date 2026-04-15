<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { RiskEntry, RiskType } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';

interface Props {
    show: boolean;
    entry: RiskEntry | null;
    defaultType?: RiskType;
    types: RiskType[];
    statusesByType: Record<RiskType, string[]>;
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const form = useForm({
    type: 'risk' as RiskType,
    name: '',
    description: '',
    owner_name: '',
    status: 'open',
    entry_date: '',
});

const isEdit = computed(() => props.entry !== null);
const headerTitle = computed(() => (isEdit.value ? t('risks.btn.edit') : t('risks.btn.add')));

const availableStatuses = computed(() => props.statusesByType[form.type] ?? []);

function today(): string {
    return new Date().toISOString().slice(0, 10);
}

watch(
    () => [props.show, props.entry, props.defaultType] as const,
    ([show, entry, defaultType]) => {
        if (!show) return;
        if (entry) {
            form.type = entry.type;
            form.name = entry.name;
            form.description = entry.description;
            form.owner_name = entry.owner_name;
            form.status = entry.status;
            form.entry_date = entry.entry_date?.slice(0, 10) ?? today();
        } else {
            form.reset();
            form.type = defaultType ?? 'risk';
            form.status = (props.statusesByType[form.type] ?? ['open'])[0];
            form.entry_date = today();
        }
        form.clearErrors();
    },
    { immediate: true },
);

// When type changes, reset status to the first valid one for the new type.
watch(
    () => form.type,
    (t) => {
        const allowed = props.statusesByType[t] ?? [];
        if (!allowed.includes(form.status)) {
            form.status = allowed[0] ?? '';
        }
    },
);

function submit() {
    const opts = {
        preserveScroll: true,
        onSuccess: () => emit('close'),
    };
    if (isEdit.value && props.entry) {
        form.put(`/risks/${props.entry.display_id}`, opts);
    } else {
        form.post('/risks', opts);
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
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('risks.field.type') }}</label>
                            <select
                                v-model="form.type"
                                :disabled="isEdit"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:bg-slate-100"
                            >
                                <option v-for="tp in types" :key="tp" :value="tp">{{ t(`risks.log.${tp}.title`) }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('risks.col.date') }}</label>
                            <input
                                v-model="form.entry_date"
                                type="date"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            <div v-if="form.errors.entry_date" class="mt-1 text-xs text-rose-600">{{ form.errors.entry_date }}</div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('risks.col.name') }} <span class="text-rose-500">*</span></label>
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('risks.col.owner') }} <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.owner_name"
                                type="text"
                                placeholder="Иванов И."
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                            <div v-if="form.errors.owner_name" class="mt-1 text-xs text-rose-600">{{ form.errors.owner_name }}</div>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('risks.col.status') }}</label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option v-for="s in availableStatuses" :key="s" :value="s">{{ t(`risks.status.${s}`) }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">{{ t('risks.detail.desc') }} <span class="text-rose-500">*</span></label>
                        <textarea
                            v-model="form.description"
                            rows="5"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.description" class="mt-1 text-xs text-rose-600">{{ form.errors.description }}</div>
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
