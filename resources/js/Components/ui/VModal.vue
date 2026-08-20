<script setup>
defineProps({ title: String, show: Boolean })
defineEmits(['close'])
</script>

<template>
    <Teleport to="body">
        <Transition name="mfade">
            <div v-if="show" class="vmodal" @click.self="$emit('close')">
                <div class="vmodal__box">
                    <div class="vmodal__head">
                        <h3>{{ title }}</h3>
                        <button class="vmodal__x" @click="$emit('close')">✕</button>
                    </div>
                    <div class="vmodal__body"><slot /></div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
