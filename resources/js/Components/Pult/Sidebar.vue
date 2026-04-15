<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { PageProps } from '../../types';
import { useTranslations } from '../../Composables/useTranslations';

const { t } = useTranslations();
const page = usePage<PageProps>();

const units = computed(() => page.props.units ?? []);

interface NavItem {
    id: string;
    key: string;
    href: string | null; // null = placeholder (not yet implemented)
}

const NAV_ITEMS: NavItem[] = [
    { id: 'dashboard', key: 'nav.dashboard', href: '/dashboard' },
    { id: 'personnel', key: 'nav.personnel', href: '/personnel' },
    { id: 'strategy', key: 'nav.strategy', href: null },
    { id: 'ideas', key: 'nav.ideas', href: '/ideas' },
    { id: 'agreements', key: 'nav.agreements', href: null },
    { id: 'responsibilities', key: 'nav.responsibilities', href: null },
    { id: 'sla', key: 'nav.sla', href: null },
    { id: 'services', key: 'nav.services', href: null },
    { id: 'reports', key: 'nav.reports', href: null },
    { id: 'risks', key: 'nav.risks', href: null },
    { id: 'processes', key: 'nav.processes', href: null },
];

const currentPath = computed(() => {
    if (typeof window === 'undefined') return '';
    return window.location.pathname;
});

function isActive(href: string | null): boolean {
    if (!href) return false;
    return currentPath.value === href || currentPath.value.startsWith(href + '/');
}
</script>

<template>
    <aside class="flex h-screen w-64 shrink-0 flex-col overflow-y-auto border-r border-slate-800 bg-slate-900 text-slate-200">
        <!-- Logo -->
        <Link
            href="/dashboard"
            class="flex items-center gap-3 border-b border-slate-800 px-5 py-4 hover:bg-slate-800/50"
        >
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-500 font-bold text-white">
                P
            </div>
            <div class="leading-tight">
                <div class="font-semibold text-white">{{ t('brand.name') }}</div>
                <div class="text-xs text-slate-400">{{ t('brand.sub') }}</div>
            </div>
        </Link>

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

        <!-- Units section -->
        <div class="flex flex-col gap-0.5 px-3 py-3">
            <div class="px-2 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                {{ t('sidebar.section.companies') }}
            </div>
            <div
                v-for="unit in units"
                :key="unit.id"
                class="flex cursor-not-allowed items-center gap-2 rounded-md px-2.5 py-1.5 text-sm text-slate-300 hover:bg-slate-800/60"
                :title="t('placeholder.wip')"
            >
                <span
                    class="flex h-5 w-5 items-center justify-center rounded text-xs font-bold"
                    :style="{ backgroundColor: unit.color + '33', color: unit.color }"
                >
                    {{ unit.unit_type === 'revenue' ? '$' : '🛠' }}
                </span>
                {{ unit.name }}
            </div>
        </div>

        <div class="mx-3 my-2 h-px bg-slate-800" />

        <!-- Footer / user -->
        <div class="mt-auto flex flex-col gap-2 border-t border-slate-800 px-4 py-4">
            <div v-if="page.props.auth.user" class="text-xs text-slate-400">
                {{ page.props.auth.user.name }}
            </div>
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
