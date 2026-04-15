<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Employee, Idea, IdeaPriority, IdeaStatus, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';

interface Props {
    show: boolean;
    idea: Idea | null;
    units: Unit[];
    authors: Employee[];
    statuses: IdeaStatus[];
    priorities: IdeaPriority[];
}

const props = defineProps<Props>();
const emit = defineEmits<{ close: [] }>();

const { t } = useTranslations();

const form = useForm({
    unit_id: '',
    author_id: 0,
    priority: 'medium' as IdeaPriority,
    status: 'new' as IdeaStatus,
    title: '',
    description: '',
    impact: '',
});

const isEdit = computed(() => props.idea !== null);
const headerTitle = computed(() =>
    isEdit.value ? t('ideas.btn.edit') : t('ideas.btn.add'),
);

watch(
    () => [props.show, props.idea] as const,
    ([show, idea]) => {
        if (!show) return;
        if (idea) {
            form.unit_id = idea.unit_id;
            form.author_id = idea.author_id;
            form.priority = idea.priority;
            form.status = idea.status;
            form.title = idea.title;
            form.description = idea.description;
            form.impact = idea.impact;
        } else {
            form.reset();
            form.unit_id = props.units[0]?.id ?? '';
            form.author_id = props.authors[0]?.id ?? 0;
            form.priority = 'medium';
            form.status = 'new';
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
    if (isEdit.value && props.idea) {
        form.put(`/ideas/${props.idea.display_id}`, opts);
    } else {
        form.post('/ideas', opts);
    }
}

function authorLabel(employee: Employee): string {
    return employee.name ? `${employee.name} — ${employee.position}` : `[${t('table.vacancy')}] ${employee.position}`;
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="show"
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4"
            @click.self="emit('close')"
        >
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <h2 class="text-lg font-semibold text-slate-900">
                        {{ headerTitle }}
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
                            {{ t('ideas.form.title') }} <span class="text-rose-500">*</span>
                        </label>
                        <input
                            v-model="form.title"
                            type="text"
                            :placeholder="t('ideas.form.title_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.title" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.title }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('ideas.form.unit') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.unit_id"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                            <div v-if="form.errors.unit_id" class="mt-1 text-xs text-rose-600">
                                {{ form.errors.unit_id }}
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('ideas.form.author') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.author_id"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option v-for="a in authors" :key="a.id" :value="a.id">{{ authorLabel(a) }}</option>
                            </select>
                            <div v-if="form.errors.author_id" class="mt-1 text-xs text-rose-600">
                                {{ form.errors.author_id }}
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('ideas.form.priority') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.priority"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option v-for="p in priorities" :key="p" :value="p">{{ t(`ideas.priority.${p}`) }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('ideas.form.status') }} <span class="text-rose-500">*</span>
                            </label>
                            <select
                                v-model="form.status"
                                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            >
                                <option v-for="s in statuses" :key="s" :value="s">{{ t(`ideas.status.${s}`) }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('ideas.form.description') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            :placeholder="t('ideas.form.description_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.description" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.description }}
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('ideas.form.impact') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="form.impact"
                            rows="4"
                            :placeholder="t('ideas.form.impact_ph')"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                        <div v-if="form.errors.impact" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.impact }}
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
