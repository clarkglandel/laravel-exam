import { ref } from "vue";
import { getMovieDetails, getMovieRecommendations } from "@/api/movie-api";
import {
    formatMovieForRecommendation,
    getPrimaryGenre,
} from "@/utils/movie-formatter";
import { ERROR_MESSAGES } from "@/constants";

/**
 * Composable for movie details functionality
 * @returns {Object} - Movie details state and methods
 */
export const useMovieDetails = () => {
    const movie = ref({});
    const loading = ref(true);
    const error = ref("");
    const trailerUrl = ref("");
    const recommendedMovies = ref([]);
    const posterError = ref(false);

    /**
     * Fetch complete movie details including trailer and recommendations
     * @param {string} imdbId - IMDb ID of the movie
     */
    const fetchMovieDetails = async (imdbId) => {
        if (!imdbId) {
            error.value = ERROR_MESSAGES.MOVIE_NOT_FOUND;
            loading.value = false;
            return;
        }

        // Reset state
        loading.value = true;
        error.value = "";
        movie.value = {};
        trailerUrl.value = "";
        recommendedMovies.value = [];
        posterError.value = false;

        try {
            // Fetch main movie details (now includes trailer data from backend)
            const movieData = await getMovieDetails(imdbId);

            // Set movie data (using new normalized structure with backward compatibility)
            movie.value = movieData.omdb_data || movieData; // Use original OMDb format for compatibility

            // Set trailer URL from the combined response
            if (movieData.trailer?.available && movieData.trailer?.embed_url) {
                trailerUrl.value = movieData.trailer.embed_url;
            }

            // Fetch recommendations using the original genre from OMDb data
            const genreForRecommendations =
                movie.value.Genre ||
                (movieData.genre && movieData.genre.join(", "));
            if (genreForRecommendations) {
                try {
                    const recommendations = await getMovieRecommendations(
                        genreForRecommendations
                    );
                    recommendedMovies.value = recommendations.map(
                        formatMovieForRecommendation
                    );
                } catch (recError) {
                    // Don't fail the whole request if recommendations fail
                    console.warn("Failed to load recommendations:", recError);
                }
            }
        } catch (err) {
            error.value = err.message || ERROR_MESSAGES.FETCH_FAILED;
        } finally {
            loading.value = false;
        }
    };

    /**
     * Handle poster image error
     */
    const handleImageError = () => {
        posterError.value = true;
    };

    /**
     * Reset all state
     */
    const reset = () => {
        movie.value = {};
        loading.value = true;
        error.value = "";
        trailerUrl.value = "";
        recommendedMovies.value = [];
        posterError.value = false;
    };

    return {
        // State
        movie,
        loading,
        error,
        trailerUrl,
        recommendedMovies,
        posterError,
        // Methods
        fetchMovieDetails,
        handleImageError,
        reset,
    };
};
