<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<Props>();
</script>

<template>
    <div
        v-if="total > 0 && links.length > 3"
        class="flex flex-col items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 sm:flex-row dark:border-slate-700"
    >
        <div class="text-xs text-slate-500">
            {{ from }}–{{ to }} of {{ total }}
        </div>
        <div class="flex gap-1">
            <template v-for="link in links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    preserve-scroll
                    preserve-state
                    :class="[
                        'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-xs transition-colors',
                        link.active
                            ? 'bg-indigo-600 font-semibold text-white'
                            : 'bg-white text-slate-600 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                    ]"
                    v-html="link.label"
                />
                <span
                    v-else
                    class="inline-flex h-8 min-w-8 cursor-default items-center justify-center rounded-md px-2 text-xs text-slate-300 dark:text-slate-600"
                    v-html="link.label"
                />
            </template>
        </div>
    </div>
</template>
