<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * Maps and combines data from OMDb API and YouTube API into unified movie data structure.
 */
class MovieDataMapper
{
    /**
     * Combine OMDb movie data with YouTube trailer data.
     *
     * @param array $omdb_data Raw data from OMDb API
     * @param array|null $youtube_data Formatted data from YouTube API
     * @return array Combined and normalized movie data
     */
    public function combineMovieData(array $omdb_data, ?array $youtube_data = null): array
    {
        $mapped_data = $this->mapOmdbData($omdb_data);
        
        if ($youtube_data) {
            $mapped_data['trailer'] = $this->mapYouTubeData($youtube_data);
        }
        
        $mapped_data['data_sources'] = $this->getDataSources($omdb_data, $youtube_data);
        $mapped_data['last_updated'] = now()->toISOString();
        
        return $mapped_data;
    }

    /**
     * Map and normalize OMDb API data.
     *
     * @param array $omdb_data
     * @return array
     */
    private function mapOmdbData(array $omdb_data): array
    {
        return [
            // Basic movie information
            'imdb_id' => $omdb_data['imdbID'] ?? null,
            'title' => $omdb_data['Title'] ?? 'Unknown Title',
            'year' => $this->parseYear($omdb_data['Year'] ?? null),
            'release_date' => $this->parseReleaseDate($omdb_data['Released'] ?? null),
            'runtime' => $this->parseRuntime($omdb_data['Runtime'] ?? null),
            'genre' => $this->parseGenres($omdb_data['Genre'] ?? ''),
            'plot' => $omdb_data['Plot'] ?? '',
            
            // Ratings and scores
            'ratings' => $this->parseRatings($omdb_data),
            'imdb_rating' => $this->parseImdbRating($omdb_data['imdbRating'] ?? null),
            'imdb_votes' => $this->parseImdbVotes($omdb_data['imdbVotes'] ?? null),
            
            // Cast and crew
            'director' => $this->parseDirectors($omdb_data['Director'] ?? ''),
            'writer' => $this->parseWriters($omdb_data['Writer'] ?? ''),
            'actors' => $this->parseActors($omdb_data['Actors'] ?? ''),
            
            // Production details
            'country' => $this->parseCountries($omdb_data['Country'] ?? ''),
            'language' => $this->parseLanguages($omdb_data['Language'] ?? ''),
            'awards' => $omdb_data['Awards'] ?? null,
            'production' => $omdb_data['Production'] ?? null,
            'box_office' => $omdb_data['BoxOffice'] ?? null,
            
            // Media
            'poster' => $this->normalizePosterUrl($omdb_data['Poster'] ?? null),
            'type' => strtolower($omdb_data['Type'] ?? 'movie'),
            
            // Additional metadata
            'rated' => $omdb_data['Rated'] ?? null,
            'metascore' => $this->parseMetascore($omdb_data['Metascore'] ?? null),
            'dvd_release' => $this->parseReleaseDate($omdb_data['DVD'] ?? null),
            'website' => $omdb_data['Website'] ?? null,
            
            // Original OMDb data for backward compatibility
            'omdb_data' => $omdb_data,
        ];
    }

    /**
     * Map YouTube trailer data.
     *
     * @param array $youtube_data
     * @return array
     */
    private function mapYouTubeData(array $youtube_data): array
    {
        return [
            'available' => !empty($youtube_data['video_id']),
            'video_id' => $youtube_data['video_id'] ?? null,
            'title' => $youtube_data['title'] ?? null,
            'description' => $youtube_data['description'] ?? null,
            'thumbnail_url' => $youtube_data['thumbnail_url'] ?? null,
            'embed_url' => $youtube_data['embed_url'] ?? null,
            'watch_url' => $youtube_data['watch_url'] ?? null,
            'channel_title' => $youtube_data['channel_title'] ?? null,
            'published_at' => $youtube_data['published_at'] ?? null,
        ];
    }

    /**
     * Parse year from OMDb year string.
     *
     * @param string|null $year_string
     * @return int|null
     */
    private function parseYear(?string $year_string): ?int
    {
        if (!$year_string) {
            return null;
        }
        
        // Extract year from formats like "2023", "2023-", "2020-2023"
        if (preg_match('/(\d{4})/', $year_string, $matches)) {
            return (int) $matches[1];
        }
        
        return null;
    }

    /**
     * Parse release date from OMDb date string.
     *
     * @param string|null $date_string
     * @return string|null
     */
    private function parseReleaseDate(?string $date_string): ?string
    {
        if (!$date_string || $date_string === 'N/A') {
            return null;
        }
        
        try {
            return Carbon::parse($date_string)->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse runtime from OMDb runtime string.
     *
     * @param string|null $runtime_string
     * @return array|null
     */
    private function parseRuntime(?string $runtime_string): ?array
    {
        if (!$runtime_string || $runtime_string === 'N/A') {
            return null;
        }
        
        // Extract minutes from "120 min" format
        if (preg_match('/(\d+)\s*min/', $runtime_string, $matches)) {
            $minutes = (int) $matches[1];
            return [
                'minutes' => $minutes,
                'formatted' => $runtime_string,
                'hours_minutes' => sprintf('%dh %dm', floor($minutes / 60), $minutes % 60),
            ];
        }
        
        return ['formatted' => $runtime_string];
    }

    /**
     * Parse genres from OMDb genre string.
     *
     * @param string $genre_string
     * @return array
     */
    private function parseGenres(string $genre_string): array
    {
        if (empty($genre_string) || $genre_string === 'N/A') {
            return [];
        }
        
        $genres = explode(',', $genre_string);
        return array_map('trim', $genres);
    }

    /**
     * Parse and normalize ratings from OMDb data.
     *
     * @param array $omdb_data
     * @return array
     */
    private function parseRatings(array $omdb_data): array
    {
        $ratings = [];
        
        // Parse ratings array from OMDb
        if (isset($omdb_data['Ratings']) && is_array($omdb_data['Ratings'])) {
            foreach ($omdb_data['Ratings'] as $rating) {
                $source = strtolower(str_replace(' ', '_', $rating['Source'] ?? ''));
                $ratings[$source] = [
                    'source' => $rating['Source'] ?? '',
                    'value' => $rating['Value'] ?? '',
                    'normalized' => $this->normalizeRating($rating['Value'] ?? '', $source),
                ];
            }
        }
        
        return $ratings;
    }

    /**
     * Parse IMDb rating.
     *
     * @param string|null $rating
     * @return float|null
     */
    private function parseImdbRating(?string $rating): ?float
    {
        if (!$rating || $rating === 'N/A') {
            return null;
        }
        
        return (float) $rating;
    }

    /**
     * Parse IMDb votes count.
     *
     * @param string|null $votes
     * @return int|null
     */
    private function parseImdbVotes(?string $votes): ?int
    {
        if (!$votes || $votes === 'N/A') {
            return null;
        }
        
        // Remove commas and parse number
        return (int) str_replace(',', '', $votes);
    }

    /**
     * Parse directors from OMDb director string.
     *
     * @param string $director_string
     * @return array
     */
    private function parseDirectors(string $director_string): array
    {
        return $this->parsePersonList($director_string);
    }

    /**
     * Parse writers from OMDb writer string.
     *
     * @param string $writer_string
     * @return array
     */
    private function parseWriters(string $writer_string): array
    {
        return $this->parsePersonList($writer_string);
    }

    /**
     * Parse actors from OMDb actor string.
     *
     * @param string $actor_string
     * @return array
     */
    private function parseActors(string $actor_string): array
    {
        return $this->parsePersonList($actor_string);
    }

    /**
     * Parse a comma-separated list of people.
     *
     * @param string $person_string
     * @return array
     */
    private function parsePersonList(string $person_string): array
    {
        if (empty($person_string) || $person_string === 'N/A') {
            return [];
        }
        
        $people = explode(',', $person_string);
        return array_map('trim', $people);
    }

    /**
     * Parse countries from OMDb country string.
     *
     * @param string $country_string
     * @return array
     */
    private function parseCountries(string $country_string): array
    {
        return $this->parsePersonList($country_string); // Same logic as person list
    }

    /**
     * Parse languages from OMDb language string.
     *
     * @param string $language_string
     * @return array
     */
    private function parseLanguages(string $language_string): array
    {
        return $this->parsePersonList($language_string); // Same logic as person list
    }

    /**
     * Normalize poster URL.
     *
     * @param string|null $poster_url
     * @return string|null
     */
    private function normalizePosterUrl(?string $poster_url): ?string
    {
        if (!$poster_url || $poster_url === 'N/A') {
            return null;
        }
        
        // Return high-quality poster URL if available
        return str_replace('SX300', 'SX600', $poster_url);
    }

    /**
     * Parse Metascore.
     *
     * @param string|null $metascore
     * @return int|null
     */
    private function parseMetascore(?string $metascore): ?int
    {
        if (!$metascore || $metascore === 'N/A') {
            return null;
        }
        
        return (int) $metascore;
    }

    /**
     * Normalize rating to a 0-10 scale.
     *
     * @param string $rating_value
     * @param string $source
     * @return float|null
     */
    private function normalizeRating(string $rating_value, string $source): ?float
    {
        if (empty($rating_value)) {
            return null;
        }
        
        switch ($source) {
            case 'internet_movie_database':
                // Already 0-10 scale
                return (float) str_replace('/10', '', $rating_value);
            
            case 'rotten_tomatoes':
                // Convert percentage to 0-10
                $percentage = (int) str_replace('%', '', $rating_value);
                return $percentage / 10;
            
            case 'metacritic':
                // Convert 0-100 to 0-10
                $score = (int) str_replace('/100', '', $rating_value);
                return $score / 10;
            
            default:
                // Try to extract numeric value
                if (preg_match('/(\d+\.?\d*)/', $rating_value, $matches)) {
                    return (float) $matches[1];
                }
                return null;
        }
    }

    /**
     * Get information about data sources used.
     *
     * @param array $omdb_data
     * @param array|null $youtube_data
     * @return array
     */
    private function getDataSources(array $omdb_data, ?array $youtube_data): array
    {
        $sources = [
            'omdb' => [
                'used' => !empty($omdb_data),
                'data_quality' => $this->assessOmdbDataQuality($omdb_data),
            ],
            'youtube' => [
                'used' => !empty($youtube_data),
                'trailer_found' => !empty($youtube_data['video_id'] ?? null),
            ],
        ];
        
        return $sources;
    }

    /**
     * Assess the quality of OMDb data.
     *
     * @param array $omdb_data
     * @return string
     */
    private function assessOmdbDataQuality(array $omdb_data): string
    {
        $quality_score = 0;
        $required_fields = ['Title', 'Year', 'Plot', 'Director', 'Actors'];
        
        foreach ($required_fields as $field) {
            if (!empty($omdb_data[$field]) && $omdb_data[$field] !== 'N/A') {
                $quality_score++;
            }
        }
        
        if ($quality_score >= 4) {
            return 'high';
        } elseif ($quality_score >= 2) {
            return 'medium';
        } else {
            return 'low';
        }
    }
} 