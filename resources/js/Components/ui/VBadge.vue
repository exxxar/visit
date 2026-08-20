<script setup>
defineProps({ color: { type: String, default: 'gray' }, label: String })
</script>

<template>
    <span class="vbadge" :class="`vbadge--${color}`">{{ label }}</span>
</template>
