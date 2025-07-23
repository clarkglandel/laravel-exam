<template>
    <div class="movie-details">
        <!-- Back Button -->
        <button class="movie-details__back-button" @click="goBack">
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path
                    d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"
                />
            </svg>
            Back
        </button>

        <!-- Loading State -->
        <MovieDetailsSkeleton v-if="loading" />

        <!-- Error State -->
        <ErrorMessage v-if="error" :message="error" />

        <!-- Movie Details -->
        <div
            v-else-if="!loading && !error"
            class="movie-details__movie-content"
        >
            <!-- Main Movie Section -->
            <div class="movie-details__main">
                <!-- Movie Poster -->
                <div class="movie-details__poster-container">
                    <PosterImage
                        :src="
                            movie.Poster !== 'N/A' ? movie.Poster : placeholder
                        "
                        :alt="movie.Title"
                        :placeholder="placeholder"
                        @error="handleImageError"
                    />
                </div>

                <!-- Movie Info -->
                <div class="movie-details__info">
                    <h1 class="movie-details__title">{{ movie.Title }}</h1>

                    <div class="movie-details__meta">
                        <span class="movie-details__genre">{{
                            movie.Genre
                        }}</span>
                        <span class="movie-details__rating">
                            <svg
                                class="movie-details__star-icon"
                                viewBox="0 0 24 24"
                                fill="currentColor"
                            >
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                                />
                            </svg>
                            {{ movie.imdbRating || "N/A" }}
                        </span>
                        <span class="movie-details__year">{{
                            movie.Year
                        }}</span>
                        <span class="movie-details__runtime">{{
                            movie.Runtime
                        }}</span>
                    </div>

                    <!-- Plot Summary -->
                    <div class="movie-details__plot-section">
                        <SectionTitle title="Plot Summary" />
                        <p class="movie-details__plot-text">{{ movie.Plot }}</p>
                    </div>

                    <!-- Additional Info -->
                    <div class="movie-details__additional-info">
                        <div
                            class="movie-details__info-item"
                            v-if="movie.Director"
                        >
                            <strong>Director:</strong> {{ movie.Director }}
                        </div>
                        <div
                            class="movie-details__info-item"
                            v-if="movie.Actors"
                        >
                            <strong>Cast:</strong> {{ movie.Actors }}
                        </div>
                        <div
                            class="movie-details__info-item"
                            v-if="movie.Released"
                        >
                            <strong>Released:</strong> {{ movie.Released }}
                        </div>
                    </div>

                    <!-- Favorite Toggle Button -->
                    <FavoriteButton
                        :movie="movie"
                        size="medium"
                        :show-text="true"
                        @favoriteChanged="handleFavoriteChanged"
                    />
                </div>
            </div>

            <!-- YouTube Trailer -->
            <div v-if="trailerUrl" class="movie-details__trailer-section">
                <SectionTitle title="Official Trailer" />
                <div class="movie-details__trailer-container">
                    <iframe
                        :src="trailerUrl"
                        frameborder="0"
                        allowfullscreen
                        class="movie-details__trailer-iframe"
                    ></iframe>
                </div>
            </div>

            <!-- Recommended Movies Section -->
            <div class="movie-details__recommendations-section">
                <SectionTitle title="Recommended Movies" />
                <div
                    v-if="recommendedMovies.length > 0"
                    class="movie-details__recommendations-grid"
                >
                    <div
                        v-for="recMovie in recommendedMovies"
                        :key="recMovie.imdbID"
                        class="movie-details__recommendation"
                    >
                        <MovieCard
                            :movie="recMovie"
                            :hasFavoriteButton="false"
                            @click="goToMovie(recMovie.imdbID)"
                        />
                    </div>
                </div>
                <div v-else class="movie-details__no-recommendations">
                    <p>No recommendations available</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, toRaw } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useMovieDetails } from "@/composables/use-movie-details";
import { DEFAULTS } from "@/constants";

// Components
import PosterImage from "@/components/common/PosterImage.vue";
import MovieCard from "@/components/common/MovieCard.vue";
import ErrorMessage from "@/components/common/ErrorMessage.vue";
import SectionTitle from "@/components/common/SectionTitle.vue";
import MovieDetailsSkeleton from "@/components/movie/MovieDetailsSkeleton.vue";
import FavoriteButton from "@/components/common/FavoriteButton.vue";

const props = defineProps({ imdbId: String });
const route = useRoute();
const router = useRouter();

// Use the movie details composable
const {
    movie,
    loading,
    error,
    trailerUrl,
    recommendedMovies,
    posterError,
    fetchMovieDetails,
    handleImageError,
} = useMovieDetails();

/**
 * Handle favorite status changes
 * @param {Object} event - Event object with movie, isFavorited, and action
 */
function handleFavoriteChanged(event) {
    console.log(
        `Movie ${event.action}:`,
        event.movie.Title || event.movie.title
    );
}

const placeholder = DEFAULTS.PLACEHOLDER_IMAGE;

/**
 * Get the IMDb ID from props or route params
 * @returns {string} - IMDb ID
 */
function getImdbId() {
    return props.imdbId || route.params.imdbId;
}

/**
 * Navigate back to home page
 */
function goBack() {
    router.go(-1);
}

/**
 * Navigate to another movie's details
 * @param {string} imdbId - IMDb ID of the movie
 */
function goToMovie(imdbId) {
    router.push({ name: "movie-details", params: { imdbId } });
}

// Watch for route changes and fetch movie details
watch(() => getImdbId(), fetchMovieDetails, { immediate: true });
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
    --details-bg: #ffffff;
    --details-text: #111827;
    --details-text-secondary: #6b7280;
    --details-text-muted: #9ca3af;
    --details-border: #e5e7eb;
    --details-shadow: rgba(0, 0, 0, 0.1);
    --details-surface: #f9fafb;
    --details-accent: #3b82f6;
}

.movie-details {
    min-height: 100vh;
    color: var(--details-text);
    padding: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    min-width: 100%;
}

/* Back Button */
.movie-details__back-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: var(--details-bg);
    border: 1px solid var(--details-border);
    border-radius: 12px;
    color: var(--details-text);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px var(--details-shadow);
}

.movie-details__back-button:hover {
    background: var(--details-surface);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px var(--details-shadow);
}

.movie-details__back-button svg {
    width: 20px;
    height: 20px;
}

/* Main Movie Section */
.movie-details__main {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
    background: var(--details-bg);
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 4px 6px -1px var(--details-shadow);
}

/* Movie Poster */
.movie-details__poster-container {
    position: relative;
    aspect-ratio: 2/3;
    border-radius: 12px;
    overflow: hidden;
    background: var(--details-surface);
}

/* Movie Info */
.movie-details__info {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.movie-details__title {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0;
    color: var(--details-text);
    line-height: 1.2;
}

.movie-details__meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    align-items: center;
}

.movie-details__genre {
    background: var(--details-accent);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 500;
}

.movie-details__rating {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 600;
    color: #fbbf24;
}

.movie-details__star-icon {
    width: 16px;
    height: 16px;
}

.movie-details__year,
.movie-details__runtime {
    color: var(--details-text-secondary);
    font-weight: 500;
}

/* Plot Section */
.movie-details__plot-text {
    font-size: 1.1rem;
    line-height: 1.7;
    color: var(--details-text-secondary);
    margin: 0;
}

/* Additional Info */
.movie-details__additional-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.movie-details__info-item {
    font-size: 1rem;
    color: var(--details-text-secondary);
}

.movie-details__info-item strong {
    color: var(--details-text);
}

/* Trailer Section */
.movie-details__trailer-section {
    margin-bottom: 3rem;
}

.movie-details__trailer-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 aspect ratio */
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px -1px var(--details-shadow);
}

.movie-details__trailer-iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* Recommendations Section */
.movie-details__recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1.5rem;
}

.movie-details__no-recommendations {
    text-align: center;
    padding: 2rem;
    color: var(--details-text-muted);
}

.movie-details__favorite-button {
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background: var(--details-accent);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}
.movie-details__favorite-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.movie-details__favorite-success {
    color: #22c55e;
    margin-top: 0.5rem;
    font-weight: 500;
}
.movie-details__favorite-error {
    color: #ef4444;
    margin-top: 0.5rem;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .movie-details__main {
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }

    .movie-details__title {
        font-size: 2rem;
    }
}

@media (max-width: 768px) {
    .movie-details {
        padding: 1rem;
    }

    .movie-details__main {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        text-align: center;
    }

    .movie-details__poster-container {
        max-width: 300px;
        margin: 0 auto;
    }

    .movie-details__info {
        text-align: left;
    }

    .movie-details__title {
        font-size: 1.75rem;
    }

    .movie-details__recommendations-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .movie-details__back-button {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    .movie-details__main {
        padding: 1.5rem;
    }

    .movie-details__title {
        font-size: 1.5rem;
    }

    .movie-details__meta {
        justify-content: center;
        text-align: center;
    }

    .movie-details__recommendations-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
