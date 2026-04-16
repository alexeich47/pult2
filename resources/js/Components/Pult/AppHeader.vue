<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '../../Composables/useTranslations';

const { t } = useTranslations();
</script>

<template>
    <header class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-2.5 dark:border-slate-700 dark:bg-slate-900">
        <div>
            <!-- Reserved for breadcrumbs -->
        </div>
        <div class="flex items-center gap-3">
            <Link
                href="/tickets"
                class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-3.5 py-1.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700"
            >
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/>
                    <path d="M13 5v2"/>
                    <path d="M13 17v2"/>
                    <path d="M13 11v2"/>
                </svg>
                {{ t('header.ticket_btn') }}
            </Link>
        </div>
    </header>
</template>
