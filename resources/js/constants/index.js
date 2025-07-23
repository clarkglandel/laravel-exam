// API Endpoints
export const API_ENDPOINTS = {
    MOVIES_SEARCH: "/api/movies/search",
    MOVIE_DETAILS: "/api/movies",
    MOVIE_RECOMMENDATIONS: "/api/movies/recommendations",
    // YouTube API endpoint no longer needed - handled by backend
};

// Default Values
export const DEFAULTS = {
    PLACEHOLDER_IMAGE: "https://via.placeholder.com/300x450?text=No+Image",
    PLACEHOLDER_IMAGE_SMALL:
        "https://via.placeholder.com/120x180?text=No+Image",
    PAGINATION: {
        CURRENT_PAGE: 1,
        LAST_PAGE: 1,
        PER_PAGE: 10,
    },
    RECOMMENDATIONS_COUNT: 3,
    SKELETON_COUNT: 10,
};

// Error Messages
export const ERROR_MESSAGES = {
    MOVIE_NOT_FOUND: "Movie not found.",
    SEARCH_REQUIRED: "Please enter a movie title.",
    FETCH_FAILED: "Failed to fetch movie details.",
    NO_RESULTS: "No results found or an error occurred.",
    SEARCH_FAILED: "Failed to fetch movies.",
};

// Theme Configuration
export const THEME = {
    DARK: "dark-theme",
    LIGHT: "light-theme",
};
