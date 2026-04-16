<script setup lang="ts">
import { useTranslations } from '../../Composables/useTranslations';

interface Props {
    show: boolean;
    title: string;
    message: string;
    confirmLabel?: string;
    cancelLabel?: string;
    variant?: 'danger' | 'default';
}

withDefaults(defineProps<Props>(), {
    confirmLabel: undefined,
    cancelLabel: undefined,
    variant: 'default',
});

defineEmits<{
    confirm: [];
    cancel: [];
}>();

const { t } = useTranslations();
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                @click.self="$emit('cancel')"
            >
                <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">{{ message }}</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            @click="$emit('cancel')"
                        >
                            {{ cancelLabel ?? t('modal.btn.cancel') }}
                        </button>
                        <button
                            type="button"
                            :class="[
                                'rounded-md px-4 py-2 text-sm font-medium text-white',
                                variant === 'danger'
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-indigo-600 hover:bg-indigo-700',
                            ]"
                            @click="$emit('confirm')"
                        >
                            {{ confirmLabel ?? t('modal.btn.save') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
