<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import SearchableSelect from '../../Components/Pult/SearchableSelect.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface Unit { id: string; name: string; color: string }
interface User { id: number; name: string; email: string | null }
interface Doc {
    id: number;
    unit_id: string | null;
    title: string;
    category: string | null;
    version: string;
    created_at: string;
    creator: User | null;
    unit: Unit | null;
}

defineProps<{
    documents: { data: Doc[]; links: unknown[] };
    allUnits: Unit[];
}>();

const { t } = useTranslations();

const showAdd = ref(false);
const form = useForm<{ unit_id: string | null; title: string; category: string; content: string }>({
    unit_id: null,
    title: '',
    category: '',
    content: '',
});

function submit() {
    form.post('/documents', { preserveScroll: true, onSuccess: () => { showAdd.value = false; form.reset(); } });
}

function formatDate(iso: string): string {
    const d = new Date(iso);
    return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()}`;
}
</script>

<template>
    <Head :title="t('nav.documents')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ t('nav.documents') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Единый реестр документов холдинга</p>
                </div>
                <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                    @click="showAdd = true"
                >
                    + Добавить документ
                </button>
            </div>

            <!-- Inline add form -->
            <div v-if="showAdd" class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 shadow-sm dark:border-indigo-900 dark:bg-slate-800">
                <form class="grid gap-3 md:grid-cols-[2fr_1fr_1fr_auto] md:items-end" @submit.prevent="submit">
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Название *</span>
                        <input v-model="form.title" type="text" required class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Категория</span>
                        <input v-model="form.category" type="text" placeholder="Регламент / Шаблон / Политика…" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Компания</span>
                        <SearchableSelect
                            v-model="form.unit_id"
                            :options="[{ value: null, label: '— Холдинг' }, ...allUnits.map(u => ({ value: u.id, label: u.name, color: u.color }))]"
                            size="sm"
                        />
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:bg-slate-300">Создать</button>
                        <button type="button" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" @click="showAdd = false">Отмена</button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="w-16 px-4 py-3">ID</th>
                            <th class="px-4 py-3">Название</th>
                            <th class="px-4 py-3">Категория</th>
                            <th class="px-4 py-3">Компания</th>
                            <th class="px-4 py-3">Создан</th>
                            <th class="w-28 px-4 py-3">Дата создания</th>
                            <th class="w-20 px-4 py-3">Версия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="d in documents.data" :key="d.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">#{{ d.id }}</td>
                            <td class="px-4 py-2.5">
                                <Link :href="`/documents/${d.id}`" class="font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                    {{ d.title }}
                                </Link>
                            </td>
                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ d.category ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span v-if="d.unit" class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="inline-block h-2 w-2 rounded-full" :style="{ backgroundColor: d.unit.color }"></span>
                                    {{ d.unit.name }}
                                </span>
                                <span v-else class="text-xs text-slate-400">Холдинг</span>
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ d.creator?.name ?? '—' }}</td>
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">{{ formatDate(d.created_at) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                                    v{{ d.version }}
                                </span>
                            </td>
                        </tr>
                        <tr v-if="documents.data.length === 0">
                            <td colspan="7" class="px-4 py-12 text-center text-sm text-slate-400">Пока нет документов</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
