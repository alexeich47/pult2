<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import type { PageProps } from '../types';
import Sidebar from '../Components/Pult/Sidebar.vue';

const page = usePage<PageProps>();

const flash = computed(() => page.props.flash);

// Simple flash-dismiss effect
let flashTimer: ReturnType<typeof setTimeout> | null = null;
watch(
    () => flash.value.success,
    (value) => {
        if (value && flashTimer) clearTimeout(flashTimer);
    },
);
</script>

<template>
    <div class="flex min-h-screen bg-slate-50 text-slate-900">
        <Sidebar />

        <main class="flex-1 overflow-x-hidden">
            <!-- Flash -->
            <div
                v-if="flash.success"
                class="mx-6 mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="mx-6 mt-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-2 text-sm text-rose-800"
            >
                {{ flash.error }}
            </div>

            <slot />
        </main>
    </div>
</template>
