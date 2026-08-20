<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VBadge from '@/Components/ui/VBadge.vue'
import VModal from '@/Components/ui/VModal.vue'
import { useLabels } from '@/composables/useLabels'

const props = defineProps(['users', 'allRoles'])
const { L, badge } = useLabels()

const q = ref('')
const search = () => router.get('/admin/users', { q: q.value }, { preserveScroll: true })

/* создание */
const showCreate = ref(false)
const create = useForm({ name: '', email: '', password: '', roles: [] })
const submitCreate = () => create.post('/admin/users', {
    onSuccess: () => { showCreate.value = false; create.reset() },
})

/* права и статус */
const editUser = ref(null)
const edit = useForm({ roles: [], status: 'active' })
const openEdit = (u) => {
    editUser.value = u
    edit.roles  = u.roles.map((r) => r.name)
    edit.status = u.status
}
const submitEdit = () => edit.put(`/admin/users/${editUser.value.id}`, {
    onSuccess: () => (editUser.value = null),
})

const toggleBlock = (u) => router.put(`/admin/users/${u.id}`, {
    status: u.status === 'active' ? 'blocked' : 'active',
    roles: u.roles.map((r) => r.name),
}, { preserveScroll: true })

const destroy = (u) =>
    confirm(`Удалить ${u.email}?`) && router.delete(`/admin/users/${u.id}`, { preserveScroll: true })
</script>

<template>
    <div>
        <div class="pg-head">
            <h1>Пользователи</h1>
            <VButton style="margin-left:auto" @click="showCreate = true">+ Новый</VButton>
        </div>

        <div class="vcard" style="margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap">
            <input v-model="q" class="inp" style="max-width:300px" placeholder="Имя или email…" @keyup.enter="search">
            <VButton variant="ghost" @click="search">Найти</VButton>
        </div>

        <div class="vcard" style="padding:0;overflow:hidden">
            <table class="vtable">
                <thead><tr><th>Имя</th><th>Email</th><th>Роли</th><th>Статус</th><th></th></tr></thead>
                <tbody>
                <tr v-for="u in users.data" :key="u.id">
                    <td><b>{{ u.name }}</b></td>
                    <td style="color:var(--mut)">{{ u.email }}</td>
                    <td>
                        <VBadge v-for="r in u.roles" :key="r.name" :label="r.name" color="cyan" style="margin-right:6px" />
                        <span v-if="!u.roles.length" style="color:var(--mut)">—</span>
                    </td>
                    <td><VBadge v-bind="badge(L.user, u.status)" /></td>
                    <td style="text-align:right;white-space:nowrap">
                        <VButton size="sm" variant="ghost" @click="openEdit(u)">Права</VButton>
                        <VButton size="sm" :variant="u.status === 'active' ? 'danger' : 'grad'" @click="toggleBlock(u)">
                            {{ u.status === 'active' ? 'Блок' : 'Разблок' }}
                        </VButton>
                        <button class="tlink" @click="destroy(u)">🗑</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="pager">
            <component :is="'Link'" v-for="l in users.links" :key="l.label" :href="l.url ?? '#'" preserve-scroll
                       :class="{ on: l.active, off: !l.url }" v-html="l.label" />
        </div>

        <!-- создание -->
        <VModal :show="showCreate" title="Новый пользователь" @close="showCreate = false">
            <label class="lbl">Имя</label><input v-model="create.name" class="inp">
            <p v-if="create.errors.name" class="err">{{ create.errors.name }}</p>
            <label class="lbl" style="margin-top:12px">Email</label><input v-model="create.email" class="inp">
            <p v-if="create.errors.email" class="err">{{ create.errors.email }}</p>
            <label class="lbl" style="margin-top:12px">Пароль</label>
            <input v-model="create.password" type="password" class="inp">
            <p v-if="create.errors.password" class="err">{{ create.errors.password }}</p>
            <label class="lbl" style="margin-top:12px">Роли</label>
            <div class="chkgrid">
                <label v-for="r in allRoles" :key="r" class="chk">
                    <input v-model="create.roles" type="checkbox" :value="r"><i>✓</i><span>{{ r }}</span>
                </label>
            </div>
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton :busy="create.processing" @click="submitCreate">Создать</VButton>
                <VButton variant="ghost" @click="showCreate = false">Отмена</VButton>
            </div>
        </VModal>

        <!-- права -->
        <VModal :show="!!editUser" :title="`Права: ${editUser?.name}`" @close="editUser = null">
            <label class="lbl">Роли</label>
            <div class="chkgrid">
                <label v-for="r in allRoles" :key="r" class="chk">
                    <input v-model="edit.roles" type="checkbox" :value="r"><i>✓</i><span>{{ r }}</span>
                </label>
            </div>
            <label class="lbl" style="margin-top:12px">Статус</label>
            <select v-model="edit.status" class="inp">
                <option v-for="(m, k) in L.user" :key="k" :value="k">{{ m.label }}</option>
            </select>
            <div style="display:flex;gap:10px;margin-top:16px">
                <VButton :busy="edit.processing" @click="submitEdit">Сохранить</VButton>
            </div>
        </VModal>
    </div>
</template>
