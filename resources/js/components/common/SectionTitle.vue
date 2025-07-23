<template>
    <h3 class="section-title">
        <slot>{{ title }}</slot>
    </h3>
</template>
<script setup>
const props = defineProps({ title: { type: String, required: true } });
</script>
<style scoped>
.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 1.5rem 0;
    color: var(--details-text, #111827);
}
</style>
