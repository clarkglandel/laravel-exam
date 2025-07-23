<template>
    <div class="error-message">
        <svg class="error-icon" viewBox="0 0 24 24" fill="currentColor">
            <path
                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
            />
        </svg>
        <span>{{ message }}</span>
    </div>
</template>
<script setup>
const props = defineProps({ message: { type: String, required: true } });
</script>
<style scoped>
.error-message {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    color: #dc2626;
    font-size: 0.9rem;
    margin-top: 1rem;
}
.error-icon {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}
</style>
