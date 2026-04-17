<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import SearchableSelect from '../../Components/Pult/SearchableSelect.vue';
import ConfirmDialog from '../../Components/Pult/ConfirmDialog.vue';
import { useTranslations } from '../../Composables/useTranslations';

interface Unit { id: string; name: string; color: string; unit_type: string | null }
interface Contractor {
    id: number;
    unit_id: string | null;
    name: string;
    specialty: string | null;
    contact_email: string | null;
    contact_phone: string | null;
    status: 'active' | 'paused' | 'ended';
    started_at: string | null;
    ended_at: string | null;
    rate: string | null;
    notes: string | null;
    unit: Unit | null;
}

const props = defineProps<{
    contractors: Contractor[];
    allUnits: Unit[];
    statuses: string[];
}>();

const { t } = useTranslations();

const STATUS_COLORS: Record<string, string> = {
    active: 'bg-emerald-50 text-emerald-700',
    paused: 'bg-amber-50 text-amber-700',
    ended: 'bg-slate-100 text-slate-600',
};
const STATUS_LABELS: Record<string, string> = {
    active: 'Активен',
    paused: 'Пауза',
    ended: 'Завершён',
};

type FormShape = {
    unit_id: string | null;
    name: string;
    specialty: string;
    contact_email: string;
    contact_phone: string;
    status: 'active' | 'paused' | 'ended';
    started_at: string;
    ended_at: string;
    rate: string;
    notes: string;
};

const editingId = ref<number | null>(null); // null = adding, number = editing
const showForm = ref(false);
const form = useForm<FormShape>({
    unit_id: null,
    name: '',
    specialty: '',
    contact_email: '',
    contact_phone: '',
    status: 'active',
    started_at: '',
    ended_at: '',
    rate: '',
    notes: '',
});

function openAdd() {
    editingId.value = null;
    form.reset();
    form.status = 'active';
    showForm.value = true;
}

function openEdit(c: Contractor) {
    editingId.value = c.id;
    form.unit_id = c.unit_id;
    form.name = c.name;
    form.specialty = c.specialty ?? '';
    form.contact_email = c.contact_email ?? '';
    form.contact_phone = c.contact_phone ?? '';
    form.status = c.status;
    form.started_at = c.started_at ?? '';
    form.ended_at = c.ended_at ?? '';
    form.rate = c.rate ?? '';
    form.notes = c.notes ?? '';
    showForm.value = true;
}

function submit() {
    const opts = { preserveScroll: true, onSuccess: () => { showForm.value = false; form.reset(); editingId.value = null; } };
    if (editingId.value !== null) {
        form.put(`/contractors/${editingId.value}`, opts);
    } else {
        form.post('/contractors', opts);
    }
}

const deletingId = ref<number | null>(null);
function doDelete() {
    if (!deletingId.value) return;
    router.delete(`/contractors/${deletingId.value}`, { preserveScroll: true, onFinish: () => { deletingId.value = null; } });
}

function formatDate(iso: string | null): string {
    if (!iso) return '—';
    const d = new Date(iso);
    return `${String(d.getDate()).padStart(2,'0')}.${String(d.getMonth()+1).padStart(2,'0')}.${d.getFullYear()}`;
}
</script>

<template>
    <Head :title="t('nav.contractors')" />

    <AppLayout>
        <div class="mx-auto max-w-6xl px-6 py-8">
            <div class="mb-6 flex items-start justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ t('nav.contractors') }}</h1>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Учёт внешних подрядчиков и фрилансеров</p>
                </div>
                <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-3.5 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700"
                    @click="openAdd"
                >
                    + Добавить контрактора
                </button>
            </div>

            <!-- Form card -->
            <div v-if="showForm" class="mb-4 rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 shadow-sm dark:border-indigo-900 dark:bg-slate-800">
                <form class="grid gap-3 md:grid-cols-3" @submit.prevent="submit">
                    <label class="block md:col-span-2">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Имя / Компания <span class="text-rose-500">*</span></span>
                        <input v-model="form.name" type="text" required class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Статус</span>
                        <SearchableSelect
                            v-model="form.status"
                            :options="statuses.map(s => ({ value: s, label: STATUS_LABELS[s] ?? s }))"
                            size="sm"
                        />
                    </label>

                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Специализация</span>
                        <input v-model="form.specialty" type="text" placeholder="SEO, Design, DevOps…" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Компания</span>
                        <SearchableSelect
                            v-model="form.unit_id"
                            :options="[{ value: null, label: '— Холдинг' }, ...allUnits.map(u => ({ value: u.id, label: u.name, color: u.color }))]"
                            size="sm"
                        />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Ставка</span>
                        <input v-model="form.rate" type="text" placeholder="$50/час или $3000/мес" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>

                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Email</span>
                        <input v-model="form.contact_email" type="email" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Телефон</span>
                        <input v-model="form.contact_phone" type="text" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <div></div>

                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Начало</span>
                        <input v-model="form.started_at" type="date" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <label class="block">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Окончание</span>
                        <input v-model="form.ended_at" type="date" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm" />
                    </label>
                    <div></div>

                    <label class="block md:col-span-3">
                        <span class="mb-1 block text-xs font-medium text-slate-600">Заметки</span>
                        <textarea v-model="form.notes" rows="2" class="w-full rounded border border-slate-300 px-3 py-1.5 text-sm"></textarea>
                    </label>

                    <div class="flex items-center justify-end gap-3 md:col-span-3">
                        <button type="button" class="rounded-md px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100" @click="showForm = false">Отмена</button>
                        <button type="submit" :disabled="form.processing" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-700 disabled:bg-slate-300">
                            {{ editingId !== null ? 'Сохранить' : 'Создать' }}
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="w-14 px-4 py-3">ID</th>
                            <th class="px-4 py-3">Имя</th>
                            <th class="px-4 py-3">Специализация</th>
                            <th class="px-4 py-3">Компания</th>
                            <th class="px-4 py-3">Контакты</th>
                            <th class="px-4 py-3">Период</th>
                            <th class="px-4 py-3">Ставка</th>
                            <th class="px-4 py-3">Статус</th>
                            <th class="w-24 px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="c in contractors" :key="c.id" class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">#{{ c.id }}</td>
                            <td class="px-4 py-2.5 font-medium text-slate-800 dark:text-slate-200">{{ c.name }}</td>
                            <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400">{{ c.specialty ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span v-if="c.unit" class="inline-flex items-center gap-1.5 text-xs">
                                    <span class="inline-block h-2 w-2 rounded-full" :style="{ backgroundColor: c.unit.color }"></span>
                                    {{ c.unit.name }}
                                </span>
                                <span v-else class="text-xs text-slate-400">Холдинг</span>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-400">
                                <div v-if="c.contact_email">{{ c.contact_email }}</div>
                                <div v-if="c.contact_phone" class="font-mono">{{ c.contact_phone }}</div>
                                <span v-if="!c.contact_email && !c.contact_phone">—</span>
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs text-slate-500">
                                {{ formatDate(c.started_at) }} → {{ formatDate(c.ended_at) }}
                            </td>
                            <td class="px-4 py-2.5 text-slate-700 dark:text-slate-300">{{ c.rate ?? '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium" :class="STATUS_COLORS[c.status]">
                                    {{ STATUS_LABELS[c.status] ?? c.status }}
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button class="text-slate-400 hover:text-indigo-600" title="Редактировать" @click="openEdit(c)">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                    </button>
                                    <button class="text-slate-400 hover:text-rose-600" title="Удалить" @click="deletingId = c.id">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="contractors.length === 0">
                            <td colspan="9" class="px-4 py-12 text-center text-sm text-slate-400">Пока нет контракторов</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ConfirmDialog :show="deletingId !== null" title="Удалить контрактора?" message="Это действие нельзя отменить." @confirm="doDelete" @cancel="deletingId = null" />
    </AppLayout>
</template>
