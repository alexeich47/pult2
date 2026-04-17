<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

interface Option {
    value: string | number | null;
    label: string;
    group?: string;
    color?: string;
}

interface Props {
    modelValue: string | number | null;
    options: Option[];
    placeholder?: string;
    searchPlaceholder?: string;
    disabled?: boolean;
    required?: boolean;
    searchThreshold?: number; // auto-show search when options.length > threshold; default 8
    fullWidth?: boolean;
    size?: 'sm' | 'md';
}

const props = withDefaults(defineProps<Props>(), {
    placeholder: '—',
    searchPlaceholder: 'Поиск…',
    disabled: false,
    required: false,
    searchThreshold: 8,
    fullWidth: true,
    size: 'md',
});

const emit = defineEmits<{ 'update:modelValue': [value: string | number | null] }>();

const open = ref(false);
const query = ref('');
const highlightedIndex = ref(-1);

const rootEl = ref<HTMLElement | null>(null);
const searchEl = ref<HTMLInputElement | null>(null);
const listEl = ref<HTMLElement | null>(null);

const selectedOption = computed(() => props.options.find(o => o.value === props.modelValue) ?? null);

const showSearch = computed(() => props.options.length > props.searchThreshold);

const filtered = computed<Option[]>(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return props.options;
    return props.options.filter(o => {
        const hay = (o.label + ' ' + (o.group ?? '')).toLowerCase();
        return hay.includes(q);
    });
});

// Group filtered options by `group` field if any option has one.
const grouped = computed(() => {
    const hasGroups = props.options.some(o => o.group);
    if (!hasGroups) return [{ name: '', items: filtered.value }];
    const map = new Map<string, Option[]>();
    for (const o of filtered.value) {
        const g = o.group ?? '';
        if (!map.has(g)) map.set(g, []);
        map.get(g)!.push(o);
    }
    return Array.from(map.entries()).map(([name, items]) => ({ name, items }));
});

function toggleOpen() {
    if (props.disabled) return;
    open.value = !open.value;
    if (open.value) {
        query.value = '';
        highlightedIndex.value = Math.max(0, filtered.value.findIndex(o => o.value === props.modelValue));
        nextTick(() => {
            if (showSearch.value) searchEl.value?.focus();
        });
    }
}

function selectOption(opt: Option) {
    emit('update:modelValue', opt.value);
    open.value = false;
    query.value = '';
}

function onKeydown(e: KeyboardEvent) {
    if (props.disabled) return;
    if (!open.value) {
        if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
            e.preventDefault();
            toggleOpen();
        }
        return;
    }
    if (e.key === 'Escape') {
        e.preventDefault();
        open.value = false;
        return;
    }
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
        if (opt) selectOption(opt);
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
    <div ref="rootEl" class="relative" :class="fullWidth ? 'w-full' : 'inline-block'" @keydown="onKeydown">
        <button
            type="button"
            :disabled="disabled"
            class="flex w-full items-center justify-between gap-2 rounded-md border border-slate-300 bg-white text-left shadow-sm transition-colors hover:border-slate-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:opacity-60 dark:border-slate-600 dark:bg-slate-900"
            :class="[
                size === 'sm' ? 'px-2 py-1 text-xs' : 'px-3 py-2 text-sm',
                open ? 'border-indigo-500 ring-1 ring-indigo-500' : '',
            ]"
            @click="toggleOpen"
        >
            <span v-if="selectedOption" class="flex min-w-0 items-center gap-2 truncate">
                <span
                    v-if="selectedOption.color"
                    class="inline-block h-2 w-2 shrink-0 rounded-full"
                    :style="{ backgroundColor: selectedOption.color }"
                ></span>
                <span class="truncate text-slate-900 dark:text-slate-100">{{ selectedOption.label }}</span>
            </span>
            <span v-else class="truncate text-slate-400">{{ placeholder }}</span>
            <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>

        <div
            v-if="open"
            class="absolute z-40 mt-1 w-full min-w-[200px] overflow-hidden rounded-md border border-slate-200 bg-white shadow-lg dark:border-slate-700 dark:bg-slate-800"
        >
            <div v-if="showSearch" class="border-b border-slate-200 p-1.5 dark:border-slate-700">
                <input
                    ref="searchEl"
                    v-model="query"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="w-full rounded border border-slate-200 bg-white px-2 py-1 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900"
                    @keydown.stop="onKeydown"
                />
            </div>
            <ul ref="listEl" class="max-h-64 overflow-y-auto py-1 text-sm">
                <template v-for="group in grouped" :key="group.name">
                    <li v-if="group.name" class="px-3 pt-2 pb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                        {{ group.name }}
                    </li>
                    <li
                        v-for="(opt, i) in group.items"
                        :key="`${opt.value}-${i}`"
                        class="flex cursor-pointer items-center gap-2 px-3 py-1.5 text-slate-700 hover:bg-indigo-50 dark:text-slate-200 dark:hover:bg-indigo-900/30"
                        :class="[
                            filtered.indexOf(opt) === highlightedIndex ? 'bg-indigo-50 dark:bg-indigo-900/30' : '',
                            opt.value === modelValue ? 'font-medium text-indigo-700 dark:text-indigo-300' : '',
                        ]"
                        @click="selectOption(opt)"
                        @mouseenter="highlightedIndex = filtered.indexOf(opt)"
                    >
                        <span v-if="opt.color" class="inline-block h-2 w-2 shrink-0 rounded-full" :style="{ backgroundColor: opt.color }"></span>
                        <span class="truncate">{{ opt.label }}</span>
                        <svg v-if="opt.value === modelValue" class="ml-auto h-4 w-4 shrink-0 text-indigo-600" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 010 1.42l-8 8a1 1 0 01-1.42 0l-4-4a1 1 0 111.42-1.42L8 12.58l7.29-7.29a1 1 0 011.42 0z" clip-rule="evenodd"/></svg>
                    </li>
                </template>
                <li v-if="filtered.length === 0" class="px-3 py-3 text-center text-xs text-slate-400">
                    Ничего не найдено
                </li>
            </ul>
        </div>
    </div>
</template>
