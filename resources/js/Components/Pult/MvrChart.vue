<script setup lang="ts">
import { computed, ref } from 'vue';

interface MvrDailyPoint {
    date: string;
    plan: number | string;
    fact: number | string;
}

const props = defineProps<{
    data: MvrDailyPoint[];
}>();

// Chart dimensions
const W = 800;
const H = 320;
const PAD_LEFT = 60;
const PAD_RIGHT = 70;
const PAD_TOP = 20;
const PAD_BOTTOM = 40;
const chartW = W - PAD_LEFT - PAD_RIGHT;
const chartH = H - PAD_TOP - PAD_BOTTOM;

const parsed = computed(() =>
    props.data.map((d) => ({
        date: d.date,
        plan: Number(d.plan),
        fact: Number(d.fact),
    })),
);

const maxVal = computed(() => {
    const vals = parsed.value.flatMap((d) => [d.plan, d.fact].filter((v) => v > 0));
    return vals.length > 0 ? Math.max(...vals) * 1.15 : 100;
});

function x(index: number): number {
    if (parsed.value.length <= 1) return PAD_LEFT;
    return PAD_LEFT + (index / (parsed.value.length - 1)) * chartW;
}

function y(value: number): number {
    return PAD_TOP + chartH - (value / maxVal.value) * chartH;
}

const planPoints = computed(() =>
    parsed.value.map((d, i) => `${x(i)},${y(d.plan)}`).join(' '),
);

const factPoints = computed(() => {
    const pts = parsed.value
        .map((d, i) => ({ d, i }))
        .filter(({ d }) => d.fact > 0);
    return pts.map(({ d, i }) => `${x(i)},${y(d.fact)}`).join(' ');
});

const yTicks = computed(() => {
    const max = maxVal.value;
    const rawStep = max / 5;
    const magnitude = Math.pow(10, Math.floor(Math.log10(rawStep)));
    const step = Math.ceil(rawStep / magnitude) * magnitude;
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

function formatDayLabel(iso: string): string {
    const d = new Date(iso);
    return `${d.getDate()}.${String(d.getMonth() + 1).padStart(2, '0')}`;
}

const xLabelEvery = computed(() => {
    const n = parsed.value.length;
    if (n <= 14) return 1;
    if (n <= 31) return 2;
    if (n <= 62) return 5;
    return Math.ceil(n / 12);
});

// Plan annotation — current month's plan value, anchored at right end of plan line.
const planLabel = computed(() => {
    if (parsed.value.length === 0) return null;
    const last = parsed.value[parsed.value.length - 1];
    return {
        x: x(parsed.value.length - 1),
        y: y(last.plan),
        text: `$${last.plan.toLocaleString()}`,
    };
});

// Tooltip
const tooltip = ref<{ x: number; y: number; date: string; plan: string; fact: string } | null>(null);

function showTooltip(d: { date: string; plan: number; fact: number }, idx: number) {
    tooltip.value = {
        x: x(idx),
        y: Math.min(y(d.plan), d.fact > 0 ? y(d.fact) : y(d.plan)) - 10,
        date: formatDayLabel(d.date),
        plan: `$${d.plan.toLocaleString()}`,
        fact: d.fact > 0 ? `$${d.fact.toLocaleString()}` : '---',
    };
}

function hideTooltip() {
    tooltip.value = null;
}
</script>

<template>
    <div v-if="parsed.length === 0" class="py-12 text-center text-sm text-slate-400">
        No daily data
    </div>
    <div v-else class="w-full overflow-x-auto">
        <svg :viewBox="`0 0 ${W} ${H}`" class="w-full max-w-full" preserveAspectRatio="xMidYMid meet">
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

            <!-- Plan line (dashed red) -->
            <polyline
                v-if="parsed.length > 0"
                :points="planPoints"
                fill="none"
                stroke="#ef4444"
                stroke-width="2"
                stroke-dasharray="6 4"
            />
            <!-- Plan value label at right end -->
            <text
                v-if="planLabel"
                :x="planLabel.x + 8"
                :y="planLabel.y + 4"
                class="fill-red-500 text-[11px] font-semibold"
            >
                {{ planLabel.text }}
            </text>
            <!-- Fact line (solid indigo) -->
            <polyline
                v-if="factPoints"
                :points="factPoints"
                fill="none"
                stroke="#6366f1"
                stroke-width="2.5"
                stroke-linejoin="round"
            />

            <!-- Plan dots -->
            <circle
                v-for="(d, i) in parsed"
                :key="'p' + i"
                :cx="x(i)"
                :cy="y(d.plan)"
                r="2.5"
                fill="#ef4444"
                class="cursor-pointer"
                @mouseenter="showTooltip(d, i)"
                @mouseleave="hideTooltip"
            />
            <!-- Fact dots -->
            <circle
                v-for="(d, i) in parsed"
                v-show="d.fact > 0"
                :key="'f' + i"
                :cx="x(i)"
                :cy="y(d.fact)"
                r="3"
                fill="#6366f1"
                class="cursor-pointer"
                @mouseenter="showTooltip(d, i)"
                @mouseleave="hideTooltip"
            />

            <!-- X-axis day labels -->
            <text
                v-for="(d, i) in parsed"
                v-show="i % xLabelEvery === 0"
                :key="'x' + i"
                :x="x(i)"
                :y="H - 8"
                text-anchor="middle"
                class="fill-slate-500 text-[10px]"
            >
                {{ formatDayLabel(d.date) }}
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
                    {{ tooltip.date }}
                </text>
                <text :x="tooltip.x" :y="tooltip.y - 20" text-anchor="middle" class="fill-indigo-300 text-[10px]">
                    Факт: {{ tooltip.fact }}
                </text>
                <text :x="tooltip.x" :y="tooltip.y - 8" text-anchor="middle" class="fill-red-300 text-[10px]">
                    План: {{ tooltip.plan }}
                </text>
            </g>
        </svg>

        <!-- Legend -->
        <div class="mt-2 flex items-center gap-5 text-xs text-slate-500">
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-0.5 w-5 bg-indigo-500"></span>
                Факт
            </span>
            <span class="flex items-center gap-1.5">
                <span class="inline-block h-0.5 w-5 border-t-2 border-dashed border-red-500"></span>
                План
            </span>
        </div>
    </div>
</template>
