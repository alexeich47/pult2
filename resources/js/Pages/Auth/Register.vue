<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import { Head, Link, useForm } from '@inertiajs/vue3';

const { t } = useTranslations();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="t('auth.register.title')" />

        <div class="mb-8">
            <h1 class="text-2xl font-semibold text-slate-900">{{ t('auth.register.title') }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ t('auth.register.sub') }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div>
                <label for="name" class="mb-1 block text-xs font-medium text-slate-700">
                    {{ t('auth.common.name') }}
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    autocomplete="name"
                    :placeholder="t('auth.common.name_ph')"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <div v-if="form.errors.name" class="mt-1 text-xs text-rose-600">{{ form.errors.name }}</div>
            </div>

            <div>
                <label for="email" class="mb-1 block text-xs font-medium text-slate-700">
                    {{ t('auth.common.email') }}
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autocomplete="username"
                    :placeholder="t('auth.common.email_ph')"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <div v-if="form.errors.email" class="mt-1 text-xs text-rose-600">{{ form.errors.email }}</div>
            </div>

            <div>
                <label for="password" class="mb-1 block text-xs font-medium text-slate-700">
                    {{ t('auth.common.password') }}
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="t('auth.common.password_ph')"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <div v-if="form.errors.password" class="mt-1 text-xs text-rose-600">{{ form.errors.password }}</div>
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-xs font-medium text-slate-700">
                    {{ t('auth.common.confirm_password') }}
                </label>
                <input
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    autocomplete="new-password"
                    :placeholder="t('auth.common.password_ph')"
                    class="w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <div v-if="form.errors.password_confirmation" class="mt-1 text-xs text-rose-600">
                    {{ form.errors.password_confirmation }}
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="w-full rounded-md bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 disabled:opacity-50"
            >
                {{ t('auth.register.submit') }}
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-600">
            {{ t('auth.register.have_account') }}
            <Link :href="route('login')" class="ml-1 font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                {{ t('auth.register.login_link') }}
            </Link>
        </div>
    </GuestLayout>
</template>
