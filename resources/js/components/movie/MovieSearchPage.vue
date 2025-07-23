<template>
    <div class="search-page">
        <header class="search-page__header">
            <nav class="search-page__nav">
                <router-link
                    to="/"
                    class="search-page__nav-link search-page__nav-link--active"
                >
                    Movies
                </router-link>
                <router-link to="/favorites" class="search-page__nav-link">
                    My Favorites
                </router-link>
            </nav>

            <div class="search-page__hero-section">
                <h1 class="search-page__hero-title">
                    Discover Movies
                    <span class="search-page__hero-accent">You'll Love</span>
                </h1>
                <p class="search-page__hero-subtitle">
                    Search through thousands of movies and TV shows to find your
                    next favorite watch
                </p>
            </div>
        </header>

        <main class="search-page__main">
            <MovieSearch
                @search="handleSearch"
                :loading="loading"
                :title="route.query.title"
            />
            <MovieList
                :movies="movies"
                :loading="loading"
                :error="error"
                :pagination="pagination"
                @page="handlePageChange"
                @select="goToDetails"
            />
        </main>
    </div>
</template>

<script setup>
import { useRouter, useRoute } from "vue-router";
import { useMovieSearch } from "@/composables/use-movie-search";
import MovieSearch from "./MovieSearch.vue";
import MovieList from "@/components/MovieList.vue";
import { watch } from "vue";

const router = useRouter();
const route = useRoute();

// Use the movie search composable
const { movies, loading, error, pagination, search, changePage, clearSearch } =
    useMovieSearch();

/**
 * Handle search results from MovieSearch component
 * @param {Object} searchData - Search data with query and results
 */
function handleSearch(query) {
    if (!query) {
        clearSearch();
        router.replace({ query: {} }); // Clear URL query params
    } else {
        router.push({
            query: {
                title: query,
                page: 1,
            },
        });
        search(query);
    }
}

/**
 * Navigate to movie details page
 * @param {Object} movie - Movie object
 */
function goToDetails(movie) {
    router.push({ name: "movie-details", params: { imdbId: movie.id } });
}

// Remove the duplicate changePage function if present
// Wrap the imported changePage to update the URL and call the original logic
function handlePageChange(newPage) {
    router.push({
        query: {
            title: router.currentRoute.value.query.title,
            page: newPage,
        },
    });
    changePage(newPage);
}

// Watch for changes in the query params and trigger search if both exist
watch(
    () => [route.query.title, route.query.page],
    ([title, page]) => {
        if (title && page) {
            search(title, page);
        }
    },
    { immediate: true }
);
</script>

<style scoped>
:root {
    --bg-primary: #0f172a;
    --bg-secondary: #1e293b;
    --text-primary: #f9fafb;
    --text-secondary: #9ca3af;
    --surface: rgba(31, 41, 55, 0.9);
    --border: rgba(255, 255, 255, 0.1);
    --shadow: rgba(0, 0, 0, 0.3);
}

.search-page {
    min-height: 100vh;
    transition: all 0.3s ease;
    color: var(--text-primary);
}

.search-page__header {
    position: relative;
    padding: 3rem 1rem 2rem;
    text-align: center;
}

.search-page__nav {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin-bottom: 2rem;
}

.search-page__nav-link {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    color: var(--text-secondary);
    font-weight: 500;
    border-radius: 12px;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.search-page__nav-link:hover {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.05);
}

.search-page__nav-link--active {
    color: var(--text-primary);
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.2);
}

.search-page__hero-section {
    max-width: 800px;
    margin: 0 auto;
}

.search-page__hero-title {
    font-size: clamp(2.5rem, 5vw, 4rem);
    font-weight: 800;
    color: var(--text-primary);
    margin: 0 0 1rem 0;
    line-height: 1.2;
    letter-spacing: -0.025em;
}

.search-page__hero-accent {
    background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.search-page__hero-subtitle {
    font-size: 1.25rem;
    color: var(--text-secondary);
    margin: 0;
    line-height: 1.6;
    max-width: 600px;
    margin: 0 auto;
}

.search-page__main {
    padding: 2rem 0;
}

/* Responsive design */
@media (max-width: 768px) {
    .search-page__header {
        padding: 2rem 1rem 1.5rem;
    }

    .search-page__nav {
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .search-page__nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }

    .search-page__hero-title {
        font-size: 2.5rem;
    }

    .search-page__hero-subtitle {
        font-size: 1.1rem;
    }
}
</style>
