<script setup lang="ts">
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '../../types';

interface MvrDataPoint {
    month: number;
    target: number | string;
    actual: number | string;
}

const props = defineProps<{
    data: MvrDataPoint[];
}>();

const page = usePage<PageProps>();

const MONTH_NAMES: Record<string, string[]> = {
    ru: ['Янв', 'Фев', 'Мар', 'Апр', 'Май', 'Июн', 'Июл', 'Авг', 'Сен', 'Окт', 'Ноя', 'Дек'],
    uk: ['Січ', 'Лют', 'Бер', 'Кві', 'Тра', 'Чер', 'Лип', 'Сер', 'Вер', 'Жов', 'Лис', 'Гру'],
    en: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
};

const monthNames = computed(() => MONTH_NAMES[page.props.locale] ?? MONTH_NAMES.en);

// Chart dimensions
const W = 700;
const H = 320;
const PAD_LEFT = 60;
const PAD_RIGHT = 20;
const PAD_TOP = 20;
const PAD_BOTTOM = 40;
const chartW = W - PAD_LEFT - PAD_RIGHT;
const chartH = H - PAD_TOP - PAD_BOTTOM;

const parsed = computed(() =>
    props.data.map((d) => ({
        month: d.month,
        target: Number(d.target),
        actual: Number(d.actual),
    })),
);

const maxVal = computed(() => {
    const vals = parsed.value.flatMap((d) => [d.target, d.actual].filter((v) => v > 0));
    return vals.length > 0 ? Math.max(...vals) * 1.1 : 100;
});

function x(index: number): number {
    if (parsed.value.length <= 1) return PAD_LEFT;
    return PAD_LEFT + (index / (parsed.value.length - 1)) * chartW;
}

function y(value: number): number {
    return PAD_TOP + chartH - (value / maxVal.value) * chartH;
}

const targetPoints = computed(() =>
    parsed.value.map((d, i) => `${x(i)},${y(d.target)}`).join(' '),
);

const actualPoints = computed(() => {
    const pts = parsed.value.filter((d) => d.actual > 0);
    return pts.map((d, i) => {
        const idx = parsed.value.indexOf(d);
        return `${x(idx)},${y(d.actual)}`;
    }).join(' ');
});

const yTicks = computed(() => {
    const max = maxVal.value;
    const step = Math.ceil(max / 5 / 10000) * 10000;
    const ticks: number[] = [];
    for (let v = 0; v <= max; v += step) {
        ticks.push(v);
    }
    return ticks;
});

function formatK(v: number): string {
    if (v >= 1000) return `$${(v / 1000).toFixed(0)}k`;
    return `$${v}`;
}

// Tooltip
const tooltip = ref<{ x: number; y: number; month: string; target: string; actual: string } | null>(null);

function showTooltip(d: { month: number; target: number; actual: number }, idx: number) {
    tooltip.value = {
        x: x(idx),
        y: Math.min(y(d.target), y(d.actual)) - 10,
        month: monthNames.value[d.month - 1],
        target: `$${d.target.toLocaleString()}`,
        actual: d.actual > 0 ? `$${d.actual.toLocaleString()}` : '---',
    };
}

function hideTooltip() {
    tooltip.value = null;
}
</script>

<template>
    <div class="w-full overflow-x-auto">
        <svg :viewBox="`0 0 ${W} ${H}`" class="w-full max-w-[700px]" preserveAspectRatio="xMidYMid meet">
            <!-- Y-axis grid lines -->
            <line
                v-for="tick in yTicks"
                :key="tick"
                :x1="PAD_LEFT"
                :x2="W - PAD_RIGHT"
                :y1="y(tick)"
                :y2="y(tick)"
                stroke="#e2e8f0"
                stroke-width="1"
            />
            <!-- Y-axis labels -->
            <text
                v-for="tick in yTicks"
                :key="'l' + tick"
                :x="PAD_LEFT - 8"
                :y="y(tick) + 4"
                text-anchor="end"
                class="fill-slate-400 text-[11px]"
            >
                {{ formatK(tick) }}
            </text>

            <!-- Target line (dashed) -->
            <polyline
                v-if="parsed.length > 0"
                :points="targetPoints"
                fill="none"
                stroke="#94a3b8"
                stroke-width="2"
                stroke-dasharray="6 4"
            />
            <!-- Actual line (solid) -->
            <polyline
                v-if="actualPoints"
                :points="actualPoints"
                fill="none"
                stroke="#6366f1"
                stroke-width="2.5"
                stroke-linejoin="round"
            />

            <!-- Target dots -->
            <circle
                v-for="(d, i) in parsed"
                :key="'t' + i"
                :cx="x(i)"
                :cy="y(d.target)"
                r="3.5"
                fill="#94a3b8"
                class="cursor-pointer"
                @mouseenter="showTooltip(d, i)"
                @mouseleave="hideTooltip"
            />
            <!-- Actual dots -->
            <circle
                v-for="(d, i) in parsed.filter((p) => p.actual > 0)"
                :key="'a' + i"
                :cx="x(parsed.indexOf(d))"
                :cy="y(d.actual)"
                r="4"
                fill="#6366f1"
                class="cursor-pointer"
                @mouseenter="showTooltip(d, parsed.indexOf(d))"
                @mouseleave="hideTooltip"
            />

            <!-- X-axis month labels -->
            <text
                v-for="(d, i) in parsed"
                :key="'m' + i"
                :x="x(i)"
                :y="H - 8"
                text-anchor="middle"
                class="fill-slate-500 text-[11px]"
            >
                {{ monthNames[d.month - 1] }}
            </text>

            <!-- Tooltip -->
            <g v-if="tooltip">
                <rect
                    :x="tooltip.x - 55"
                    :y="tooltip.y - 50"
                    width="110"
                    height="46"
                    rx="6"
                    fill="#1e293b"
                    opacity="0.95"
                />
                <text :x="tooltip.x" :y="tooltip.y - 34" text-anchor="middle" class="fill-white text-[11px] font-semibold">
                    {{ tooltip.month }}
                </text>
                <text :x="tooltip.x" :y="tooltip.y - 20" text-anchor="middle" class="fill-slate-300 text-[10px]">
                    Plan: {{ tooltip.target }}
                </text>
                <text :x="tooltip.x" :y="tooltip.y - 8" text-anchor="middle" class="fill-indigo-300 text-[10px]">
                    Fact: {{ tooltip.actual }}
                </text>
            </g>
        </svg>

        <!-- Legend -->
        <div class="mt-2 flex items-center gap-5 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-0.5 w-5 border-t-2 border-dashed border-slate-400"></span>
                Target
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-0.5 w-5 bg-indigo-500"></span>
                Actual
            </span>
        </div>
    </div>
</template>
