<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Handles YouTube API requests for movie trailers and video content.
 */
class YouTubeService
{
    /**
     * The YouTube Data API key.
     *
     * @var string|null
     */
    private string|null $youtube_api_key;

    /**
     * The YouTube Data API base URL.
     *
     * @var string
     */
    private string $youtube_api_url = 'https://www.googleapis.com/youtube/v3/search';

    /**
     * YouTubeService constructor.
     */
    public function __construct()
    {
        $this->youtube_api_key = env('YOUTUBE_API_KEY');
    }

    /**
     * Search for a movie trailer on YouTube.
     *
     * @param string $movie_title The movie title to search for
     * @param int $max_results Maximum number of results to return (default: 1)
     * @return array|null Returns video data or null if not found
     */
    public function searchMovieTrailer(string $movie_title, int $max_results = 1): array|null
    {
        if (!$this->youtube_api_key) {
            Log::warning('YouTube API key not configured');
            return null;
        }

        if (empty(trim($movie_title))) {
            Log::warning('Empty movie title provided to YouTube search');
            return null;
        }

        try {
            $search_query = $this->formatSearchQuery($movie_title);
            
            $response = Http::timeout(10)->get($this->youtube_api_url, [
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => $max_results,
                'q' => $search_query,
                'key' => $this->youtube_api_key,
                'videoCategoryId' => '1', // Film & Animation category
                'order' => 'relevance',
                'safeSearch' => 'strict',
            ]);

            if (!$response->successful()) {
                Log::error('YouTube API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'movie_title' => $movie_title,
                ]);
                return null;
            }

            $data = $response->json();
            
            if (empty($data['items'])) {
                Log::info('No YouTube videos found for movie', ['movie_title' => $movie_title]);
                return null;
            }

            $video = $data['items'][0];
            
            return $this->formatVideoData($video);

        } catch (\Exception $e) {
            Log::error('YouTube API search failed', [
                'error' => $e->getMessage(),
                'movie_title' => $movie_title,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Format the search query for better YouTube results.
     *
     * @param string $movie_title
     * @return string
     */
    private function formatSearchQuery(string $movie_title): string
    {
        // Clean up the movie title and add trailer keywords
        $clean_title = trim($movie_title);
        
        // Remove common suffixes that might hurt search results
        $clean_title = preg_replace('/\s*\(\d{4}\)$/', '', $clean_title);
        
        return $clean_title . ' official trailer';
    }

    /**
     * Format video data from YouTube API response.
     *
     * @param array $video_data
     * @return array
     */
    private function formatVideoData(array $video_data): array
    {
        $video_id = $video_data['id']['videoId'] ?? null;
        
        if (!$video_id) {
            return [];
        }

        return [
            'video_id' => $video_id,
            'title' => $video_data['snippet']['title'] ?? 'Unknown Title',
            'description' => $video_data['snippet']['description'] ?? '',
            'thumbnail_url' => $video_data['snippet']['thumbnails']['high']['url'] ?? 
                             ($video_data['snippet']['thumbnails']['medium']['url'] ?? 
                              $video_data['snippet']['thumbnails']['default']['url'] ?? null),
            'embed_url' => "https://www.youtube.com/embed/{$video_id}",
            'watch_url' => "https://www.youtube.com/watch?v={$video_id}",
            'channel_title' => $video_data['snippet']['channelTitle'] ?? 'Unknown Channel',
            'published_at' => $video_data['snippet']['publishedAt'] ?? null,
        ];
    }

    /**
     * Get multiple trailers for a movie (alternative versions, teasers, etc.).
     *
     * @param string $movie_title
     * @param int $max_results
     * @return array
     */
    public function getMovieTrailers(string $movie_title, int $max_results = 3): array
    {
        if (!$this->youtube_api_key) {
            return [];
        }

        try {
            $search_query = $this->formatSearchQuery($movie_title);
            
            $response = Http::timeout(10)->get($this->youtube_api_url, [
                'part' => 'snippet',
                'type' => 'video',
                'maxResults' => $max_results,
                'q' => $search_query,
                'key' => $this->youtube_api_key,
                'videoCategoryId' => '1',
                'order' => 'relevance',
                'safeSearch' => 'strict',
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $trailers = [];

            foreach ($data['items'] ?? [] as $video) {
                $formatted_video = $this->formatVideoData($video);
                if (!empty($formatted_video)) {
                    $trailers[] = $formatted_video;
                }
            }

            return $trailers;

        } catch (\Exception $e) {
            Log::error('YouTube multiple trailers search failed', [
                'error' => $e->getMessage(),
                'movie_title' => $movie_title,
            ]);
            return [];
        }
    }
} 