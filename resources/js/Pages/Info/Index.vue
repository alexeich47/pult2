<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';
import type { PageProps, Unit } from '../../types';

interface Goal {
    title: string;
    desc: string;
}

interface Feature {
    title: string;
    desc: string;
}

interface ChangelogEntry {
    version: string;
    title: string;
    items: string[];
}

const { t, tRaw } = useTranslations();
const page = usePage<PageProps>();

const introLines = computed<string[]>(() => tRaw<string[]>('info.intro', []));
const goals = computed<Goal[]>(() => tRaw<Goal[]>('info.goals.items', []));
const features = computed<Feature[]>(() => tRaw<Feature[]>('info.features.items', []));
const changelog = computed<ChangelogEntry[]>(() => tRaw<ChangelogEntry[]>('info.changelog.entries', []));

const units = computed<Unit[]>(() => page.props.units ?? []);
</script>

<template>
    <Head :title="t('info.title')" />

    <AppLayout>
        <div class="mx-auto max-w-5xl px-6 py-8">
            <header class="mb-8">
                <div class="mb-3 flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-500 text-xl font-bold text-white">P</div>
                    <div>
                        <h1 class="text-2xl font-semibold text-slate-900">Pult 2.0</h1>
                        <p class="text-sm text-slate-600">{{ t('info.hero_sub') }}</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm leading-relaxed text-slate-700">
                    <p v-for="(line, i) in introLines" :key="i">{{ line }}</p>
                </div>
            </header>

            <!-- Goals -->
            <section class="mb-10">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('info.goals.label') }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="(goal, i) in goals"
                        :key="i"
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h3 class="font-semibold text-slate-900">{{ goal.title }}</h3>
                        <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ goal.desc }}</p>
                    </article>
                </div>
            </section>

            <!-- Features -->
            <section class="mb-10">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('info.features.title') }}</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <article
                        v-for="(feat, i) in features"
                        :key="i"
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <h3 class="text-sm font-semibold text-slate-900">{{ feat.title }}</h3>
                        <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ feat.desc }}</p>
                    </article>
                </div>
            </section>

            <!-- Units legend -->
            <section class="mb-10">
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('info.units.label') }}</h2>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex flex-col gap-1 text-sm text-slate-600">
                        <span>{{ t('info.units.revenue') }}</span>
                        <span>{{ t('info.units.service') }}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span
                            v-for="unit in units"
                            :key="unit.id"
                            class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-xs font-medium"
                            :style="{ backgroundColor: unit.color + '22', color: unit.color }"
                        >
                            {{ unit.unit_type === 'revenue' ? '$' : '🛠' }}
                            {{ unit.name }}
                        </span>
                    </div>
                </div>
            </section>

            <!-- Changelog -->
            <section>
                <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('info.changelog.title') }}</h2>
                <div class="space-y-3">
                    <article
                        v-for="(entry, i) in changelog"
                        :key="entry.version"
                        class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <div class="mb-2 flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-indigo-600">{{ entry.version }}</span>
                            <span class="font-semibold text-slate-900">{{ entry.title }}</span>
                            <span
                                v-if="i === 0"
                                class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold uppercase text-emerald-700"
                            >
                                {{ t('info.changelog.latest') }}
                            </span>
                        </div>
                        <ul class="space-y-1 text-sm text-slate-700">
                            <li
                                v-for="item in entry.items"
                                :key="item"
                                class="flex gap-2"
                            >
                                <span class="mt-1.5 inline-block h-1 w-1 shrink-0 rounded-full bg-slate-400"></span>
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </article>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
