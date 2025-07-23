import axios from "axios";
import { API_ENDPOINTS, DEFAULTS, ERROR_MESSAGES } from "@/constants";

/**
 * Search for movies by title with pagination
 * @param {string} title - Movie title to search for
 * @param {number} page - Page number for pagination
 * @returns {Promise<Object>} - Search results with pagination
 */
export const searchMovies = async (title, page = 1) => {
    try {
        const response = await axios.get(API_ENDPOINTS.MOVIES_SEARCH, {
            params: { title, page },
        });
        return response.data;
    } catch (error) {
        console.error("Movie search error:", error);
        throw new Error(
            error.request.statusText || ERROR_MESSAGES.FETCH_FAILED
        );
    }
};

/**
 * Get movie details by IMDb ID (now includes YouTube trailer data)
 * @param {string} imdbId - IMDb ID of the movie
 * @returns {Promise<Object>} - Combined movie details with trailer data
 */
export const getMovieDetails = async (imdbId) => {
    try {
        const response = await axios.get(
            `${API_ENDPOINTS.MOVIE_DETAILS}/${imdbId}`
        );

        if (!response.data) {
            throw new Error(ERROR_MESSAGES.MOVIE_NOT_FOUND);
        }

        // Handle API error responses
        if (response.data.error) {
            throw new Error(response.data.error);
        }

        return response.data;
    } catch (error) {
        console.error("Movie details error:", error);

        // Handle axios errors
        if (error.response?.data?.error) {
            throw new Error(error.response.data.error);
        }

        throw new Error(error.message || ERROR_MESSAGES.FETCH_FAILED);
    }
};

/**
 * Get movie recommendations by genre
 * @param {string} genre - Movie genre
 * @returns {Promise<Array>} - Array of recommended movies
 */
export const getMovieRecommendations = async (genre) => {
    try {
        const primaryGenre = genre ? genre.split(",")[0].trim() : "Action";
        const response = await axios.get(
            `${API_ENDPOINTS.MOVIE_RECOMMENDATIONS}/${encodeURIComponent(
                primaryGenre
            )}`
        );

        // Handle new backend response format
        if (response.data && Array.isArray(response.data.data)) {
            return response.data.data.slice(0, DEFAULTS.RECOMMENDATIONS_COUNT);
        } else if (response.data && Array.isArray(response.data.Search)) {
            return response.data.Search.slice(
                0,
                DEFAULTS.RECOMMENDATIONS_COUNT
            );
        }

        return [];
    } catch (error) {
        console.error("Recommendations error:", error);
        return []; // Return empty array instead of throwing
    }
};

/**
 * Get all favorite movies
 * @returns {Promise<Array>} - Array of favorite movie details
 */
export const fetchFavorites = async () => {
    try {
        const response = await axios.get("/api/favorites");

        if (response.data && Array.isArray(response.data.data)) {
            return response.data.data;
        }

        return [];
    } catch (error) {
        console.error("Fetch favorites error:", error);
        throw new Error(
            error.response?.data?.error || ERROR_MESSAGES.FETCH_FAILED
        );
    }
};

/**
 * Check if a movie is favorited
 * @param {string} imdbId - IMDb ID of the movie
 * @returns {Promise<Object>} - Object with is_favorited boolean
 */
export const checkFavoriteStatus = async (imdbId) => {
    try {
        const response = await axios.get(`/api/favorites/check/${imdbId}`);
        return response.data;
    } catch (error) {
        console.error("Check favorite status error:", error);
        return { is_favorited: false };
    }
};

/**
 * Toggle favorite status for a movie
 * @param {Object} movieDetails - Movie details object
 * @returns {Promise<Object>} - Response with updated favorite status
 */
export const toggleFavorite = async (movieDetails) => {
    try {
        const response = await axios.post("/api/favorites/toggle", {
            movie_details: movieDetails,
        });
        return response.data;
    } catch (error) {
        console.error("Toggle favorite error:", error);
        throw new Error(
            error.response?.data?.error || ERROR_MESSAGES.FETCH_FAILED
        );
    }
};

/**
 * Remove a movie from favorites
 * @param {string} imdbId - IMDb ID of the movie
 * @returns {Promise<Object>} - Response confirming removal
 */
export const removeFavorite = async (imdbId) => {
    try {
        const response = await axios.delete(`/api/favorites/${imdbId}`);
        return response.data;
    } catch (error) {
        console.error("Remove favorite error:", error);
        throw new Error(
            error.response?.data?.error || ERROR_MESSAGES.FETCH_FAILED
        );
    }
};
