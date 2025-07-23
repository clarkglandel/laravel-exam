import { DEFAULTS } from "@/constants";

/**
 * Transform raw movie data from API to standardized format
 * @param {Object} rawMovie - Raw movie data from API
 * @returns {Object} - Standardized movie object
 */
export const formatMovieForList = (rawMovie) => {
    return {
        id: rawMovie.imdbID,
        title: rawMovie.Title,
        year: rawMovie.Year,
        poster:
            rawMovie.Poster !== "N/A"
                ? rawMovie.Poster
                : DEFAULTS.PLACEHOLDER_IMAGE_SMALL,
    };
};

/**
 * Transform multiple movies for list display
 * @param {Array} movies - Array of raw movie data
 * @returns {Array} - Array of formatted movie objects
 */
export const formatMoviesForList = (movies) => {
    if (!Array.isArray(movies)) return [];
    return movies.map(formatMovieForList);
};

/**
 * Transform movie data for recommendations
 * @param {Object} movie - Raw movie data
 * @returns {Object} - Formatted movie for recommendations
 */
export const formatMovieForRecommendation = (movie) => {
    return {
        ...movie,
        poster: movie.Poster,
        title: movie.Title,
        year: movie.Year,
    };
};

/**
 * Extract primary genre from genre string
 * @param {string} genreString - Comma-separated genre string
 * @returns {string} - Primary genre or default
 */
export const getPrimaryGenre = (genreString) => {
    return genreString ? genreString.split(",")[0].trim() : "Action";
};

/**
 * Format pagination data consistently
 * @param {Object} paginationData - Raw pagination from API
 * @returns {Object} - Standardized pagination object
 */
export const formatPagination = (paginationData) => {
    if (!paginationData) {
        return { ...DEFAULTS.PAGINATION };
    }

    return {
        page: paginationData.current_page || DEFAULTS.PAGINATION.CURRENT_PAGE,
        total: paginationData.last_page || DEFAULTS.PAGINATION.LAST_PAGE,
    };
};
