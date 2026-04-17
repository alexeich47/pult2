<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import MvrChart from '../../Components/Pult/MvrChart.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import SearchableSelect from '../../Components/Pult/SearchableSelect.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { MvrEntry } from '../../types';

interface DailyRow { id: number | null; unit_id: string | null; date: string; plan: number; fact: number }

interface MonthlyDeviation {
    month: number;
    plan: number;
    fact: number;
    diff: number;
    diff_pct: number;
    has_data: boolean;
}

interface RevenueUnit { id: string; name: string; color: string }

interface Props {
    entries: MvrEntry[];
    dailyEntries: DailyRow[];
    monthlyDeviations: MonthlyDeviation[];
    revenueUnits: RevenueUnit[];
    year: number;
    month: number;
    daysInMonth: number;
    isHoldingView: boolean;
    scopeUnitId: string | null;
    can: { create: boolean | null; delete: boolean | null };
}

const props = defineProps<Props>();
const { t } = useTranslations();

type Tab = 'chart' | 'data' | 'plan' | 'deviation';
const activeTab = ref<Tab>('chart');

const MONTH_NAMES_RU = ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
const MONTH_NAMES_EN = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const MONTH_NAMES_UK = ['Січень','Лютий','Березень','Квітень','Травень','Червень','Липень','Серпень','Вересень','Жовтень','Листопад','Грудень'];

function monthName(m: number): string {
    const l = document.documentElement.lang || 'ru';
    return (l === 'uk' ? MONTH_NAMES_UK : l === 'en' ? MONTH_NAMES_EN : MONTH_NAMES_RU)[m - 1];
}

function daysIn(m: number, y: number): number { return new Date(y, m, 0).getDate(); }

// ── Plan tab: inline edit targets ─────────────────────────────────
const editingId = ref<number | null>(null);
const editForm = useForm<{ target: number | null; actual: number }>({ target: null, actual: 0 });

function startEditPlan(e: MvrEntry) { editingId.value = e.id; editForm.target = Number(e.target) || null; editForm.actual = Number(e.actual); }
function savePlan(e: MvrEntry) { editForm.put(`/finance/${e.id}`, { preserveScroll: true, onSuccess: () => { editingId.value = null; } }); }

const showAddForm = ref(false);
const defaultUnitId = computed(() => props.scopeUnitId ?? props.revenueUnits[0]?.id ?? '');
const addForm = useForm<{ unit_id: string; year: number; month: number; target: number | null; actual: number; currency: string }>({
    unit_id: defaultUnitId.value,
    year: props.year,
    month: 1,
    target: null,
    actual: 0,
    currency: 'USD',
});
const allMonths = Array.from({ length: 12 }, (_, i) => i + 1);

function openAdd() {
    showAddForm.value = true;
    addForm.unit_id = defaultUnitId.value;
    addForm.year = props.year;
    addForm.month = 1;
    addForm.target = null;
    addForm.actual = 0;
}
function submitAdd() { addForm.post('/finance', { preserveScroll: true, onSuccess: () => { showAddForm.value = false; } }); }

function unitName(id: string | null): string {
    if (!id) return '';
    return props.revenueUnits.find(u => u.id === id)?.name ?? id;
}
function unitColor(id: string | null): string {
    if (!id) return '#94a3b8';
    return props.revenueUnits.find(u => u.id === id)?.color ?? '#94a3b8';
}

const deletingId = ref<number | null>(null);
function doDelete() { if (!deletingId.value) return; router.delete(`/finance/${deletingId.value}`, { preserveScroll: true, onFinish: () => { deletingId.value = null; } }); }

// ── Data tab: daily plan/fact editing ────────────────────────────
const editingDailyId = ref<number | null>(null);
const dailyForm = useForm({ plan: 0, fact: 0 });

function startEditDaily(d: DailyRow) { if (d.id === null) return; editingDailyId.value = d.id; dailyForm.plan = Number(d.plan); dailyForm.fact = Number(d.fact); }
function saveDaily(d: DailyRow) { if (d.id === null) return; dailyForm.put(`/finance/daily/${d.id}`, { preserveScroll: true, onSuccess: () => { editingDailyId.value = null; } }); }

function changeYear(d: number) { router.get('/finance', { year: props.year + d, month: props.month }, { preserveState: true }); }
function changeMonth(d: number) {
    let m = props.month + d; let y = props.year;
    if (m < 1) { m = 12; y--; } else if (m > 12) { m = 1; y++; }
    router.get('/finance', { year: y, month: m }, { preserveState: true });
}

// Chart data — daily plan/fact for the selected month
const chartData = computed(() => props.dailyEntries.map(d => ({ date: d.date, plan: Number(d.plan), fact: Number(d.fact) })));

function formatDiff(t: string, a: string): string { const tv = Number(t); const av = Number(a); if (av === 0) return '---'; const d = av - tv; return `${d >= 0 ? '+' : ''}$${d.toLocaleString()}`; }
function diffClass(t: string, a: string): string { if (Number(a) === 0) return 'text-slate-400'; return Number(a) - Number(t) >= 0 ? 'text-emerald-600' : 'text-rose-600'; }

function formatDay(iso: string): string { return new Date(iso).getDate().toString(); }
function weekday(iso: string): string {
    const d = new Date(iso).getDay();
    return ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'][d];
}
function isWeekend(iso: string): boolean { const d = new Date(iso).getDay(); return d === 0 || d === 6; }

const TABS: { id: Tab; icon: string; labelKey: string }[] = [
    { id: 'chart', icon: '📊', labelKey: 'finance.tab.chart' },
    { id: 'data', icon: '📝', labelKey: 'finance.tab.data' },
    { id: 'plan', icon: '🎯', labelKey: 'finance.tab.plan' },
    { id: 'deviation', icon: '📉', labelKey: 'finance.tab.deviation' },
];

const unitSelectOptions = computed(() => props.revenueUnits.map(u => ({ value: u.id, label: u.name, color: u.color })));
const monthSelectOptions = computed(() => allMonths.map(m => ({ value: m, label: monthName(m) })));

// ── Deviation tab helpers ─────────────────────────────────────────
function deviationColor(pct: number, alpha = 1): string {
    const intensity = Math.min(Math.abs(pct) / 25, 1); // saturate at ±25%
    const a = (0.08 + intensity * 0.55) * alpha;
    return pct >= 0
        ? `rgba(16, 185, 129, ${a.toFixed(3)})`
        : `rgba(244, 63, 94, ${a.toFixed(3)})`;
}

function deviationTextClass(pct: number): string {
    const abs = Math.abs(pct);
    if (pct >= 0) return abs >= 10 ? 'text-emerald-700' : 'text-emerald-600';
    return abs >= 10 ? 'text-rose-700' : 'text-rose-600';
}

const maxAbsPct = computed(() => {
    const vals = props.monthlyDeviations.filter(m => m.has_data).map(m => Math.abs(m.diff_pct));
    return vals.length > 0 ? Math.max(...vals, 5) : 5; // min scale ±5%
});

// SVG bar chart geometry
const CW = 800;
const CH = 220;
const C_PAD_LEFT = 30;
const C_PAD_RIGHT = 10;
const C_PAD_TOP = 20;
const C_PAD_BOTTOM = 40;
const chartInnerW = CW - C_PAD_LEFT - C_PAD_RIGHT;
const chartInnerH = CH - C_PAD_TOP - C_PAD_BOTTOM;
const zeroY = C_PAD_TOP + chartInnerH / 2;

function colX(i: number): number {
    return C_PAD_LEFT + (i + 0.5) * (chartInnerW / 12);
}

function barW(): number {
    return Math.min(chartInnerW / 12 * 0.6, 40);
}

function barRect(m: MonthlyDeviation): { y: number; h: number } {
    const halfH = chartInnerH / 2;
    const ratio = Math.min(Math.abs(m.diff_pct) / maxAbsPct.value, 1);
    const h = ratio * halfH;
    return m.diff_pct >= 0
        ? { y: zeroY - h, h }
        : { y: zeroY, h };
}

function monthShort(m: number): string {
    const SHORT_RU = ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'];
    const SHORT_UK = ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'];
    const SHORT_EN = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const l = document.documentElement.lang || 'ru';
    return (l === 'uk' ? SHORT_UK : l === 'en' ? SHORT_EN : SHORT_RU)[m - 1];
}
</script>

<template>
    <Head :title="t('finance.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900">{{ t('finance.title') }}</h1>
                    <p class="mt-1 text-sm text-slate-600">{{ t('finance.sub') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200" @click="changeYear(-1)">&larr;</button>
                    <span class="text-sm font-bold text-slate-700">{{ year }}</span>
                    <button class="rounded-md bg-slate-100 px-2.5 py-1 text-sm text-slate-600 hover:bg-slate-200" @click="changeYear(1)">&rarr;</button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <!-- Block header — shared across all tabs -->
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-semibold text-slate-800">
                            {{ t('finance.mvr_title') }}
                            <span v-if="isHoldingView" class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700">
                                Σ {{ t('service_pages.holding_view') }}
                            </span>
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">{{ t('finance.mvr_sub') }}</p>
                    </div>
                    <!-- Month selector — pinned, shown only for month-scoped tabs -->
                    <div v-if="activeTab === 'chart' || activeTab === 'data'" class="flex shrink-0 items-center gap-2">
                        <button class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-200" @click="changeMonth(-1)">&larr;</button>
                        <span class="min-w-[120px] text-center text-sm font-semibold text-slate-800">{{ monthName(month) }} {{ year }}</span>
                        <button class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-600 hover:bg-slate-200" @click="changeMonth(1)">&rarr;</button>
                    </div>
                </div>

                <!-- Tab bar -->
                <div class="flex border-b border-slate-200">
                    <button
                        v-for="tb in TABS" :key="tb.id" type="button"
                        class="relative flex items-center gap-2 px-5 py-3 text-sm font-medium transition-colors"
                        :style="{ backgroundColor: activeTab === tb.id ? 'var(--tab-active-bg, white)' : 'var(--tab-bg, #f8fafc)', color: activeTab === tb.id ? '#6366f1' : '#94a3b8' }"
                        @click="activeTab = tb.id"
                    >
                        <span>{{ tb.icon }}</span>{{ t(tb.labelKey) }}
                        <span v-if="activeTab === tb.id" class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-500"></span>
                    </button>
                </div>

                <!-- ═══ CHART TAB ═══ -->
                <div v-if="activeTab === 'chart'" class="p-6">
                    <MvrChart v-if="chartData.length > 0" :data="chartData" />
                    <p v-else class="py-12 text-center text-sm text-slate-400">No daily data for {{ monthName(month) }} {{ year }}</p>
                </div>

                <!-- ═══ DATA TAB (daily plan/fact) ═══ -->
                <div v-else-if="activeTab === 'data'">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="px-5 py-2.5 w-16">День</th>
                                <th class="px-5 py-2.5 w-12"></th>
                                <th class="px-5 py-2.5 text-right">{{ t('finance.col.actual') }}</th>
                                <th class="px-5 py-2.5 text-right">{{ t('finance.col.target') }}</th>
                                <th class="px-5 py-2.5 text-right">{{ t('finance.col.diff') }}</th>
                                <th class="w-16 px-5 py-2.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="d in dailyEntries" :key="d.date"
                                class="hover:bg-slate-50"
                                :class="{ 'bg-slate-50/50': isWeekend(d.date) }"
                            >
                                <td class="px-5 py-2 font-mono text-xs text-slate-700">{{ formatDay(d.date) }}</td>
                                <td class="px-5 py-2 text-[10px] text-slate-400">{{ weekday(d.date) }}</td>
                                <template v-if="d.id !== null && editingDailyId === d.id">
                                    <td class="px-5 py-1.5"><input v-model.number="dailyForm.fact" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-xs" /></td>
                                    <td class="px-5 py-1.5"><input v-model.number="dailyForm.plan" type="number" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-xs" /></td>
                                    <td></td>
                                    <td class="px-5 py-1.5 text-right">
                                        <button class="mr-1 text-xs text-indigo-600" @click="saveDaily(d)">✓</button>
                                        <button class="text-xs text-slate-400" @click="editingDailyId = null">✕</button>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-5 py-2 text-right text-xs" :class="Number(d.fact) >= Number(d.plan) ? 'text-emerald-600 font-medium' : 'text-slate-600'">
                                        {{ Number(d.fact) > 0 ? `$${Number(d.fact).toLocaleString()}` : '---' }}
                                    </td>
                                    <td class="px-5 py-2 text-right text-xs text-slate-500">${{ Number(d.plan).toLocaleString() }}</td>
                                    <td class="px-5 py-2 text-right text-xs font-medium" :class="diffClass(d.plan, d.fact)">
                                        {{ formatDiff(d.plan, d.fact) }}
                                    </td>
                                    <td class="px-5 py-2 text-right">
                                        <button v-if="can.create && !isHoldingView" class="text-slate-400 hover:text-indigo-600" @click="startEditDaily(d)">
                                            <svg class="inline h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                        </button>
                                    </td>
                                </template>
                            </tr>
                            <tr v-if="dailyEntries.length === 0">
                                <td colspan="6" class="px-5 py-8 text-center text-xs text-slate-400">No daily data for {{ monthName(month) }} {{ year }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ═══ DEVIATION TAB (monthly plan vs fact) ═══ -->
                <div v-else-if="activeTab === 'deviation'" class="p-6">
                    <h2 class="mb-1 text-base font-semibold text-slate-800">{{ t('finance.deviation_title') }}</h2>
                    <p class="mb-5 text-sm text-slate-500">{{ t('finance.deviation_sub') }}</p>

                    <!-- Bar chart: deviation % per month -->
                    <div class="mb-6 overflow-x-auto rounded-lg border border-slate-200 bg-slate-50/40 p-3">
                        <svg :viewBox="`0 0 ${CW} ${CH}`" class="w-full max-w-full" preserveAspectRatio="xMidYMid meet">
                            <!-- 0% reference line -->
                            <line :x1="C_PAD_LEFT" :y1="zeroY" :x2="CW - C_PAD_RIGHT" :y2="zeroY" stroke="#94a3b8" stroke-width="1" />
                            <text :x="C_PAD_LEFT - 6" :y="zeroY + 4" text-anchor="end" class="fill-slate-400 text-[10px]">0%</text>
                            <text :x="C_PAD_LEFT - 6" :y="C_PAD_TOP + 8" text-anchor="end" class="fill-slate-400 text-[10px]">+{{ Math.round(maxAbsPct) }}%</text>
                            <text :x="C_PAD_LEFT - 6" :y="C_PAD_TOP + chartInnerH" text-anchor="end" class="fill-slate-400 text-[10px]">−{{ Math.round(maxAbsPct) }}%</text>

                            <g v-for="(m, i) in monthlyDeviations" :key="m.month">
                                <rect
                                    v-if="m.has_data"
                                    :x="colX(i) - barW() / 2"
                                    :y="barRect(m).y"
                                    :width="barW()"
                                    :height="Math.max(barRect(m).h, 2)"
                                    :fill="deviationColor(m.diff_pct)"
                                    :stroke="deviationColor(m.diff_pct, 1.4)"
                                    stroke-width="1"
                                    rx="3"
                                >
                                    <title>{{ monthName(m.month) }}: План ${{ Math.round(m.plan).toLocaleString() }} · Факт ${{ Math.round(m.fact).toLocaleString() }} · {{ m.diff_pct >= 0 ? '+' : '' }}{{ m.diff_pct }}%</title>
                                </rect>
                                <text
                                    v-if="m.has_data"
                                    :x="colX(i)"
                                    :y="m.diff_pct >= 0 ? barRect(m).y - 5 : barRect(m).y + barRect(m).h + 12"
                                    text-anchor="middle"
                                    class="text-[10px] font-semibold"
                                    :fill="m.diff_pct >= 0 ? '#047857' : '#be123c'"
                                >
                                    {{ m.diff_pct >= 0 ? '+' : '' }}{{ m.diff_pct }}%
                                </text>
                                <text
                                    :x="colX(i)"
                                    :y="CH - 8"
                                    text-anchor="middle"
                                    class="fill-slate-500 text-[11px]"
                                >
                                    {{ monthShort(m.month) }}
                                </text>
                            </g>
                        </svg>
                    </div>

                    <!-- Cards grid: per-month details -->
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        <div
                            v-for="m in monthlyDeviations"
                            :key="m.month"
                            class="rounded-lg border p-3 transition-all"
                            :style="{ backgroundColor: m.has_data ? deviationColor(m.diff_pct, 0.6) : '#f8fafc', borderColor: m.has_data ? deviationColor(m.diff_pct, 1.5) : '#e2e8f0' }"
                        >
                            <div class="mb-2 flex items-baseline justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-700">{{ monthName(m.month) }}</span>
                                <span v-if="m.has_data" class="text-base font-bold" :class="deviationTextClass(m.diff_pct)">
                                    {{ m.diff_pct >= 0 ? '+' : '' }}{{ m.diff_pct }}%
                                </span>
                            </div>
                            <template v-if="m.has_data">
                                <div class="grid grid-cols-2 gap-1.5 text-[11px]">
                                    <div>
                                        <div class="text-slate-500">{{ t('finance.col.target') }}</div>
                                        <div class="font-medium text-slate-700">${{ Math.round(m.plan).toLocaleString() }}</div>
                                    </div>
                                    <div>
                                        <div class="text-slate-500">{{ t('finance.col.actual') }}</div>
                                        <div class="font-semibold text-slate-900">${{ Math.round(m.fact).toLocaleString() }}</div>
                                    </div>
                                </div>
                                <div class="mt-2 border-t border-white/40 pt-1.5 text-sm font-bold" :class="deviationTextClass(m.diff_pct)">
                                    {{ m.diff >= 0 ? '+' : '−' }}${{ Math.round(Math.abs(m.diff)).toLocaleString() }}
                                </div>
                            </template>
                            <div v-else class="py-2 text-[11px] text-slate-400">{{ t('finance.deviation_no_data') }}</div>
                        </div>
                    </div>
                </div>

                <!-- ═══ PLAN TAB (monthly targets) ═══ -->
                <div v-else>
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3">
                        <h2 class="text-sm font-semibold text-slate-800">{{ t('finance.col.target') }} — {{ year }}</h2>
                        <button v-if="can.create" class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700" @click="openAdd">+ {{ t('finance.add_entry') }}</button>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                            <tr>
                                <th v-if="isHoldingView" class="px-5 py-3">{{ t('finance.col.unit') }}</th>
                                <th class="px-5 py-3">{{ t('finance.col.month') }}</th>
                                <th class="px-5 py-3 text-right">{{ t('finance.col.target') }}</th>
                                <th class="px-5 py-3 text-right">{{ t('finance.daily_target') }}</th>
                                <th class="px-5 py-3 text-center">{{ t('finance.col.days') }}</th>
                                <th class="w-24 px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="showAddForm" class="bg-indigo-50/40">
                                <td v-if="isHoldingView" class="px-5 py-2">
                                    <SearchableSelect v-model="addForm.unit_id" :options="unitSelectOptions" size="sm" />
                                </td>
                                <td class="px-5 py-2"><SearchableSelect v-model="addForm.month" :options="monthSelectOptions" size="sm" /></td>
                                <td class="px-5 py-2"><input v-model.number="addForm.target" type="number" placeholder="0" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" /></td>
                                <td class="px-5 py-2 text-right text-xs text-indigo-600">${{ addForm.target && addForm.target > 0 ? Math.round(addForm.target / daysIn(addForm.month, year)).toLocaleString() : '0' }}/д</td>
                                <td class="px-5 py-2 text-center text-xs text-slate-400">{{ daysIn(addForm.month, year) }}</td>
                                <td class="px-5 py-2 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button class="text-emerald-600 hover:text-emerald-700" title="Сохранить" @click="submitAdd">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        </button>
                                        <button class="text-slate-400 hover:text-slate-600" title="Отмена" @click="showAddForm = false">
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="entry in entries" :key="entry.id" class="hover:bg-slate-50">
                                <td v-if="isHoldingView" class="px-5 py-2.5">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-700">
                                        <span class="inline-block h-2 w-2 rounded-full" :style="{ backgroundColor: unitColor(entry.unit_id) }"></span>
                                        {{ unitName(entry.unit_id) }}
                                    </span>
                                </td>
                                <td class="px-5 py-2.5 font-medium text-slate-700">{{ monthName(entry.month) }}</td>
                                <template v-if="editingId === entry.id">
                                    <td class="px-5 py-2"><input v-model.number="editForm.target" type="number" placeholder="0" class="w-full rounded border border-slate-300 px-2 py-1 text-right text-sm" /></td>
                                    <td class="px-5 py-2 text-right text-xs text-indigo-600">${{ editForm.target && editForm.target > 0 ? Math.round(editForm.target / daysIn(entry.month, year)).toLocaleString() : '0' }}/д</td>
                                    <td class="px-5 py-2 text-center text-xs text-slate-400">{{ daysIn(entry.month, year) }}</td>
                                    <td class="px-5 py-2 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button class="text-emerald-600 hover:text-emerald-700" title="Сохранить" @click="savePlan(entry)">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            </button>
                                            <button class="text-slate-400 hover:text-slate-600" title="Отмена" @click="editingId = null">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </template>
                                <template v-else>
                                    <td class="px-5 py-2.5 text-right font-semibold text-slate-800">${{ Number(entry.target).toLocaleString() }}</td>
                                    <td class="px-5 py-2.5 text-right text-xs text-indigo-600">${{ Math.round(Number(entry.target) / daysIn(entry.month, year)).toLocaleString() }}/д</td>
                                    <td class="px-5 py-2.5 text-center text-xs text-slate-400">{{ daysIn(entry.month, year) }}</td>
                                    <td class="px-5 py-2.5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button v-if="can.create" class="text-slate-400 hover:text-indigo-600" title="Редактировать" @click="startEditPlan(entry)">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                            </button>
                                            <button v-if="can.delete" class="text-slate-400 hover:text-rose-600" title="Удалить" @click="deletingId = entry.id">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            <tr v-if="entries.length === 0 && !showAddForm"><td :colspan="isHoldingView ? 6 : 5" class="px-5 py-12 text-center text-sm text-slate-400">No data</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmDialog :show="deletingId !== null" :title="t('finance.title')" :message="t('risks.delete_confirm')" @confirm="doDelete" @cancel="deletingId = null" />
    </AppLayout>
</template>
