<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;
use Illuminate\Database\QueryException;
use Exception;

class FavoriteController extends Controller
{
    /**
     * Get all favorite movies, returning only the movie details.
     */
    public function index()
    {
        try {
            $favorites = Favorite::all();

            $movie_list = $favorites->map(function ($favorite) {
                return json_decode($favorite->movie_details, true);
            });

            return response()->json([
                'data' => $movie_list,
                'total' => $movie_list->count(),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch favorites.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a movie is favorited by IMDb ID.
     */
    public function check($imdbId)
    {
        try {
            $favorite = Favorite::whereJsonContains('movie_details->imdb_id', $imdbId)
                ->orWhereJsonContains('movie_details->imdbID', $imdbId)
                ->first();

            return response()->json([
                'is_favorited' => $favorite !== null,
                'favorite_id' => $favorite ? $favorite->id : null,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to check favorite.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new favorite movie with user IP address.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'movie_details' => 'required|array',
            ]);

            // Check if already favorited to prevent duplicates
            $imdbId = $validated['movie_details']['imdb_id'] ?? $validated['movie_details']['imdbID'] ?? null;

            if ($imdbId) {
                $existing = Favorite::whereJsonContains('movie_details->imdb_id', $imdbId)
                    ->orWhereJsonContains('movie_details->imdbID', $imdbId)
                    ->first();

                if ($existing) {
                    return response()->json([
                        'message' => 'Movie is already in favorites',
                        'favorite' => $existing,
                    ], 200);
                }
            }

            $favorite = Favorite::create([
                'movie_details' => json_encode($validated['movie_details']),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'Favorite added successfully',
                'favorite' => $favorite,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'messages' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error.',
                'message' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to add favorite.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove a favorite movie by IMDb ID.
     */
    public function destroy($imdbId)
    {
        try {
            $favorite = Favorite::whereJsonContains('movie_details->imdb_id', $imdbId)
                ->orWhereJsonContains('movie_details->imdbID', $imdbId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'error' => 'Favorite not found',
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'message' => 'Favorite removed successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to remove favorite.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle favorite status for a movie.
     */
    public function toggle(Request $request)
    {
        try {
            $validated = $request->validate([
                'movie_details' => 'required|array',
            ]);

            $imdbId = $validated['movie_details']['imdb_id'] ?? $validated['movie_details']['imdbID'] ?? null;

            if (!$imdbId) {
                return response()->json([
                    'error' => 'Movie IMDb ID is required',
                ], 400);
            }

            // Check if already favorited
            $existing = Favorite::whereJsonContains('movie_details->imdb_id', $imdbId)
                ->orWhereJsonContains('movie_details->imdbID', $imdbId)
                ->first();

            if ($existing) {
                // Remove from favorites
                $existing->delete();
                return response()->json([
                    'message' => 'Favorite removed successfully',
                    'is_favorited' => false,
                ], 200);
            } else {
                // Add to favorites
                $favorite = Favorite::create([
                    'movie_details' => json_encode($validated['movie_details']),
                    'ip_address' => $request->ip(),
                ]);

                return response()->json([
                    'message' => 'Favorite added successfully',
                    'favorite' => $favorite,
                    'is_favorited' => true,
                ], 201);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed.',
                'messages' => $e->errors(),
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'error' => 'Database error.',
                'message' => $e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to toggle favorite.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
