<script setup>
defineProps({
    variant: { type: String, default: 'grad' },   // grad | ghost | danger
    size:    { type: String, default: 'md' },     // sm | md
    busy:    Boolean,
})
</script>

<template>
    <button class="vbtn" :class="[`vbtn--${variant}`, `vbtn--${size}`, { 'vbtn--busy': busy }]" :disabled="busy">
        <span v-if="busy" class="spin" />
        <slot />
    </button>
</template>
