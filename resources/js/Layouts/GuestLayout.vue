<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { useTranslations } from '../Composables/useTranslations';
import type { PageProps } from '../types';

const { t } = useTranslations();
const page = usePage<PageProps>();
</script>

<template>
    <div class="flex min-h-screen flex-col md:flex-row">
        <!-- Left brand panel -->
        <aside
            class="flex flex-col justify-between bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950 px-8 py-10 text-slate-200 md:w-5/12 md:px-12 md:py-14 lg:w-1/2 lg:px-16"
        >
            <div>
                <Link href="/" class="inline-flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-indigo-500 text-lg font-bold text-white shadow-lg shadow-indigo-500/30">
                        P
                    </div>
                    <div class="leading-tight">
                        <div class="text-xl font-semibold text-white">{{ t('brand.name') }}</div>
                        <div class="text-xs text-slate-400">{{ t('brand.sub') }}</div>
                    </div>
                </Link>

                <p class="mt-10 max-w-md text-base leading-relaxed text-slate-300 md:mt-16 md:text-lg">
                    {{ t('brand.tagline') }}
                </p>
            </div>

            <div class="mt-12 flex items-center justify-between">
                <div class="text-xs text-slate-500">Pult 2.0 · Laravel 13 + Inertia + Vue 3</div>

                <!-- Locale switcher -->
                <div class="flex gap-1">
                    <Link
                        v-for="code in page.props.supportedLocales"
                        :key="code"
                        :href="`/locale/${code}`"
                        method="post"
                        as="button"
                        :class="[
                            'rounded px-2 py-0.5 text-[11px] uppercase transition-colors',
                            page.props.locale === code
                                ? 'bg-indigo-500 text-white'
                                : 'bg-slate-800 text-slate-400 hover:bg-slate-700 hover:text-white',
                        ]"
                    >
                        {{ code }}
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Right form panel -->
        <main class="flex flex-1 items-center justify-center bg-slate-50 px-6 py-12 md:py-16">
            <div class="w-full max-w-md">
                <slot />
            </div>
        </main>
    </div>
</template>
