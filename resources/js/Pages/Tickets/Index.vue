<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import TicketFormModal from '../../Components/Pult/TicketFormModal.vue';
import { useTranslations } from '../../Composables/useTranslations';

type TicketId = 'dayoff' | 'server' | 'domain' | 'payment' | 'contractor' | 'raise';

const { t } = useTranslations();

const TICKETS: { id: TicketId; color: string }[] = [
    { id: 'dayoff', color: '#22c55e' },
    { id: 'server', color: '#3b82f6' },
    { id: 'domain', color: '#f59e0b' },
    { id: 'payment', color: '#ec4899' },
    { id: 'contractor', color: '#8b5cf6' },
    { id: 'raise', color: '#06b6d4' },
];

const showTicketModal = ref(false);
const activeTicket = ref<TicketId | null>(null);
const activeColor = ref('#6b7280');

function openTicket(ticket: { id: TicketId; color: string }) {
    activeTicket.value = ticket.id;
    activeColor.value = ticket.color;
    showTicketModal.value = true;
}
</script>

<template>
    <Head :title="t('tickets_page.title')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ t('tickets_page.title') }}</h1>
                <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">{{ t('tickets_page.sub') }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="ticket in TICKETS"
                    :key="ticket.id"
                    class="cursor-pointer rounded-lg border border-slate-200 bg-white p-5 transition-all hover:-translate-y-0.5 hover:border-[var(--tc)] hover:shadow-lg dark:border-slate-700 dark:bg-slate-800"
                    :style="{ '--tc': ticket.color }"
                    @click="openTicket(ticket)"
                >
                    <div
                        class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-md text-white"
                        :style="{ backgroundColor: ticket.color }"
                    >
                        <span class="text-lg font-bold">{{ ticket.id[0].toUpperCase() }}</span>
                    </div>
                    <div class="text-base font-semibold text-slate-900 dark:text-slate-100">
                        {{ t(`ticket.${ticket.id}.title`) }}
                    </div>
                    <div class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        {{ t(`ticket.${ticket.id}.desc`) }}
                    </div>
                    <div class="mt-4 flex items-center gap-1 text-sm font-medium" :style="{ color: ticket.color }">
                        {{ t('ticket.cta') }}
                        <span>&rarr;</span>
                    </div>
                </div>
            </div>
        </div>

        <TicketFormModal
            :show="showTicketModal"
            :ticket-id="activeTicket"
            :ticket-color="activeColor"
            @close="showTicketModal = false; activeTicket = null"
        />
    </AppLayout>
</template>
