<script setup>
import PlaceMenu from '@/Components/PlaceMenu.vue'

const props = defineProps(['place'])
</script>

<template>
    <!-- ... существующий контент ... -->

    <PlaceMenu
        :place="place"
        :external-id="place.external_id"
        :external-source="place.external_source"
    />
</template>
