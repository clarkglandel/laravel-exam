<template>
    <div class="movie-list">
        <!-- Loading State -->
        <div v-if="loading" class="movie-list__loading">
            <div class="skeleton-grid">
                <div v-for="n in 5" :key="n" class="skeleton-card">
                    <div class="skeleton-poster"></div>
                    <div class="skeleton-content">
                        <div class="skeleton-title"></div>
                        <div class="skeleton-year"></div>
                    </div>
                </div>
            </div>
        </div>
        <div v-else-if="error" class="movie-list__error">
            <svg class="error-icon" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M18.3 5.71a1 1 0 0 0-1.41 0L12 10.59 7.11 5.7A1 1 0 0 0 5.7 7.11L10.59 12l-4.89 4.89a1 1 0 1 0 1.41 1.41L12 13.41l4.89 4.89a1 1 0 0 0 1.41-1.41L13.41 12l4.89-4.89a1 1 0 0 0 0-1.4z"
                />
            </svg>
            {{ error }}
        </div>
        <div v-else-if="movies && movies.length > 0">
            <div class="movie-grid">
                <MovieCard
                    v-for="movie in movies"
                    :key="movie.id"
                    :movie="movie"
                    :hasFavoriteButton="hasFavoriteButton"
                    @click="$emit('select', movie)"
                    @favoriteChanged="handleFavoriteChanged"
                />
            </div>

            <!-- Pagination -->
            <div class="pagination" v-if="pagination && pagination.total > 1">
                <button
                    class="pagination__btn pagination__btn--prev"
                    :disabled="pagination.page === 1"
                    @click="$emit('page', pagination.page - 1)"
                >
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"
                        />
                    </svg>
                    Previous
                </button>

                <div class="pagination__info">
                    Page {{ pagination.page }} of {{ pagination.total }}
                </div>

                <button
                    class="pagination__btn pagination__btn--next"
                    :disabled="pagination.page === pagination.total"
                    @click="$emit('page', pagination.page + 1)"
                >
                    Next
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"
                        />
                    </svg>
                </button>
            </div>
        </div>
        <div v-else class="movie-list__empty">
            <svg class="empty-icon" viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4z"
                />
            </svg>
            <h3>No movies found</h3>
            <p>Try searching for your favorite movie or TV show</p>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from "vue";
import MovieCard from "@/components/common/MovieCard.vue";

const props = defineProps({
    movies: Array,
    loading: Boolean,
    error: String,
    pagination: Object,
    hasFavoriteButton: { type: Boolean, default: false },
});

const emit = defineEmits(["select", "page", "favoriteChanged"]);

const imageError = reactive({});

function handleImageError(event) {
    const movieId = event.target.alt; // Using alt text which contains movie title
    // Find the movie ID from the image's parent element or data attribute
    const movieCard = event.target.closest(".movie-card");
    const movieData = props.movies.find((m) => m.title === event.target.alt);
    if (movieData) {
        imageError[movieData.id] = true;
        event.target.style.display = "none";
    }
}

function handleImageLoad(event) {
    const movieCard = event.target.closest(".movie-card");
    const movieData = props.movies.find((m) => m.title === event.target.alt);
    if (movieData) {
        imageError[movieData.id] = false;
        event.target.style.display = "block";
    }
}

function handleFavoriteChanged(event) {
    // Emit the favorite change event to parent components
    emit("favoriteChanged", event);
}
</script>

<style scoped>
:root {
    --card-bg: #ffffff;
    --card-border: #f3f4f6;
    --card-shadow: rgba(0, 0, 0, 0.1);
    --card-shadow-hover: rgba(0, 0, 0, 0.1);
    --text-primary: #111827;
    --text-secondary: #6b7280;
    --text-muted: #9ca3af;
    --loading-primary: #3b82f6;
    --loading-secondary: #f3f4f6;
    --error-text: #ef4444;
    --btn-bg: #ffffff;
    --btn-border: #d1d5db;
    --btn-text: #374151;
    --btn-hover-bg: #f9fafb;
    --btn-hover-border: #9ca3af;
    --border-light: #f3f4f6;
    --poster-bg: #f9fafb;
}

:root.dark-theme {
    --card-bg: #1f2937;
    --card-border: #374151;
    --card-shadow: rgba(0, 0, 0, 0.3);
    --card-shadow-hover: rgba(0, 0, 0, 0.4);
    --text-primary: #f9fafb;
    --text-secondary: #9ca3af;
    --text-muted: #6b7280;
    --loading-primary: #3b82f6;
    --loading-secondary: #374151;
    --error-text: #ef4444;
    --btn-bg: #1f2937;
    --btn-border: #374151;
    --btn-text: #f9fafb;
    --btn-hover-bg: #374151;
    --btn-hover-border: #4b5563;
    --border-light: #374151;
    --poster-bg: #374151;
}

.movie-list {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

/* Loading State - Skeleton */
.movie-list__loading {
    padding: 2rem 0;
}

.skeleton-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
}

.skeleton-card {
    background: var(--card-bg);
    border-radius: 16px;
    overflow: hidden;
    border: 1px solid var(--card-border);
    box-shadow: 0 4px 6px -1px var(--card-shadow);
    min-width: 212px;
}

.skeleton-poster {
    aspect-ratio: 2/3;
    background: linear-gradient(
        90deg,
        var(--poster-bg) 25%,
        var(--card-border) 50%,
        var(--poster-bg) 75%
    );
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}

.skeleton-content {
    padding: 1.5rem;
}

.skeleton-title {
    height: 1.1rem;
    background: linear-gradient(
        90deg,
        var(--poster-bg) 25%,
        var(--card-border) 50%,
        var(--poster-bg) 75%
    );
    background-size: 200% 100%;
    border-radius: 4px;
    margin-bottom: 0.5rem;
    animation: skeleton-loading 1.5s infinite;
}

.skeleton-year {
    height: 0.9rem;
    width: 60%;
    background: linear-gradient(
        90deg,
        var(--poster-bg) 25%,
        var(--card-border) 50%,
        var(--poster-bg) 75%
    );
    background-size: 200% 100%;
    border-radius: 4px;
    animation: skeleton-loading 1.5s infinite;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.loading-spinner {
    width: 32px;
    height: 32px;
    border: 3px solid var(--loading-secondary);
    border-top: 3px solid var(--loading-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* Error State */
.movie-list__error {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    color: var(--error-text);
    font-size: 1.1rem;
    text-align: center;
}

.error-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 1rem;
    opacity: 0.7;
}

/* Empty State */
.movie-list__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
    color: var(--text-muted);
}

.empty-icon {
    width: 64px;
    height: 64px;
    margin-bottom: 1.5rem;
    opacity: 0.5;
}

.movie-list__empty h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
}

.movie-list__empty p {
    margin: 0;
    font-size: 1rem;
    opacity: 0.7;
}

/* Movie Grid */
.movie-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1rem;
    margin-bottom: 3rem;
}

/* Pagination */
.pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.5rem;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-light);
}

.pagination__btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--btn-bg);
    border: 1px solid var(--btn-border);
    border-radius: 12px;
    color: var(--btn-text);
    font-weight: 500;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pagination__btn:hover:not(:disabled) {
    background: var(--btn-hover-bg);
    border-color: var(--btn-hover-border);
}

.pagination__btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination__btn svg {
    width: 18px;
    height: 18px;
}

.pagination__info {
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .skeleton-grid,
    .movie-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 1024px) {
    .skeleton-grid,
    .movie-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 768px) {
    .movie-list {
        padding: 1rem;
    }

    .movie-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .skeleton-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
    }

    .pagination {
        flex-direction: column;
        gap: 1rem;
    }

    .pagination__btn {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 480px) {
    .skeleton-grid,
    .movie-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }

    .pagination__btn {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }
}
</style>
