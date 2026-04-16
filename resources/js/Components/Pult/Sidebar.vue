<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { PageProps } from '../../types';
import { useDarkMode } from '../../Composables/useDarkMode';
import { useTranslations } from '../../Composables/useTranslations';

const { t } = useTranslations();
const { isDark, toggle: toggleDark } = useDarkMode();
const page = usePage<PageProps>();

const units = computed(() => page.props.units ?? []);
const activeUnitId = computed(() => page.props.activeUnitId);

interface NavItem {
    id: string;
    key: string;
    href: string | null; // null = placeholder (not yet implemented)
}

const NAV_ITEMS: NavItem[] = [
    { id: 'dashboard', key: 'nav.dashboard', href: '/dashboard' },
    { id: 'structure', key: 'nav.structure', href: '/structure' },
    { id: 'personnel', key: 'nav.personnel', href: '/personnel' },
    { id: 'hiring', key: 'nav.hiring', href: '/hiring' },
    { id: 'strategy', key: 'nav.strategy', href: '/strategy' },
    { id: 'ideas', key: 'nav.ideas', href: '/ideas' },
    { id: 'agreements', key: 'nav.agreements', href: '/agreements' },
    { id: 'responsibilities', key: 'nav.responsibilities', href: '/responsibilities' },
    { id: 'sla', key: 'nav.sla', href: '/sla' },
    { id: 'services', key: 'nav.services', href: '/services' },
    { id: 'reports', key: 'nav.reports', href: '/reports' },
    { id: 'risks', key: 'nav.risks', href: '/risks' },
    { id: 'processes', key: 'nav.processes', href: '/processes' },
];

const currentPath = computed(() => {
    if (typeof window === 'undefined') return '';
    return window.location.pathname;
});

function isActive(href: string | null): boolean {
    if (!href) return false;
    return currentPath.value === href || currentPath.value.startsWith(href + '/');
}

/** Root unit (swiftpunk) — the holding itself */
const holdingUnit = computed(() => units.value.find((u) => u.unit_type === null));

/** Top-level companies (direct children of holding) */
const topLevelUnits = computed(() =>
    units.value.filter((u) => u.parent_id === holdingUnit.value?.id),
);

/** Get children of a given unit */
function childrenOf(parentId: string) {
    return units.value.filter((u) => u.parent_id === parentId);
}

function switchContext(event: Event) {
    const target = event.target as HTMLSelectElement;
    const value = target.value;
    router.post(`/context/${value}`, {}, { preserveScroll: true });
}
</script>

<template>
    <aside class="flex h-screen w-64 shrink-0 flex-col border-r border-slate-800 bg-slate-900 text-slate-200">
        <!-- Logo (fixed top) -->
        <Link
            href="/dashboard"
            class="flex shrink-0 items-center gap-3 border-b border-slate-800 px-5 py-4 hover:bg-slate-800/50"
        >
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 font-bold text-white">
                P
            </div>
            <div class="leading-tight">
                <div class="font-semibold text-white">{{ t('brand.name') }}</div>
                <div class="text-xs text-slate-400">{{ t('brand.sub') }}</div>
            </div>
        </Link>

        <!-- Scrollable middle section -->
        <div class="flex-1 overflow-y-auto">

        <!-- Company context selector -->
        <div class="border-b border-slate-800 px-3 py-3">
            <label class="px-2 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                {{ t('context.label') }}
            </label>
            <select
                class="mt-1 w-full rounded-md border border-slate-700 bg-slate-800 px-2.5 py-1.5 text-sm text-slate-200 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                :value="activeUnitId ?? 'all'"
                @change="switchContext"
            >
                <option value="all">
                    {{ holdingUnit?.name ?? 'Swift Punk' }} ({{ t('context.all') }})
                </option>
                <template v-for="unit in topLevelUnits" :key="unit.id">
                    <option :value="unit.id">
                        {{ unit.name }}
                    </option>
                    <option
                        v-for="child in childrenOf(unit.id)"
                        :key="child.id"
                        :value="child.id"
                    >
                        &nbsp;&nbsp;&nbsp;&nbsp;{{ child.name }}
                    </option>
                </template>
            </select>
        </div>

        <!-- Data section -->
        <div class="flex flex-col gap-0.5 px-3 py-3">
            <div class="px-2 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                {{ t('sidebar.section.data') }}
            </div>
            <template v-for="item in NAV_ITEMS" :key="item.id">
                <Link
                    v-if="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2 rounded-md px-2.5 py-1.5 text-sm transition-colors',
                        isActive(item.href)
                            ? 'bg-indigo-500/15 text-indigo-200'
                            : 'text-slate-300 hover:bg-slate-800/60 hover:text-white',
                    ]"
                >
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-current opacity-60" />
                    {{ t(item.key) }}
                </Link>
                <div
                    v-else
                    class="flex cursor-not-allowed items-center gap-2 rounded-md px-2.5 py-1.5 text-sm text-slate-500"
                    :title="t('placeholder.wip')"
                >
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-current opacity-40" />
                    {{ t(item.key) }}
                    <span class="ml-auto text-[10px] uppercase text-slate-600">wip</span>
                </div>
            </template>
        </div>

        <div class="mx-3 my-2 h-px bg-slate-800" />

        </div><!-- end scrollable middle -->

        <!-- Footer (pinned to bottom, never scrolls) -->
        <div class="shrink-0 flex flex-col gap-2 border-t border-slate-800 px-4 py-3">
            <div class="flex gap-3 text-xs">
                <Link href="/info" class="text-slate-400 hover:text-white">{{ t('footer.info') }}</Link>
                <Link href="/codex" class="text-slate-400 hover:text-white">{{ t('footer.codex') }}</Link>
                <Link href="/dictionary" class="text-slate-400 hover:text-white">{{ t('footer.dictionary') }}</Link>
                <Link href="/archive" class="text-slate-400 hover:text-white">{{ t('footer.archive') }}</Link>
                <Link href="/activity-log" class="text-slate-400 hover:text-white">{{ t('footer.log') }}</Link>
            </div>
            <div class="my-1 h-px bg-slate-800"></div>
            <div v-if="page.props.auth.user" class="text-xs text-slate-400">
                {{ page.props.auth.user.name }}
            </div>
            <!-- Controls row: locale + dark toggle -->
            <div class="flex items-center gap-2">
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
                <button
                    type="button"
                    class="ml-auto rounded bg-slate-800 px-2 py-0.5 text-[11px] text-slate-400 transition-colors hover:bg-slate-700 hover:text-white"
                    @click="toggleDark"
                    :title="isDark ? 'Light mode' : 'Dark mode'"
                >
                    {{ isDark ? '☀️' : '🌙' }}
                </button>
            </div>
            <Link
                href="/profile"
                class="text-xs text-slate-400 hover:text-white"
            >
                Profile
            </Link>
            <Link
                href="/logout"
                method="post"
                as="button"
                class="text-left text-xs text-slate-400 hover:text-white"
            >
                Log out
            </Link>
        </div>
    </aside>
</template>
