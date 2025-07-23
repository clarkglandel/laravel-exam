<template>
    <div class="poster-container">
        <img
            v-if="!error"
            :src="src"
            :alt="alt"
            @error="onError"
            class="movie-poster"
        />
        <div v-else class="image-placeholder">
            <svg
                class="placeholder-icon"
                viewBox="0 0 24 24"
                fill="currentColor"
            >
                <path
                    d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"
                />
            </svg>
            <span class="placeholder-text">No Image</span>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { DEFAULTS } from "@/constants";

const props = defineProps({
    src: { type: String, required: true },
    alt: { type: String, default: "" },
    placeholder: {
        type: String,
        default: DEFAULTS.PLACEHOLDER_IMAGE_SMALL,
    },
});
const error = ref(false);
const emit = defineEmits(["error"]);
function onError() {
    error.value = true;
    emit("error");
}
</script>

<style scoped>
.poster-container {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: 8px;
    overflow: hidden;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.movie-poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.image-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}
.placeholder-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}
.placeholder-text {
    color: #9ca3af;
    font-size: 0.9rem;
    margin-top: 0.5rem;
}
</style>
