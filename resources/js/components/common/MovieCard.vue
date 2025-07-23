<template>
    <div class="movie-card" @click="$emit('click')">
        <div class="movie-card__poster-wrapper">
            <PosterImage
                :src="movie.Poster || movie.poster"
                :alt="movie.Title || movie.title"
                :placeholder="placeholder"
                class="movie-card__poster-image"
                @error="imageError = true"
            />
            <div v-if="showOverlay && !imageError" class="movie-card__overlay">
                <slot name="overlay">
                    <svg
                        class="movie-card__play-icon"
                        viewBox="0 0 24 24"
                        fill="currentColor"
                    >
                        <path d="M8 5v14l11-7z" />
                    </svg>
                </slot>
            </div>
        </div>
        <div class="movie-card__content">
            <div class="movie-card__info">
                <h4 class="movie-card__title">
                    {{ movie.Title || movie.title }}
                </h4>
                <p class="movie-card__year">{{ movie.Year || movie.year }}</p>
            </div>
            <FavoriteButton
                v-if="hasFavoriteButton"
                :movie="movie"
                size="small"
                @favoriteChanged="handleFavoriteChanged"
                @click.stop
            />
        </div>
    </div>
</template>

<script setup>
import PosterImage from "./PosterImage.vue";
import FavoriteButton from "./FavoriteButton.vue";
import { ref } from "vue";
import { DEFAULTS } from "@/constants";

const props = defineProps({
    movie: { type: Object, required: true },
    showOverlay: { type: Boolean, default: true },
    hasFavoriteButton: { type: Boolean, default: true },
});

const emit = defineEmits(["favoriteChanged"]);

const placeholder = DEFAULTS.PLACEHOLDER_IMAGE_SMALL;
const imageError = ref(false);

/**
 * Handle favorite status changes and emit to parent
 */
function handleFavoriteChanged(event) {
    emit("favoriteChanged", event);
}
</script>

<style scoped>
.movie-card {
    cursor: pointer;
    border-radius: 12px;
    overflow: hidden;
    background: var(--card-bg, #fff);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    transition: box-shadow 0.2s, transform 0.2s;
    /* Add will-change for smoother animation */
    will-change: box-shadow, transform;
}
.movie-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    transform: scale(1.03);
}
.movie-card__poster-wrapper {
    position: relative;
    aspect-ratio: 2/3;
    background: var(--poster-bg, #f9fafb);
}
.movie-card__poster-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}
.movie-card__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(
        180deg,
        rgba(0, 0, 0, 0.32) 0%,
        rgba(0, 0, 0, 0.48) 100%
    );
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.25s;
    z-index: 2;
}
.movie-card:hover .movie-card__overlay {
    opacity: 1;
    pointer-events: auto;
}
.movie-card__play-icon {
    width: 48px;
    height: 48px;
    color: #fff;
    filter: drop-shadow(0 2px 8px rgba(0, 0, 0, 0.25));
    transition: transform 0.18s;
    z-index: 3;
    opacity: 0.92;
}
.movie-card:hover .movie-card__play-icon {
    transform: scale(1.12);
}
.movie-card__content {
    padding: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.movie-card__info {
    flex: 1;
}

.movie-card__title {
    font-size: 1rem;
    font-weight: 600;
    margin: 0 0 0.25rem 0;
}
.movie-card__year {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.movie-details__recommendations-section .movie-card__overlay {
    display: none;
}
</style>
