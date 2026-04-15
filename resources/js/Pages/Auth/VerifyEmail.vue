<script setup lang="ts">
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    status?: string;
}>();

const { t } = useTranslations();

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.verify.title')" />

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900">{{ t('auth.verify.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ t('auth.verify.sub') }}</p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800"
        >
            {{ t('auth.verify.sent') }}
        </div>

        <form @submit.prevent="submit">
            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
            >
                {{ t('auth.verify.resend') }}
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline"
            >
                {{ t('auth.verify.logout') }}
            </Link>
        </div>
    </GuestLayout>
</template>
