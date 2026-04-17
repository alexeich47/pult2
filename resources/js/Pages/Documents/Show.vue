<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface Unit { id: string; name: string; color: string }
interface User { id: number; name: string; email: string | null }
interface Doc {
    id: number;
    unit_id: string | null;
    title: string;
    category: string | null;
    content: string | null;
    version: string;
    created_at: string;
    updated_at: string;
    creator: User | null;
    unit: Unit | null;
}
interface ActivityEntry {
    id: number;
    description: string;
    causer_name: string;
    created_at: string | null;
    changes: { attributes?: Record<string, unknown>; old?: Record<string, unknown> };
}

const props = defineProps<{
    document: Doc;
    activities: ActivityEntry[];
}>();

const { t } = useTranslations();

const editing = ref(false);
const form = useForm({
    title: props.document.title,
    category: props.document.category ?? '',
    content: props.document.content ?? '',
    unit_id: props.document.unit_id,
});

function save() {
    form.put(`/documents/${props.document.id}`, {
        preserveScroll: true,
        onSuccess: () => { editing.value = false; },
    });
}

function formatDateTime(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}`;
}

function renderChanges(a: ActivityEntry): Array<{ field: string; from: unknown; to: unknown }> {
    const attrs = a.changes.attributes ?? {};
    const old = a.changes.old ?? {};
    const keys = new Set([...Object.keys(attrs), ...Object.keys(old)]);
    return Array.from(keys).map(k => ({ field: k, from: old[k] ?? null, to: attrs[k] ?? null }));
}

function actionLabel(d: string): string {
    if (d.endsWith('.created')) return 'Создан';
    if (d.endsWith('.updated')) return 'Изменён';
    if (d.endsWith('.deleted')) return 'Удалён';
    return d;
}
function actionColor(d: string): string {
    if (d.endsWith('.created')) return 'bg-emerald-50 text-emerald-700';
    if (d.endsWith('.updated')) return 'bg-sky-50 text-sky-700';
    if (d.endsWith('.deleted')) return 'bg-rose-50 text-rose-700';
    return 'bg-slate-100 text-slate-600';
}
</script>

<template>
    <Head :title="document.title" />

    <AppLayout>
        <div class="mx-auto max-w-4xl px-6 py-8">
            <div class="mb-4 flex items-center gap-2 text-xs text-slate-500">
                <Link href="/documents" class="hover:text-indigo-600">{{ t('nav.documents') }}</Link>
                <span>/</span>
                <span class="text-slate-700">#{{ document.id }}</span>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <!-- Header -->
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <div class="mb-2 flex items-center gap-2">
                            <span class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">v{{ document.version }}</span>
                            <span v-if="document.category" class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700 dark:bg-slate-700 dark:text-slate-300">{{ document.category }}</span>
                            <span v-if="document.unit" class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                <span class="inline-block h-1.5 w-1.5 rounded-full" :style="{ backgroundColor: document.unit.color }"></span>
                                {{ document.unit.name }}
                            </span>
                        </div>
                        <h1 v-if="!editing" class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ document.title }}</h1>
                        <input v-else v-model="form.title" type="text" class="w-full rounded-md border border-indigo-400 px-3 py-1.5 text-xl font-semibold focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <button v-if="!editing" type="button" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200" @click="editing = true">Редактировать</button>
                        <template v-else>
                            <button type="button" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" @click="editing = false">Отмена</button>
                            <button type="button" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:bg-slate-300" @click="save">Сохранить</button>
                        </template>
                    </div>
                </div>

                <!-- Meta -->
                <div class="mb-5 grid grid-cols-2 gap-3 border-y border-slate-100 py-3 text-xs text-slate-600 dark:border-slate-700 dark:text-slate-400 sm:grid-cols-4">
                    <div>
                        <div class="text-slate-400">ID</div>
                        <div class="font-mono font-medium text-slate-700 dark:text-slate-300">#{{ document.id }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">Создан</div>
                        <div class="font-medium text-slate-700 dark:text-slate-300">{{ document.creator?.name ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">Дата создания</div>
                        <div class="font-medium text-slate-700 dark:text-slate-300">{{ formatDateTime(document.created_at) }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400">Изменён</div>
                        <div class="font-medium text-slate-700 dark:text-slate-300">{{ formatDateTime(document.updated_at) }}</div>
                    </div>
                </div>

                <!-- Content -->
                <div v-if="!editing" class="prose prose-sm max-w-none text-slate-700 dark:prose-invert dark:text-slate-300">
                    <template v-if="document.content">
                        <p v-for="(line, i) in document.content.split('\n')" :key="i">{{ line }}</p>
                    </template>
                    <p v-else class="text-slate-400">Содержимое не заполнено</p>
                </div>
                <div v-else class="space-y-3">
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Категория</span>
                        <input v-model="form.category" type="text" class="w-full rounded border border-slate-300 px-3 py-2 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Содержимое</span>
                        <textarea v-model="form.content" rows="10" class="w-full rounded border border-slate-300 px-3 py-2 text-sm font-mono"></textarea>
                    </label>
                </div>
            </div>

            <!-- Edit log -->
            <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <div class="border-b border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 dark:border-slate-700 dark:text-slate-200">
                    Лог правок
                </div>
                <ul v-if="activities.length > 0" class="divide-y divide-slate-100 dark:divide-slate-700">
                    <li v-for="a in activities" :key="a.id" class="px-5 py-3 text-sm">
                        <div class="mb-1 flex items-center gap-2">
                            <span class="rounded-full px-2 py-0.5 text-[10px] font-medium uppercase tracking-wider" :class="actionColor(a.description)">{{ actionLabel(a.description) }}</span>
                            <span class="font-medium text-slate-800 dark:text-slate-200">{{ a.causer_name }}</span>
                            <span class="text-xs text-slate-400">{{ formatDateTime(a.created_at) }}</span>
                        </div>
                        <ul v-if="renderChanges(a).length > 0" class="ml-2 space-y-0.5 text-xs">
                            <li v-for="c in renderChanges(a)" :key="c.field" class="text-slate-600 dark:text-slate-400">
                                <span class="font-mono text-slate-500">{{ c.field }}</span>:
                                <span v-if="c.from !== null" class="text-rose-600 line-through">{{ c.from }}</span>
                                <span v-if="c.from !== null && c.to !== null" class="mx-1 text-slate-400">→</span>
                                <span v-if="c.to !== null" class="text-emerald-700">{{ c.to }}</span>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div v-else class="px-5 py-8 text-center text-sm text-slate-400">Правок пока нет</div>
            </div>
        </div>
    </AppLayout>
</template>
