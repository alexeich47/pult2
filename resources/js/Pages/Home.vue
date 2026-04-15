<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import { useTranslations } from '../Composables/useTranslations';

const { t } = useTranslations();

interface Ticket {
    id: 'dayoff' | 'server' | 'domain' | 'payment';
    color: string;
}

const TICKETS: Ticket[] = [
    { id: 'dayoff', color: '#22c55e' },
    { id: 'server', color: '#3b82f6' },
    { id: 'domain', color: '#f59e0b' },
    { id: 'payment', color: '#ec4899' },
];
</script>

<template>
    <Head :title="t('home.greeting')" />

    <AppLayout>
        <div class="mx-auto max-w-5xl px-6 py-10">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900">
                    {{ t('home.greeting') }}
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    {{ t('home.sub') }}
                </p>
            </div>

            <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">
                {{ t('home.tickets_label') }}
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="ticket in TICKETS"
                    :key="ticket.id"
                    class="cursor-pointer rounded-lg border border-slate-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-[var(--tc)] hover:shadow-lg"
                    :style="{ '--tc': ticket.color }"
                >
                    <div
                        class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-md text-white"
                        :style="{ backgroundColor: ticket.color }"
                    >
                        <span class="text-lg font-bold">{{ ticket.id[0].toUpperCase() }}</span>
                    </div>
                    <div class="text-base font-semibold text-slate-900">
                        {{ t(`ticket.${ticket.id}.title`) }}
                    </div>
                    <div class="mt-1 text-sm text-slate-600">
                        {{ t(`ticket.${ticket.id}.desc`) }}
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-sm font-medium" :style="{ color: ticket.color }">
                        {{ t('ticket.cta') }}
                        <span>→</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
