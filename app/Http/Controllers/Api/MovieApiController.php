<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\YouTubeService;
use App\Services\MovieDataMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;

/**
 * Handles movie-related API requests using the OMDb API and YouTube API.
 */
class MovieApiController extends Controller
{
    /**
     * The OMDb API key.
     *
     * @var string|null
     */
    private string|null $omdb_key;

    /**
     * The OMDb API base URL.
     *
     * @var string
     */
    private string $omdb_url = 'https://www.omdbapi.com/';

    /**
     * YouTube service instance.
     *
     * @var YouTubeService
     */
    private YouTubeService $youtube_service;

    /**
     * Movie data mapper instance.
     *
     * @var MovieDataMapper
     */
    private MovieDataMapper $movie_mapper;

    /**
     * MovieApiController constructor.
     */
    public function __construct(YouTubeService $youtube_service, MovieDataMapper $movie_mapper)
    {
        $this->omdb_key = env('OMDB_API_KEY');
        $this->youtube_service = $youtube_service;
        $this->movie_mapper = $movie_mapper;
    }

    /**
     * Search for movies by title with custom rate limiting.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $title = $request->query('title');
        $page = $request->query('page', 1);
        $cache_key = 'movie_search_' . md5(strtolower($title)) . '_page_' . $page;
        $cached = Cache::get($cache_key);

        if ($cached) {
            Log::info('Movie search cache hit', ['cache_key' => $cache_key, 'title' => $title, 'page' => $page]);
            return response()->json($cached['data'], $cached['status']);
        }

        // Only apply rate limiting if cache is missed
        $key = 'search_' . $request->ip();
        $executed = RateLimiter::attempt(
            $key,
            5, // 5 attempts
            function () use ($request) {
                return $this->executeSearch($request);
            },
            60 // 60 seconds (1 minute)
        );

        if (!$executed) {
            // Rate limit exceeded - log it
            Log::channel('api-errors')->error('Rate limit exceeded for search endpoint', [
                'ip' => $request->ip(),
                'endpoint' => $request->path(),
                'full_url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->all(),
                'user_agent' => $request->userAgent(),
                'remaining_attempts' => RateLimiter::remaining($key, 5),
                'timestamp' => now()->toISOString(),
            ]);
            
            return response()->json([
                'error' => 'Too many requests. Please wait before trying again.',
                'retry_after' => RateLimiter::availableIn($key)
            ], 429);
        }

        return $executed;
    }
    
    /**
     * Execute the actual search logic.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    private function executeSearch(Request $request): \Illuminate\Http\JsonResponse
    {
        $title = $request->query('title');
        $page = $request->query('page', 1);

        // Manual validation
        $errors = [];
        if (!is_string($title) || trim($title) === '' || mb_strlen($title) < 2 || mb_strlen($title) > 100) {
            $errors['title'] = 'The title parameter is required and must be 2-100 characters.';
        }
        if (!is_numeric($page) || (int)$page < 1) {
            $errors['page'] = 'The page parameter must be a positive integer.';
        }
        if (!empty($errors)) {
            Log::channel('api-errors')->error('Validation error in search', [
                'errors' => $errors,
                'input' => $request->all(),
                'ip' => $request->ip(),
                'endpoint' => '/api/movies/search',
            ]);
            return response()->json([
                'error' => 'Invalid input.',
                'messages' => $errors
            ], 422);
        }
        $page = (int)$page;

        $cache_key = 'movie_search_' . md5(strtolower($title)) . '_page_' . $page;
        $ttl = 3600; // 1 hour
        $cached = Cache::get($cache_key);
        if ($cached) {
            Log::info('Movie search cache hit', ['cache_key' => $cache_key, 'title' => $title, 'page' => $page]);
            return response()->json($cached['data'], $cached['status']);
        } else {
            Log::info('Movie search cache miss', ['cache_key' => $cache_key, 'title' => $title, 'page' => $page]);
        }

        try {
            // OMDb only supports 10 results per page, so we fetch the OMDb page that contains our 5-result page
            $results_per_page = 5;
            $omdb_per_page = 10;
            $omdb_page = (int) ceil($page / 2); // Each OMDb page covers 2 frontend pages

            $response = Http::get($this->omdb_url, [
                'apikey' => $this->omdb_key,
                's' => $title,
                'page' => $omdb_page,
            ]);

            $body = json_decode($response->body(), true);

            $search_results = $body['Search'] ?? [];
            $total_results = isset($body['totalResults']) ? (int) $body['totalResults'] : 0;

            // Calculate the slice for the current 5-result page
            $start = ($page - 1) % 2 * $results_per_page;
            $paged_results = array_slice($search_results, $start, $results_per_page);

            $pagination = [
                'current_page' => $page,
                'per_page' => $results_per_page,
                'total' => $total_results,
                'last_page' => $results_per_page > 0 ? (int) ceil($total_results / $results_per_page) : 1,
                'from' => $total_results > 0 ? (($page - 1) * $results_per_page) + 1 : null,
                'to' => $total_results > 0 ? min($page * $results_per_page, $total_results) : null,
            ];

            $result = [
                'data' => [
                    'data' => $paged_results,
                    'pagination' => $pagination,
                ],
                'status' => $response->status(),
            ];
            Cache::put($cache_key, $result, $ttl);
            return response()->json($result['data'], $result['status']);
        } catch (\Exception $e) {
            Log::channel('api-errors')->error('Search API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->all(),
                'ip' => $request->ip(),
                'endpoint' => '/api/movies/search',
            ]);
            return response()->json([
                'error' => 'Internal server error while searching movies',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Get details for a specific movie by ID with integrated YouTube trailer data.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function movie(string $id): \Illuminate\Http\JsonResponse
    {
        if (!$this->omdb_key) {
            return response()->json([
                'error' => 'OMDb API key not configured',
                'code' => 'OMDB_API_KEY_MISSING'
            ], 500);
        }

        $cache_key = 'movie_details_' . $id;
        $ttl = 3600; // 1 hour
        $cached = Cache::get($cache_key);
        if ($cached) {
            Log::info('Movie details cache hit', ['cache_key' => $cache_key, 'imdb_id' => $id]);
            return response()->json($cached['data'], $cached['status']);
        } else {
            Log::info('Movie details cache miss', ['cache_key' => $cache_key, 'imdb_id' => $id]);
        }

        try {
            // Fetch movie data from OMDb API
            $omdb_response = Http::timeout(10)->get($this->omdb_url, [
                'apikey' => $this->omdb_key,
                'i' => $id,
                'plot' => 'full',
            ]);

            if (!$omdb_response->successful()) {
                Log::error('OMDb API request failed', [
                    'status' => $omdb_response->status(),
                    'body' => $omdb_response->body(),
                    'imdb_id' => $id,
                ]);
                
                return response()->json([
                    'error' => 'Failed to fetch movie data from OMDb API',
                    'code' => 'OMDB_API_ERROR'
                ], $omdb_response->status());
            }

            $omdb_data = $omdb_response->json();

            // Check if the movie was found
            if (isset($omdb_data['Response']) && $omdb_data['Response'] === 'False') {
                return response()->json([
                    'error' => $omdb_data['Error'] ?? 'Movie not found',
                    'code' => 'MOVIE_NOT_FOUND'
                ], 404);
            }

            // Fetch trailer data from YouTube (optional - don't fail if this fails)
            $youtube_data = null;
            if (!empty($omdb_data['Title'])) {
                try {
                    $youtube_data = $this->youtube_service->searchMovieTrailer($omdb_data['Title']);
                } catch (\Exception $e) {
                    Log::warning('YouTube API call failed, continuing without trailer', [
                        'error' => $e->getMessage(),
                        'movie_title' => $omdb_data['Title'],
                    ]);
                }
            }

            // Combine data from both APIs
            $combined_data = $this->movie_mapper->combineMovieData($omdb_data, $youtube_data);

            $result = [
                'data' => $combined_data,
                'status' => 200,
            ];
            Cache::put($cache_key, $result, $ttl);
            return response()->json($result['data'], $result['status']);

        } catch (\Exception $e) {
            Log::error('Movie details API failed', [
                'error' => $e->getMessage(),
                'imdb_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Internal server error while fetching movie details',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }

    /**
     * Get movie recommendations by genre (workaround using search).
     *
     * @param string $genre
     * @return \Illuminate\Http\JsonResponse
     */
    public function recommendations(string $genre): \Illuminate\Http\JsonResponse
    {
        // Generate a cache key using genre and page
        try {
            $page = random_int(1, 10);
        } catch (\Exception $e) {
            Log::warning('random_int failed in recommendations', [
                'error' => $e->getMessage(),
                'genre' => $genre,
            ]);
            $page = 1;
        }
        $cache_key = 'recommendations_' . strtolower($genre) . '_page_' . $page;
        $ttl = 3600; // 1 hour

        $cached = \Cache::get($cache_key);
        if ($cached) {
            Log::info('Recommendations cache hit', ['cache_key' => $cache_key, 'genre' => $genre, 'page' => $page]);
            return response()->json(['Search' => $cached], 200);
        } else {
            Log::info('Recommendations cache miss', ['cache_key' => $cache_key, 'genre' => $genre, 'page' => $page]);
        }

        try {
            $response = \Http::get($this->omdb_url, [
                'apikey' => $this->omdb_key,
                's' => $genre,
                'page' => $page,
            ]);

            if (!$response->ok()) {
                Log::channel('api-errors')->error('OMDb recommendations API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'genre' => $genre,
                    'endpoint' => '/api/movies/recommendations',
                ]);
                return response()->json([
                    'error' => 'Failed to fetch recommendations from OMDb API.',
                    'details' => $response->json() ?? $response->body(),
                ], $response->status());
            }

            try {
                $body = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                Log::channel('api-errors')->error('Failed to decode OMDb response', [
                    'error' => $e->getMessage(),
                    'body' => $response->body(),
                    'genre' => $genre,
                ]);
                return response()->json([
                    'error' => 'Invalid response format from OMDb API.',
                    'details' => $e->getMessage(),
                ], 500);
            }

            if (!is_array($body) || !isset($body['Search']) || !is_array($body['Search'])) {
                Log::channel('api-errors')->error('No recommendations found', [
                    'genre' => $genre,
                    'endpoint' => '/api/movies/recommendations',
                ]);
                return response()->json([
                    'error' => 'No recommendations found for the given genre.',
                    'genre' => $genre,
                    'results' => [],
                ], 404);
            }

            $results = $body['Search'];
            // Get only 3 random recommendations from the body results
            $recommendations = collect($results)->shuffle()->take(3)->values()->all();

            // Store recommendations in cache
            \Cache::put($cache_key, $recommendations, $ttl);

            return response()->json([
                'Search' => $recommendations,
            ], 200);

        } catch (\Throwable $e) {
            Log::channel('api-errors')->error('Unexpected error in recommendations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'genre' => $genre,
                'page' => $page,
            ]);
            return response()->json([
                'error' => 'Internal server error while fetching recommendations.',
                'code' => 'INTERNAL_ERROR'
            ], 500);
        }
    }
}
