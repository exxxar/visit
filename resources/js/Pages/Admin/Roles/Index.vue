<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import VButton from '@/Components/ui/VButton.vue'
import VCard from '@/Components/ui/VCard.vue'

const props = defineProps(['roles', 'allPermissions'])

const state = ref(Object.fromEntries(
    props.roles.map((r) => [r.id, new Set(r.permissions.map((p) => p.name))])
))
const saving = ref(null)

const toggle = (roleId, perm) => {
    const s = state.value[roleId]
    s.has(perm) ? s.delete(perm) : s.add(perm)
}

const save = (role) => {
    saving.value = role.id
    router.put(`/admin/roles/${role.id}`, { permissions: [...state.value[role.id]] }, {
        preserveScroll: true,
        onFinish: () => (saving.value = null),
    })
}
</script>

<template>
    <div>
        <div class="pg-head"><h1>Роли и права</h1></div>

        <div class="grid grid--2">
            <VCard v-for="role in roles" :key="role.id">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px">
                    <h3 style="margin:0">{{ role.name }}</h3>
                    <VButton size="sm" style="margin-left:auto" :busy="saving === role.id" @click="save(role)">
                        Сохранить
                    </VButton>
                </div>
                <div class="chkgrid">
                    <label v-for="p in allPermissions" :key="p" class="chk">
                        <input type="checkbox" :checked="state[role.id]?.has(p)" @change="toggle(role.id, p)">
                        <i>✓</i><span>{{ p }}</span>
                    </label>
                </div>
            </VCard>
        </div>
    </div>
</template>
