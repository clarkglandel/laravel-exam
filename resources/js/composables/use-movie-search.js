import { ref } from "vue";
import { searchMovies } from "@/api/movie-api";
import { formatMoviesForList, formatPagination } from "@/utils/movie-formatter";
import { ERROR_MESSAGES, DEFAULTS } from "@/constants";

/**
 * Composable for movie search functionality
 * @returns {Object} - Search state and methods
 */
export const useMovieSearch = () => {
    const movies = ref([]);
    const loading = ref(false);
    const error = ref("");
    const pagination = ref({ ...DEFAULTS.PAGINATION });
    const lastQuery = ref("");

    /**
     * Search for movies by title
     * @param {string} query - Search query
     * @param {number} page - Page number
     */
    const search = async (query, page = 1) => {
        if (!query?.trim()) {
            error.value = ERROR_MESSAGES.SEARCH_REQUIRED;
            return;
        }

        loading.value = true;
        error.value = "";
        lastQuery.value = query;

        try {
            const response = await searchMovies(query, page);

            if (response?.data && Array.isArray(response.data)) {
                movies.value = formatMoviesForList(response.data);
                pagination.value = formatPagination(response.pagination);
            } else {
                movies.value = [];
                pagination.value = { ...DEFAULTS.PAGINATION };
                error.value = response?.Error || ERROR_MESSAGES.NO_RESULTS;
            }
        } catch (err) {
            error.value = err.message || ERROR_MESSAGES.NO_RESULTS;
            movies.value = [];
            pagination.value = { ...DEFAULTS.PAGINATION };
        } finally {
            loading.value = false;
        }
    };

    /**
     * Change page in search results
     * @param {number} page - New page number
     */
    const changePage = async (page) => {
        if (!lastQuery.value) return;
        await search(lastQuery.value, page);
    };

    /**
     * Clear search results and state
     */
    const clearSearch = () => {
        movies.value = [];
        error.value = "";
        lastQuery.value = "";
        pagination.value = { ...DEFAULTS.PAGINATION };
    };

    return {
        // State
        movies,
        loading,
        error,
        pagination,
        lastQuery,
        // Methods
        search,
        changePage,
        clearSearch,
    };
};
