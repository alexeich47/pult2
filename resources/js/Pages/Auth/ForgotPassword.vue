<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const { t } = useTranslations();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.forgot.title')" />

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900">{{ t('auth.forgot.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ t('auth.forgot.sub') }}</p>
        </div>

        <div v-if="status" class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="email" class="mb-1 block text-xs font-medium text-slate-700">
                    {{ t('auth.common.email') }}
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    autocomplete="username"
                    :placeholder="t('auth.common.email_ph')"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <div v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
            >
                {{ t('auth.forgot.submit') }}
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            <Link :href="route('login')" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                ← {{ t('auth.forgot.back_to_login') }}
            </Link>
        </div>
    </GuestLayout>
</template>
