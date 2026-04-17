<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { computed, reactive } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface RevenueUnit { id: string; name: string; color: string }

const props = defineProps<{
    year: number;
    revenueUnits: RevenueUnit[];
    targets: Record<string, Record<number, number | null>>;
}>();

const { t } = useTranslations();

const MONTH_NAMES_RU = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
const MONTH_NAMES_UK = ['Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];
const MONTH_NAMES_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const MONTH_SHORT_RU = ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];

function monthName(m: number): string {
    const l = document.documentElement.lang || 'ru';
    return (l === 'uk' ? MONTH_NAMES_UK : l === 'en' ? MONTH_NAMES_EN : MONTH_NAMES_RU)[m - 1];
}
function monthShort(m: number): string { return MONTH_SHORT_RU[m - 1]; }

const months = Array.from({ length: 12 }, (_, i) => i + 1);

// Local mutable copy of targets for two-way binding (Inertia props are read-only).
const grid = reactive<Record<string, Record<number, number | null>>>({});
for (const u of props.revenueUnits) {
    grid[u.id] = {};
    for (const m of months) {
        grid[u.id][m] = props.targets[u.id]?.[m] ?? null;
    }
}

const form = useForm<{ year: number; entries: Array<{ unit_id: string; month: number; target: number }> }>({
    year: props.year,
    entries: [],
});

function changeYear(d: number) {
    router.get('/service-pages/mvr-planning', { year: props.year + d }, { preserveState: false });
}

function submit() {
    form.entries = [];
    for (const u of props.revenueUnits) {
        for (const m of months) {
            const v = grid[u.id][m];
            if (v !== null && v !== undefined && Number(v) >= 0) {
                form.entries.push({ unit_id: u.id, month: m, target: Number(v) });
            }
        }
    }
    form.post('/service-pages/mvr-planning', { preserveScroll: true });
}

function unitTotal(unitId: string): number {
    return months.reduce((acc, m) => acc + (Number(grid[unitId][m]) || 0), 0);
}
function monthTotal(month: number): number {
    return props.revenueUnits.reduce((acc, u) => acc + (Number(grid[u.id][month]) || 0), 0);
}
const grandTotal = computed(() => props.revenueUnits.reduce((acc, u) => acc + unitTotal(u.id), 0));
</script>

<template>
    <Head :title="t('service_pages.mvr_planning.title')" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-6 py-8">
            <div class="mb-6 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/service-pages" class="hover:text-indigo-600">{{ t('service_pages.title') }}</Link>
                <span>/</span>
                <span class="text-slate-700">{{ t('service_pages.mvr_planning.title') }}</span>
            </div>

            <div class="mb-6 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-3xl">🎯</span>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ t('service_pages.mvr_planning.title') }}</h1>
                        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ t('service_pages.mvr_planning.sub') }}</p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <button class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200" @click="changeYear(-1)">&larr;</button>
                    <span class="min-w-[60px] text-center text-sm font-bold text-slate-700">{{ year }}</span>
                    <button class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200" @click="changeYear(1)">&rarr;</button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left dark:bg-slate-900">{{ t('service_pages.mvr_planning.col_unit') }}</th>
                            <th v-for="m in months" :key="m" class="px-2 py-3 text-right" :title="monthName(m)">{{ monthShort(m) }}</th>
                            <th class="px-3 py-3 text-right">Σ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="u in revenueUnits" :key="u.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="sticky left-0 z-10 bg-white px-4 py-2 dark:bg-slate-800">
                                <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-800 dark:text-slate-200">
                                    <span class="inline-block h-2 w-2 rounded-full" :style="{ backgroundColor: u.color }"></span>
                                    {{ u.name }}
                                </span>
                            </td>
                            <td v-for="m in months" :key="m" class="px-1 py-1.5">
                                <input
                                    v-model.number="grid[u.id][m]"
                                    type="number"
                                    min="0"
                                    step="100"
                                    placeholder="—"
                                    class="w-20 rounded border border-slate-200 bg-white px-1.5 py-1 text-right text-xs text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                                />
                            </td>
                            <td class="px-3 py-2 text-right text-xs font-semibold text-slate-700 dark:text-slate-300">
                                ${{ unitTotal(u.id).toLocaleString() }}
                            </td>
                        </tr>
                        <tr class="bg-slate-50 font-semibold dark:bg-slate-900">
                            <td class="sticky left-0 z-10 bg-slate-50 px-4 py-2.5 text-xs uppercase tracking-wider text-slate-500 dark:bg-slate-900">Σ</td>
                            <td v-for="m in months" :key="m" class="px-2 py-2.5 text-right text-xs text-slate-700 dark:text-slate-300">
                                ${{ Math.round(monthTotal(m) / 1000).toLocaleString() }}k
                            </td>
                            <td class="px-3 py-2.5 text-right text-sm text-indigo-600 dark:text-indigo-400">
                                ${{ Math.round(grandTotal / 1000).toLocaleString() }}k
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 flex items-center justify-end gap-3">
                <span v-if="form.recentlySuccessful" class="text-xs text-emerald-600">{{ t('service_pages.mvr_planning.saved') }}</span>
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                    :disabled="form.processing"
                    @click="submit"
                >
                    {{ t('service_pages.mvr_planning.save_all') }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
