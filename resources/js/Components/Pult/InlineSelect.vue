<script setup lang="ts">
import { ref, watch } from 'vue';

interface Option {
    value: string | number;
    label: string;
    color?: string;
}

interface Props {
    modelValue: string | number;
    options: Option[];
    displayColor?: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{ 'update:modelValue': [value: string | number] }>();

const editing = ref(false);
const localValue = ref(props.modelValue);

watch(() => props.modelValue, (v) => { localValue.value = v; });

function open() {
    localValue.value = props.modelValue;
    editing.value = true;
}

function commit() {
    editing.value = false;
    if (localValue.value !== props.modelValue) {
        emit('update:modelValue', localValue.value);
    }
}

const currentLabel = () => {
    const opt = props.options.find((o) => o.value === props.modelValue);
    return opt?.label ?? String(props.modelValue);
};

const currentColor = () => {
    const opt = props.options.find((o) => o.value === props.modelValue);
    return opt?.color ?? props.displayColor ?? '#6b7280';
};
</script>

<template>
    <div class="inline-block">
        <select
            v-if="editing"
            v-model="localValue"
            class="rounded-md border border-indigo-400 bg-white px-2 py-0.5 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-500"
            @change="commit"
            @blur="commit"
            @keydown.escape="editing = false"
        >
            <option v-for="opt in options" :key="opt.value" :value="opt.value">
                {{ opt.label }}
            </option>
        </select>
        <button
            v-else
            type="button"
            class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-medium transition-colors hover:ring-1 hover:ring-indigo-300"
            :style="{
                backgroundColor: currentColor() + '18',
                color: currentColor(),
            }"
            @click.stop="open"
            :title="'Click to edit'"
        >
            {{ currentLabel() }}
            <svg class="h-3 w-3 opacity-40" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</template>
