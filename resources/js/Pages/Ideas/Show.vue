<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import Badge from '../../Components/Pult/Badge.vue';
import IdeaFormModal from '../../Components/Pult/IdeaFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { Employee, Idea, IdeaPriority, IdeaStatus, Unit } from '../../types';

interface Props {
    idea: Idea;
    allUnits: Unit[];
    authors: Employee[];
    statuses: IdeaStatus[];
    priorities: IdeaPriority[];
    can: {
        update: boolean | null;
        delete: boolean | null;
    };
}

const props = defineProps<Props>();
const { t } = useTranslations();

const STATUS_COLORS: Record<IdeaStatus, string> = {
    new: '#6c63ff',
    under_review: '#f59e0b',
    approved: '#22c55e',
    rejected: '#ef4444',
    in_progress: '#8b5cf6',
    done: '#22c55e',
};

const PRIORITY_COLORS: Record<IdeaPriority, string> = {
    high: '#ef4444',
    medium: '#f59e0b',
    low: '#94a3b8',
};

const showModal = ref(false);

function openEdit() {
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
}

function destroy() {
    if (!confirm(t('ideas.delete_confirm'))) return;
    router.delete(`/ideas/${props.idea.display_id}`);
}

function authorLabel(): string {
    const a = props.idea.author;
    if (!a) return '—';
    return a.name ? `${a.name} — ${a.position}` : `[${t('table.vacancy')}] ${a.position}`;
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { day: '2-digit', month: '2-digit', year: 'numeric' });
}

const THRICE_DIMS: Array<{ key: `score_${string}`; letter: string; label: string; hint: string }> = [
    { key: 'score_time', letter: 'T', label: 'Time', hint: 'Скорость получения результата. Быстро = 10' },
    { key: 'score_headcount', letter: 'H', label: 'Headcount', hint: 'Требуемые ресурсы. Меньше людей = выше балл' },
    { key: 'score_reach', letter: 'R', label: 'Reach', hint: 'Сколько пользователей/объёма затронет' },
    { key: 'score_impact', letter: 'I', label: 'Impact', hint: 'Влияние на ключевые метрики/деньги' },
    { key: 'score_confidence', letter: 'C', label: 'Confidence', hint: 'Уверенность в результате' },
    { key: 'score_effort', letter: 'E', label: 'Effort', hint: 'Сложность / объём работ. Проще = выше балл' },
];

function thriceTierCls(score: number | null): string {
    if (score === null) return 'bg-slate-100 text-slate-500';
    if (score >= 45) return 'bg-emerald-100 text-emerald-800';
    if (score >= 30) return 'bg-lime-100 text-lime-800';
    if (score >= 18) return 'bg-amber-100 text-amber-800';
    return 'bg-rose-100 text-rose-800';
}
function thriceTierLabel(score: number | null): string {
    if (score === null) return 'не оценена';
    if (score >= 45) return 'делаем в первую очередь';
    if (score >= 30) return 'делаем';
    if (score >= 18) return 'под вопросом';
    return 'откладываем';
}
</script>

<template>
    <Head :title="idea.title" />

    <AppLayout>
        <div class="mx-auto max-w-3xl px-6 py-8">
            <Link
                href="/ideas"
                class="mb-6 inline-flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900"
            >
                ← {{ t('ideas.btn.back') }}
            </Link>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="mb-2 flex items-center gap-2 text-xs">
                        <span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-slate-600">{{ idea.display_id }}</span>
                        <Badge v-if="idea.unit" :color="idea.unit.color">
                            {{ idea.unit.name }}
                        </Badge>
                        <Badge :color="STATUS_COLORS[idea.status]">
                            {{ t(`ideas.status.${idea.status}`) }}
                        </Badge>
                        <Badge :color="PRIORITY_COLORS[idea.priority]">
                            {{ t(`ideas.priority.${idea.priority}`) }}
                        </Badge>
                        <span class="ml-auto text-slate-500">{{ formatDate(idea.created_at) }}</span>
                    </div>
                    <h1 class="text-2xl font-semibold leading-tight text-slate-900">{{ idea.title }}</h1>
                    <div class="mt-1 text-sm text-slate-500">
                        {{ authorLabel() }}
                    </div>
                </div>

                <div class="space-y-6 px-6 py-6">
                    <section>
                        <h2 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ t('ideas.detail.desc') }}
                        </h2>
                        <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
                            {{ idea.description }}
                        </p>
                    </section>

                    <section>
                        <h2 class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ t('ideas.detail.impact') }}
                        </h2>
                        <p class="whitespace-pre-wrap text-sm leading-relaxed text-slate-700">
                            {{ idea.impact }}
                        </p>
                    </section>

                    <!-- THRICE scoring breakdown -->
                    <section v-if="idea.thrice_score !== null || idea.score_time !== null">
                        <div class="mb-2 flex items-center justify-between">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                Оценка THRICE
                            </h2>
                            <span
                                class="rounded-full px-3 py-1 text-sm font-bold"
                                :class="thriceTierCls(idea.thrice_score)"
                                :title="thriceTierLabel(idea.thrice_score)"
                            >
                                {{ idea.thrice_score ?? '—' }} / 60 · {{ thriceTierLabel(idea.thrice_score) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            <div
                                v-for="d in THRICE_DIMS"
                                :key="d.key"
                                class="rounded-md border border-slate-200 bg-slate-50/60 p-2.5"
                                :title="d.hint"
                            >
                                <div class="mb-1 flex items-baseline justify-between">
                                    <span class="text-[11px] font-medium text-slate-700">
                                        <span class="font-mono text-indigo-500">{{ d.letter }}</span>
                                        <span class="ml-1">{{ d.label }}</span>
                                    </span>
                                    <span class="text-sm font-semibold text-slate-800">
                                        {{ (idea as Idea & Record<string, unknown>)[d.key] ?? '—' }}
                                    </span>
                                </div>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-200">
                                    <div
                                        class="h-full rounded-full bg-indigo-500"
                                        :style="{ width: (((idea as Idea & Record<string, unknown>)[d.key] as number | null) ?? 0) * 10 + '%' }"
                                    ></div>
                                </div>
                                <div class="mt-1 line-clamp-1 text-[10px] text-slate-400">{{ d.hint }}</div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-200 bg-slate-50 px-6 py-4">
                    <button
                        v-if="can.delete"
                        type="button"
                        class="rounded-md border border-rose-300 bg-white p-2 text-rose-500 hover:bg-rose-50 hover:text-rose-700"
                        @click="destroy"
                        :title="t('ideas.btn.delete')"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                    <button
                        v-if="can.update"
                        type="button"
                        class="rounded-md bg-indigo-600 p-2 text-white hover:bg-indigo-700"
                        @click="openEdit"
                        :title="t('ideas.btn.edit')"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <IdeaFormModal
            :show="showModal"
            :idea="idea"
            :units="allUnits"
            :authors="authors"
            :statuses="statuses"
            :priorities="priorities"
            @close="closeModal"
        />
    </AppLayout>
</template>
