<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Option {
    value: string | number;
    label: string;
    color?: string;
}

interface Props {
    modelValue: string | number;
    options: Option[];
    displayColor?: string;
    searchThreshold?: number;
}

const props = withDefaults(defineProps<Props>(), {
    searchThreshold: 8,
});

const emit = defineEmits<{ 'update:modelValue': [value: string | number] }>();

const rootEl = ref<HTMLElement | null>(null);
const searchEl = ref<HTMLInputElement | null>(null);
const open = ref(false);
const query = ref('');
const highlightedIndex = ref(-1);

const showSearch = computed(() => props.options.length > props.searchThreshold);

const filtered = computed<Option[]>(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter(o => o.label.toLowerCase().includes(q));
});

const currentLabel = computed(() => {
    const opt = props.options.find((o) => o.value === props.modelValue);
    return opt?.label ?? String(props.modelValue);
});

const currentColor = computed(() => {
    const opt = props.options.find((o) => o.value === props.modelValue);
    return opt?.color ?? props.displayColor ?? '#6b7280';
});

function openDropdown() {
    if (open.value) return;
    open.value = true;
    query.value = '';
    highlightedIndex.value = Math.max(0, filtered.value.findIndex(o => o.value === props.modelValue));
    nextTick(() => { if (showSearch.value) searchEl.value?.focus(); });
}

function commit(opt: Option) {
    open.value = false;
    query.value = '';
    if (opt.value !== props.modelValue) emit('update:modelValue', opt.value);
}

function onKeydown(e: KeyboardEvent) {
    if (!open.value) return;
    if (e.key === 'Escape') { e.preventDefault(); open.value = false; return; }
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (filtered.value.length > 0) highlightedIndex.value = (highlightedIndex.value + 1) % filtered.value.length;
        return;
    }
    if (e.key === 'ArrowUp') {
        e.preventDefault();
        if (filtered.value.length > 0) highlightedIndex.value = (highlightedIndex.value - 1 + filtered.value.length) % filtered.value.length;
        return;
    }
    if (e.key === 'Enter') {
        e.preventDefault();
        const opt = filtered.value[highlightedIndex.value];
        if (opt) commit(opt);
    }
}

function onDocumentClick(e: MouseEvent) {
    if (!rootEl.value) return;
    if (!rootEl.value.contains(e.target as Node)) open.value = false;
}

watch(filtered, () => { highlightedIndex.value = Math.min(highlightedIndex.value, Math.max(0, filtered.value.length - 1)); });

onMounted(() => document.addEventListener('click', onDocumentClick));
onBeforeUnmount(() => document.removeEventListener('click', onDocumentClick));
</script>

<template>
    <div ref="rootEl" class="relative inline-block" @keydown="onKeydown">
        <button
            type="button"
            class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium transition-colors hover:ring-1 hover:ring-indigo-300"
            :style="{ backgroundColor: currentColor + '18', color: currentColor }"
            title="Click to edit"
            @click.stop="openDropdown"
        >
            {{ currentLabel }}
            <svg class="h-3 w-3 opacity-40" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute left-0 z-40 mt-1 w-max min-w-[160px] overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800"
            @click.stop
        >
            <div v-if="showSearch" class="border-b border-slate-200 p-1.5 dark:border-slate-700">
                <input
                    ref="searchEl"
                    v-model="query"
                    type="text"
                    placeholder="Поиск…"
                    class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                    @keydown.stop="onKeydown"
                />
            </div>
            <ul class="max-h-56 overflow-y-auto py-1 text-xs">
                <li
                    v-for="(opt, i) in filtered"
                    :key="`${opt.value}-${i}`"
                    class="flex cursor-pointer items-center gap-2 px-3 py-1 text-slate-700 hover:bg-indigo-50 dark:text-slate-200 dark:hover:bg-indigo-900/30"
                    :class="[
                        i === highlightedIndex ? 'bg-indigo-50 dark:bg-indigo-900/30' : '',
                        opt.value === modelValue ? 'font-medium text-indigo-700 dark:text-indigo-300' : '',
                    ]"
                    @click="commit(opt)"
                    @mouseenter="highlightedIndex = i"
                >
                    <span v-if="opt.color" class="inline-block h-2 w-2 shrink-0 rounded-full" :style="{ backgroundColor: opt.color }"></span>
                    <span class="truncate">{{ opt.label }}</span>
                </li>
                <li v-if="filtered.length === 0" class="px-3 py-2 text-center text-slate-400">
                    Ничего не найдено
                </li>
            </ul>
        </div>
    </div>
</template>
