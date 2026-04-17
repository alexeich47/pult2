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
    emoji?: string;
    highlight?: boolean; // render with special emphasis (bigger, uppercase, emoji instead of dot)
}

interface NavGroup {
    id: string;
    key: string;          // i18n key for the group label
    emoji?: string;
    items: NavItem[];
}

// Items that aren't inside a group render flat at the top of the nav list.
const NAV_ITEMS: NavItem[] = [
    { id: 'dashboard', key: 'nav.dashboard', href: '/dashboard', emoji: '📊', highlight: true },
];

const NAV_GROUPS: NavGroup[] = [
    {
        id: 'tasks',
        key: 'sidebar.group.tasks',
        emoji: '✅',
        items: [
            { id: 'tasks', key: 'nav.tasks', href: '/tasks' },
            { id: 'ideas', key: 'nav.ideas', href: '/ideas' },
            { id: 'risks', key: 'nav.risks', href: '/risks' },
        ],
    },
    {
        id: 'money',
        key: 'sidebar.group.money',
        emoji: '💰',
        items: [
            { id: 'finance', key: 'nav.finance', href: '/finance' },
            { id: 'rnd', key: 'nav.rnd', href: '/rnd' },
        ],
    },
    {
        id: 'docs',
        key: 'sidebar.group.docs',
        emoji: '📚',
        items: [
            { id: 'documents', key: 'nav.documents', href: '/documents' },
            { id: 'strategy', key: 'nav.strategy', href: '/strategy' },
            { id: 'instructions', key: 'nav.instructions', href: '/instructions' },
            { id: 'processes', key: 'nav.processes', href: '/processes' },
        ],
    },
    {
        id: 'personnel',
        key: 'sidebar.group.personnel',
        emoji: '👥',
        items: [
            { id: 'structure', key: 'nav.structure', href: '/structure' },
            { id: 'personnel', key: 'nav.personnel', href: '/personnel' },
            { id: 'contractors', key: 'nav.contractors', href: '/contractors' },
            { id: 'hiring', key: 'nav.hiring', href: '/hiring' },
            { id: 'meetings', key: 'nav.meetings', href: '/meetings' },
            { id: 'services', key: 'nav.services', href: '/services' },
        ],
    },
    {
        id: 'metrics',
        key: 'sidebar.group.metrics',
        emoji: '📈',
        items: [
            { id: 'okr', key: 'nav.okr', href: '/okr' },
            { id: 'sla', key: 'nav.sla', href: '/sla' },
        ],
    },
];

// Persist expanded/collapsed per group in localStorage.
const COLLAPSE_KEY = 'pult.sidebar.groups.collapsed';
function loadCollapsed(): Record<string, boolean> {
    if (typeof window === 'undefined') return {};
    try { return JSON.parse(window.localStorage.getItem(COLLAPSE_KEY) ?? '{}'); } catch { return {}; }
}
const collapsed = ref<Record<string, boolean>>(loadCollapsed());
function toggleGroup(id: string) {
    collapsed.value = { ...collapsed.value, [id]: !collapsed.value[id] };
    try { window.localStorage.setItem(COLLAPSE_KEY, JSON.stringify(collapsed.value)); } catch { /* quota/ssr */ }
}

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
            href="/home"
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

        <!-- Scrollable middle section (hidden scrollbar) -->
        <div class="flex-1 overflow-y-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">

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
                    v-if="item.href && item.highlight"
                    :href="item.href"
                    :class="[
                        'mb-1 flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-sm font-bold uppercase tracking-wider transition-colors',
                        isActive(item.href)
                            ? 'bg-indigo-500/20 text-white ring-1 ring-indigo-500/40'
                            : 'bg-slate-800/40 text-slate-100 hover:bg-slate-800 hover:text-white',
                    ]"
                >
                    <span class="text-base leading-none">{{ item.emoji }}</span>
                    {{ t(item.key) }}
                </Link>
                <Link
                    v-else-if="item.href"
                    :href="item.href"
                    :class="[
                        'flex items-center gap-2 rounded-md px-2.5 py-1 text-[13px] transition-colors',
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
                    class="flex cursor-not-allowed items-center gap-2 rounded-md px-2.5 py-1 text-[13px] text-slate-500"
                    :title="t('placeholder.wip')"
                >
                    <span class="inline-block h-1.5 w-1.5 rounded-full bg-current opacity-40" />
                    {{ t(item.key) }}
                    <span class="ml-auto text-[10px] uppercase text-slate-600">wip</span>
                </div>
            </template>

            <!-- Collapsible groups -->
            <div v-for="group in NAV_GROUPS" :key="group.id" class="mt-3">
                <button
                    type="button"
                    class="group flex w-full items-center gap-2 rounded-md px-2 py-1.5 text-sm font-bold uppercase tracking-wider text-slate-200 transition-colors hover:bg-slate-800/60 hover:text-white"
                    @click="toggleGroup(group.id)"
                >
                    <span v-if="group.emoji" class="text-base leading-none">{{ group.emoji }}</span>
                    <svg
                        class="h-3.5 w-3.5 transition-transform text-slate-400"
                        :class="collapsed[group.id] ? '-rotate-90' : ''"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                    </svg>
                    {{ t(group.key) }}
                </button>
                <div v-show="!collapsed[group.id]" class="mt-0.5 flex flex-col gap-0.5">
                    <template v-for="item in group.items" :key="item.id">
                        <Link
                            v-if="item.href"
                            :href="item.href"
                            :class="[
                                'flex items-center gap-2 rounded-md px-2.5 py-1 pl-6 text-[13px] transition-colors',
                                isActive(item.href)
                                    ? 'bg-indigo-500/15 text-indigo-200'
                                    : 'text-slate-300 hover:bg-slate-800/60 hover:text-white',
                            ]"
                        >
                            <span class="inline-block h-1.5 w-1.5 rounded-full bg-current opacity-60" />
                            {{ t(item.key) }}
                        </Link>
                    </template>
                </div>
            </div>
        </div>

        <div class="mx-3 my-2 h-px bg-slate-800" />

        </div><!-- end scrollable middle -->

        <!-- Footer (pinned to bottom, compact) -->
        <div class="shrink-0 border-t border-slate-800 px-4 py-2">
            <div class="flex flex-wrap gap-x-3 gap-y-1 text-[11px]">
                <Link href="/info" class="text-slate-500 hover:text-white">{{ t('footer.info') }}</Link>
                <Link href="/codex" class="text-slate-500 hover:text-white">{{ t('footer.codex') }}</Link>
                <Link href="/dictionary" class="text-slate-500 hover:text-white">{{ t('footer.dictionary') }}</Link>
                <Link href="/archive" class="text-slate-500 hover:text-white">{{ t('footer.archive') }}</Link>
                <Link href="/activity-log" class="text-slate-500 hover:text-white">{{ t('footer.log') }}</Link>
            </div>
            <div class="mt-2 flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <Link
                        v-for="code in page.props.supportedLocales"
                        :key="code"
                        :href="`/locale/${code}`"
                        method="post"
                        as="button"
                        :class="[
                            'rounded px-1.5 py-0.5 text-[10px] uppercase transition-colors',
                            page.props.locale === code
                                ? 'bg-indigo-500 text-white'
                                : 'bg-slate-800 text-slate-500 hover:text-white',
                        ]"
                    >
                        {{ code }}
                    </Link>
                    <button
                        type="button"
                        class="ml-1 rounded bg-slate-800 px-1.5 py-0.5 text-[10px] text-slate-500 hover:text-white"
                        @click="toggleDark"
                    >
                        {{ isDark ? '☀️' : '🌙' }}
                    </button>
                </div>
                <div class="flex items-center gap-2 text-[11px]">
                    <span v-if="page.props.auth.user" class="text-slate-500">{{ page.props.auth.user.name }}</span>
                    <Link href="/profile" class="text-slate-500 hover:text-white">⚙</Link>
                    <Link href="/logout" method="post" as="button" class="text-slate-500 hover:text-white">↗</Link>
                </div>
            </div>
        </div>
    </aside>
</template>
