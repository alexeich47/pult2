<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { Employee, Idea, IdeaPriority, IdeaStatus, PageProps, Unit } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';
import SearchableSelect from './SearchableSelect.vue';

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
const page = usePage<PageProps>();

// Employee matching the currently authenticated user (by email), if any.
const currentEmployeeId = computed(() => page.props.auth?.user?.employee_id ?? null);

function defaultAuthorId(): number {
    if (currentEmployeeId.value && props.authors.some(a => a.id === currentEmployeeId.value)) {
        return currentEmployeeId.value;
    }
    return props.authors[0]?.id ?? 0;
}

type FormShape = {
    unit_id: string;
    author_id: number;
    priority: IdeaPriority;
    status: IdeaStatus;
    title: string;
    description: string;
    impact: string;
    score_time: number | null;
    score_headcount: number | null;
    score_reach: number | null;
    score_impact: number | null;
    score_confidence: number | null;
    score_effort: number | null;
};

const form = useForm<FormShape>({
    unit_id: '',
    author_id: 0,
    priority: 'medium' as IdeaPriority,
    status: 'new' as IdeaStatus,
    title: '',
    description: '',
    impact: '',
    score_time: null,
    score_headcount: null,
    score_reach: null,
    score_impact: null,
    score_confidence: null,
    score_effort: null,
});

const THRICE_DIMS: Array<{ key: keyof FormShape & `score_${string}`; letter: string; label: string; hint: string }> = [
    { key: 'score_time', letter: 'T', label: 'Time', hint: 'Скорость получения результата. Быстро = 10, очень долго = 1' },
    { key: 'score_headcount', letter: 'H', label: 'Headcount', hint: 'Требуемые ресурсы/люди. Меньше людей нужно = выше балл' },
    { key: 'score_reach', letter: 'R', label: 'Reach', hint: 'Сколько пользователей / объёма затронет. Больше охват = выше балл' },
    { key: 'score_impact', letter: 'I', label: 'Impact', hint: 'Влияние на ключевые метрики/деньги. Сильнее = выше балл' },
    { key: 'score_confidence', letter: 'C', label: 'Confidence', hint: 'Уверенность в результате. Больше уверенности = выше балл' },
    { key: 'score_effort', letter: 'E', label: 'Effort', hint: 'Сложность / объём работ. Проще = выше балл' },
];

const thriceTotal = computed(() => {
    const vals = THRICE_DIMS.map(d => form[d.key] as number | null);
    if (vals.some(v => v === null)) return null;
    return vals.reduce((acc: number, v) => acc + (v ?? 0), 0);
});
const thriceTotalClass = computed(() => {
    const s = thriceTotal.value;
    if (s === null) return 'bg-slate-100 text-slate-600';
    if (s >= 45) return 'bg-emerald-100 text-emerald-800';
    if (s >= 30) return 'bg-lime-100 text-lime-800';
    if (s >= 18) return 'bg-amber-100 text-amber-800';
    return 'bg-rose-100 text-rose-800';
});
const thriceTotalLabel = computed(() => {
    const s = thriceTotal.value;
    if (s === null) return 'Заполните все 6 параметров';
    if (s >= 45) return 'Делаем в первую очередь';
    if (s >= 30) return 'Делаем';
    if (s >= 18) return 'Под вопросом';
    return 'Откладываем / в архив';
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
            form.score_time = (idea as Idea & Record<string, unknown>).score_time as number | null ?? null;
            form.score_headcount = (idea as Idea & Record<string, unknown>).score_headcount as number | null ?? null;
            form.score_reach = (idea as Idea & Record<string, unknown>).score_reach as number | null ?? null;
            form.score_impact = (idea as Idea & Record<string, unknown>).score_impact as number | null ?? null;
            form.score_confidence = (idea as Idea & Record<string, unknown>).score_confidence as number | null ?? null;
            form.score_effort = (idea as Idea & Record<string, unknown>).score_effort as number | null ?? null;
        } else {
            form.reset();
            form.unit_id = props.units[0]?.id ?? '';
            form.author_id = defaultAuthorId();
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

const unitOptions = computed(() => props.units.map(u => ({ value: u.id, label: u.name, color: u.color })));
const authorOptions = computed(() => props.authors.map(a => ({ value: a.id, label: authorLabel(a) })));
const priorityOptions = computed(() => props.priorities.map(p => ({ value: p, label: t(`ideas.priority.${p}`) })));
const statusOptions = computed(() => props.statuses.map(s => ({ value: s, label: t(`ideas.status.${s}`) })));
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
                            <SearchableSelect v-model="form.unit_id" :options="unitOptions" />
                            <div v-if="form.errors.unit_id" class="mt-1 text-xs text-rose-600">
                                {{ form.errors.unit_id }}
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('ideas.form.author') }} <span class="text-rose-500">*</span>
                            </label>
                            <SearchableSelect v-model="form.author_id" :options="authorOptions" />
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
                            <SearchableSelect v-model="form.priority" :options="priorityOptions" />
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-700">
                                {{ t('ideas.form.status') }} <span class="text-rose-500">*</span>
                            </label>
                            <SearchableSelect v-model="form.status" :options="statusOptions" />
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

                    <!-- THRICE scoring -->
                    <div class="rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                        <div class="mb-2 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-slate-800">Оценка THRICE</div>
                                <div class="text-[11px] text-slate-500">Выставьте 1–10 по каждому параметру. Сумма от 6 до 60.</div>
                            </div>
                            <div
                                class="rounded-full px-3 py-1 text-sm font-bold"
                                :class="thriceTotalClass"
                                :title="thriceTotalLabel"
                            >
                                <template v-if="thriceTotal !== null">{{ thriceTotal }} / 60</template>
                                <template v-else>— / 60</template>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <label v-for="d in THRICE_DIMS" :key="d.key" class="block rounded-md border border-slate-200 bg-white p-2" :title="d.hint">
                                <span class="mb-1 flex items-baseline gap-1 text-[11px] font-medium text-slate-700">
                                    <span class="font-mono text-indigo-500">{{ d.letter }}</span>
                                    <span>{{ d.label }}</span>
                                </span>
                                <div class="flex items-center gap-2">
                                    <input
                                        v-model.number="form[d.key]"
                                        type="range"
                                        min="1"
                                        max="10"
                                        step="1"
                                        class="h-1.5 flex-1 accent-indigo-500"
                                    />
                                    <input
                                        v-model.number="form[d.key]"
                                        type="number"
                                        min="1"
                                        max="10"
                                        placeholder="—"
                                        class="w-12 rounded border border-slate-300 px-1 py-0.5 text-center text-xs"
                                    />
                                </div>
                                <div class="mt-1 line-clamp-1 text-[10px] text-slate-400">{{ d.hint }}</div>
                            </label>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-500">
                            <span class="font-medium" :class="thriceTotalClass.split(' ').find(c => c.startsWith('text-'))">
                                {{ thriceTotalLabel }}
                            </span>
                            &nbsp;·&nbsp;
                            <span class="text-slate-400">45+ делаем, 30–44 делаем, 18–29 под вопросом, &lt;18 откладываем</span>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-700">
                            {{ t('ideas.form.impact') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            v-model="form.impact"
                            rows="4"
                            placeholder="Почему это важно и какая выгода: опишите кратко текстом свои оценки по THRICE выше"
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
