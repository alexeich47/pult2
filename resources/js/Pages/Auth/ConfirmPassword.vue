<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Head, useForm } from '@inertiajs/vue3';

const { t } = useTranslations();

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.confirm.title')" />

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900">{{ t('auth.confirm.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ t('auth.confirm.sub') }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="password" class="mb-1 block text-xs font-medium text-slate-700">
                    {{ t('auth.common.password') }}
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autofocus
                    autocomplete="current-password"
                    :placeholder="t('auth.common.password_ph')"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <div v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
            >
                {{ t('auth.confirm.submit') }}
            </button>
        </form>
    </GuestLayout>
</template>
