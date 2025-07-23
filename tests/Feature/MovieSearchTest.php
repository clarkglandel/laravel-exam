<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MovieSearchTest extends TestCase
{
    public function test_search_requires_valid_title()
    {
        $response = $this->getJson('/api/movies/search?title=');
        $response->assertStatus(422)
                 ->assertJsonFragment([
                     'error' => 'Invalid input.'
                 ])
                 ->assertJsonStructure([
                     'messages' => ['title']
                 ]);
    }

    public function test_search_returns_paginated_results()
    {
        // This test assumes OMDb API key is set and OMDb is reachable.
        $response = $this->getJson('/api/movies/search?title=Matrix&page=1');
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'pagination' => [
                         'current_page',
                         'per_page',
                         'total',
                         'last_page',
                         'from',
                         'to',
                     ],
                 ]);
        $this->assertCount(5, $response->json('data'));
    }
} 