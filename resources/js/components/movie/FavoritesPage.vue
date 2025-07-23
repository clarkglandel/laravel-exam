<template>
    <div class="favorites-page">
        <header class="favorites-page__header">
            <nav class="favorites-page__nav">
                <router-link to="/" class="favorites-page__nav-link">
                    Movies
                </router-link>
                <router-link
                    to="/favorites"
                    class="favorites-page__nav-link favorites-page__nav-link--active"
                >
                    My Favorites
                </router-link>
            </nav>

            <SectionTitle title="My Favorite Movies" />
        </header>

        <!-- Use MovieList component structure -->
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

            <!-- Error State -->
            <div v-else-if="error" class="movie-list__error">
                <svg class="error-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"
                    />
                </svg>
                {{ error }}
                <button @click="loadFavorites" class="retry-button">
                    Try Again
                </button>
            </div>

            <!-- Empty State -->
            <div v-else-if="!favorites.length" class="movie-list__empty">
                <svg class="empty-icon" viewBox="0 0 24 24" fill="currentColor">
                    <path
                        d="M18 4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4h-4z"
                    />
                </svg>
                <h3>No favorites yet</h3>
                <p>Start adding movies to your favorites to see them here!</p>
                <router-link to="/" class="browse-button"
                    >Browse Movies</router-link
                >
            </div>

            <!-- Favorites Grid -->
            <div v-else>
                <div class="movie-grid">
                    <div
                        v-for="movie in formattedFavorites"
                        :key="movie.id"
                        class="movie-card"
                        @click="goToMovieDetails(movie.id)"
                        @favoriteChanged="handleFavoriteChanged"
                    >
                        <div class="movie-card__poster-wrapper">
                            <img
                                :src="movie.poster"
                                :alt="movie.title"
                                class="movie-card__poster"
                                loading="lazy"
                                @error="handleImageError"
                                @load="handleImageLoad"
                            />
                            <div
                                class="image-placeholder"
                                v-if="imageError[movie.id]"
                            >
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
                            <div class="movie-card__overlay">
                                <svg
                                    class="play-icon"
                                    viewBox="0 0 24 24"
                                    fill="currentColor"
                                >
                                    <path d="M8 5v14l11-7z" />
                                </svg>
                            </div>
                        </div>
                        <div class="movie-card__content">
                            <h3 class="movie-card__title">{{ movie.title }}</h3>
                            <p class="movie-card__year">{{ movie.year }}</p>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="favorites-stats">
                    <p>
                        {{ favorites.length }} movie{{
                            favorites.length !== 1 ? "s" : ""
                        }}
                        in your favorites
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, computed, reactive } from "vue";
import { useRouter } from "vue-router";
import { fetchFavorites } from "@/api/movie-api";
import SectionTitle from "@/components/common/SectionTitle.vue";

export default {
    name: "FavoritesPage",
    components: {
        SectionTitle,
    },
    setup() {
        const router = useRouter();
        const favorites = ref([]);
        const loading = ref(false);
        const error = ref(null);
        const imageError = reactive({});

        // Format favorites to match MovieList expected structure
        const formattedFavorites = computed(() => {
            return favorites.value.map((movie) => ({
                id: movie.imdb_id || movie.imdbID,
                title: movie.title || movie.Title,
                year: movie.year || movie.Year,
                poster: movie.poster || movie.Poster || movie.poster,
            }));
        });

        const loadFavorites = async () => {
            loading.value = true;
            error.value = null;

            try {
                const data = await fetchFavorites();
                favorites.value = data;
            } catch (err) {
                error.value = err.message || "Failed to load favorites";
                console.error("Failed to load favorites:", err);
            } finally {
                loading.value = false;
            }
        };

        const goToMovieDetails = (imdbId) => {
            if (imdbId) {
                router.push(`/movie/${imdbId}`);
            }
        };

        const handleImageError = (event) => {
            const movieData = formattedFavorites.value.find(
                (m) => m.title === event.target.alt
            );
            if (movieData) {
                imageError[movieData.id] = true;
                event.target.style.display = "none";
            }
        };

        const handleImageLoad = (event) => {
            const movieData = formattedFavorites.value.find(
                (m) => m.title === event.target.alt
            );
            if (movieData) {
                imageError[movieData.id] = false;
                event.target.style.display = "block";
            }
        };

        const handleFavoriteChanged = (event) => {
            // If a movie was removed from favorites, refresh the list
            if (!event.isFavorited) {
                loadFavorites();
            }
        };

        onMounted(() => {
            loadFavorites();
        });

        return {
            favorites,
            formattedFavorites,
            loading,
            error,
            imageError,
            loadFavorites,
            goToMovieDetails,
            handleImageError,
            handleImageLoad,
            handleFavoriteChanged,
        };
    },
};
</script>

<style lang="scss" scoped>
// Navigation styles
.favorites-page {
    min-height: 100vh;
    color: #f9fafb;
}

.favorites-page__header {
    text-align: center;
    padding: 2rem 1rem;
}

.favorites-page__nav {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.favorites-page__nav-link {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    color: #9ca3af;
    font-weight: 500;
    border-radius: 12px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.favorites-page__nav-link:hover {
    color: #f9fafb;
    background: rgba(255, 255, 255, 0.05);
}

.favorites-page__nav-link--active {
    color: #f9fafb;
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.2);
}

// MovieList styles (copied exactly)
:root {
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
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 4px var(--card-shadow);
}

.skeleton-poster {
    aspect-ratio: 2/3;
    background: linear-gradient(
        90deg,
        var(--loading-secondary) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        var(--loading-secondary) 100%
    );
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
}

.skeleton-content {
    padding: 1.5rem;
}

.skeleton-title {
    height: 1.2rem;
    background: linear-gradient(
        90deg,
        var(--loading-secondary) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        var(--loading-secondary) 100%
    );
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 4px;
    margin-bottom: 0.75rem;
}

.skeleton-year {
    height: 1rem;
    width: 60%;
    background: linear-gradient(
        90deg,
        var(--loading-secondary) 0%,
        rgba(255, 255, 255, 0.1) 50%,
        var(--loading-secondary) 100%
    );
    background-size: 200% 100%;
    animation: skeleton-loading 1.5s infinite;
    border-radius: 4px;
}

@keyframes skeleton-loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

/* Error State */
.movie-list__error {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 4rem 2rem;
    text-align: center;
    color: var(--error-text);
}

.error-icon {
    width: 64px;
    height: 64px;
    margin-bottom: 1rem;
    opacity: 0.7;
}

.retry-button {
    margin-top: 1rem;
    padding: 0.75rem 1.5rem;
    background: var(--error-text);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 0.2s ease;
}

.retry-button:hover {
    opacity: 0.9;
}

/* Empty State */
.movie-list__empty {
    display: flex;
    flex-direction: column;
    align-items: center;
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
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem 0;
    color: var(--text-primary);
}

.movie-list__empty p {
    font-size: 1.1rem;
    margin: 0 0 2rem 0;
    color: var(--text-secondary);
}

.browse-button {
    display: inline-block;
    padding: 0.75rem 2rem;
    background: var(--loading-primary);
    color: white;
    text-decoration: none;
    border-radius: 12px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.browse-button:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

/* Movie Grid */
.movie-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.movie-card {
    background: var(--card-bg);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px var(--card-shadow);
}

.movie-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px var(--card-shadow-hover);
}

.movie-card__poster-wrapper {
    position: relative;
    aspect-ratio: 2/3;
    overflow: hidden;
    background: var(--poster-bg);
}

.movie-card__poster {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.movie-card:hover .movie-card__poster {
    transform: scale(1.05);
}

.image-placeholder {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background: var(--poster-bg);
    color: var(--text-muted);
    border: 2px dashed var(--card-border);
    box-sizing: border-box;
}

.placeholder-icon {
    width: 48px;
    height: 48px;
    margin-bottom: 0.5rem;
    opacity: 0.5;
}

.placeholder-text {
    font-size: 0.875rem;
    font-weight: 500;
    opacity: 0.7;
}

.movie-card__overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.movie-card:hover .movie-card__overlay {
    opacity: 1;
}

.play-icon {
    width: 48px;
    height: 48px;
    color: white;
}

.movie-card__content {
    padding: 1.5rem;
}

.movie-card__title {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.movie-card__year {
    margin: 0;
    font-size: 0.9rem;
    color: var(--text-secondary);
    font-weight: 500;
}

/* Stats */
.favorites-stats {
    text-align: center;
    padding: 2rem 0;
    border-top: 1px solid var(--border-light);
}

.favorites-stats p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-style: italic;
    margin: 0;
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
    .favorites-page__header {
        padding: 1rem;
    }

    .favorites-page__nav {
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .favorites-page__nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .movie-list {
        padding: 1rem;
    }

    .skeleton-grid,
    .movie-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }

    .movie-card__content {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .skeleton-grid,
    .movie-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }

    .movie-list__empty,
    .movie-list__error {
        padding: 3rem 1rem;
    }

    .empty-icon,
    .error-icon {
        width: 48px;
        height: 48px;
    }
}
</style>
