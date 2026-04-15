<script setup lang="ts">
interface Props {
    color?: string;
    variant?: 'solid' | 'soft';
}

const props = withDefaults(defineProps<Props>(), {
    color: '#6b7280',
    variant: 'soft',
});

const style = {
    '--badge-color': props.color,
};
</script>

<template>
    <span
        :class="[
            'inline-flex items-center gap-1.5 rounded-md px-2 py-0.5 text-xs font-medium',
            variant === 'solid'
                ? 'text-white'
                : 'bg-[color-mix(in_oklab,var(--badge-color)_18%,transparent)] text-[var(--badge-color)]',
        ]"
        :style="variant === 'solid' ? { backgroundColor: color } : style"
    >
        <slot />
    </span>
</template>
