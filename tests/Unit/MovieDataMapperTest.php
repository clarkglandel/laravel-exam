<?php

namespace Tests\Unit;

use App\Services\MovieDataMapper;
use PHPUnit\Framework\TestCase;

class MovieDataMapperTest extends TestCase
{
    /**
     * Test combineMovieData merges OMDb and YouTube data correctly.
     */
    public function test_combine_movie_data_merges_and_normalizes()
    {
        $mapper = new MovieDataMapper();

        $omdb_data = [
            'imdbID' => 'tt1375666',
            'Title' => 'Inception',
            'Year' => '2010',
            'Released' => '16 Jul 2010',
            'Runtime' => '148 min',
            'Genre' => 'Action, Adventure, Sci-Fi',
            'Plot' => 'A thief who steals corporate secrets...',
            'Ratings' => [
                ['Source' => 'Internet Movie Database', 'Value' => '8.8/10'],
                ['Source' => 'Rotten Tomatoes', 'Value' => '87%'],
            ],
            'imdbRating' => '8.8',
            'imdbVotes' => '2,000,000',
            'Director' => 'Christopher Nolan',
            'Writer' => 'Christopher Nolan',
            'Actors' => 'Leonardo DiCaprio, Joseph Gordon-Levitt',
            'Country' => 'USA, UK',
            'Language' => 'English, Japanese, French',
            'Awards' => 'Won 4 Oscars.',
            'Production' => 'Warner Bros.',
            'BoxOffice' => '$292,576,195',
            'Poster' => 'https://example.com/poster.jpg',
            'Type' => 'movie',
            'Rated' => 'PG-13',
            'Metascore' => '74',
            'DVD' => '07 Dec 2010',
            'Website' => 'N/A',
        ];

        $youtube_data = [
            'video_id' => 'abc123',
            'title' => 'Inception Official Trailer',
            'description' => 'Watch the trailer for Inception.',
            'thumbnail_url' => 'https://img.youtube.com/vi/abc123/hqdefault.jpg',
            'embed_url' => 'https://www.youtube.com/embed/abc123',
            'watch_url' => 'https://www.youtube.com/watch?v=abc123',
            'channel_title' => 'Warner Bros. Pictures',
            'published_at' => '2010-05-10T00:00:00Z',
        ];

        $result = $mapper->combineMovieData($omdb_data, $youtube_data);

        $this->assertEquals('tt1375666', $result['imdb_id']);
        $this->assertEquals('Inception', $result['title']);
        $this->assertEquals(2010, $result['year']);
        $this->assertEquals(['Action', 'Adventure', 'Sci-Fi'], $result['genre']);
        $this->assertEquals(['Leonardo DiCaprio', 'Joseph Gordon-Levitt'], $result['actors']);
        $this->assertArrayHasKey('trailer', $result);
        $this->assertEquals('abc123', $result['trailer']['video_id']);
        $this->assertArrayHasKey('data_sources', $result);
        $this->assertTrue($result['data_sources']['omdb']['used']);
        $this->assertTrue($result['data_sources']['youtube']['used']);
        $this->assertTrue($result['data_sources']['youtube']['trailer_found']);
        $this->assertEquals('high', $result['data_sources']['omdb']['data_quality']);
        $this->assertArrayHasKey('last_updated', $result);
    }
} 