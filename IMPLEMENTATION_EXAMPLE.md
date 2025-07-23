# Custom Mapping Logic Implementation Example

This document demonstrates how the custom mapping logic combines data from OMDb and YouTube APIs into a single, comprehensive movie details response.

## Overview

The implementation consists of three main components:

1. **YouTubeService**: Handles YouTube API calls
2. **MovieDataMapper**: Combines and normalizes data from both APIs
3. **MovieApiController**: Orchestrates the API calls and returns unified responses

## Example Implementation Flow

### 1. Request Flow

```
GET /api/movies/tt0111161
```

### 2. Backend Processing

#### Step 1: OMDb API Call

```php
// In MovieApiController::movie()
$omdb_response = Http::timeout(10)->get($this->omdb_url, [
    'apikey' => $this->omdb_key,
    'i' => 'tt0111161', // The Shawshank Redemption
    'plot' => 'full',
]);
```

**OMDb Response:**

```json
{
    "Title": "The Shawshank Redemption",
    "Year": "1994",
    "Rated": "R",
    "Released": "14 Oct 1994",
    "Runtime": "142 min",
    "Genre": "Drama",
    "Director": "Frank Darabont",
    "Writer": "Stephen King, Frank Darabont",
    "Actors": "Tim Robbins, Morgan Freeman, Bob Gunton",
    "Plot": "Two imprisoned men bond over a number of years...",
    "Language": "English",
    "Country": "United States",
    "Awards": "Nominated for 7 Oscars. 21 wins & 42 nominations total",
    "Poster": "https://m.media-amazon.com/images/M/MV5BNDE3ODcxYzMtY2YzZC00NmNlLWJiNDMtZDViZWM2MzIxZDYwXkEyXkFqcGdeQXVyNjAwNDUxODI@._V1_SX300.jpg",
    "Ratings": [
        {
            "Source": "Internet Movie Database",
            "Value": "9.3/10"
        },
        {
            "Source": "Rotten Tomatoes",
            "Value": "91%"
        },
        {
            "Source": "Metacritic",
            "Value": "82/100"
        }
    ],
    "Metascore": "82",
    "imdbRating": "9.3",
    "imdbVotes": "2,763,891",
    "imdbID": "tt0111161",
    "Type": "movie",
    "DVD": "N/A",
    "BoxOffice": "$16,015,408",
    "Production": "N/A",
    "Website": "N/A",
    "Response": "True"
}
```

#### Step 2: YouTube API Call

```php
// In YouTubeService::searchMovieTrailer()
$youtube_data = $this->youtube_service->searchMovieTrailer('The Shawshank Redemption');
```

**YouTube API Response (processed):**

```json
{
    "video_id": "PLl99DlL6b4",
    "title": "The Shawshank Redemption (1994) - Official Trailer",
    "description": "Academy Award® winner Tim Robbins and Morgan Freeman star in The Shawshank Redemption...",
    "thumbnail_url": "https://i.ytimg.com/vi/PLl99DlL6b4/maxresdefault.jpg",
    "embed_url": "https://www.youtube.com/embed/PLl99DlL6b4",
    "watch_url": "https://www.youtube.com/watch?v=PLl99DlL6b4",
    "channel_title": "Warner Bros. Pictures",
    "published_at": "2014-02-26T16:30:01Z"
}
```

#### Step 3: Data Mapping and Combination

```php
// In MovieDataMapper::combineMovieData()
$combined_data = $this->movie_mapper->combineMovieData($omdb_data, $youtube_data);
```

### 3. Final Combined Response

The custom mapping logic produces this unified response:

```json
{
    "imdb_id": "tt0111161",
    "title": "The Shawshank Redemption",
    "year": 1994,
    "release_date": "1994-10-14",
    "runtime": {
        "minutes": 142,
        "formatted": "142 min",
        "hours_minutes": "2h 22m"
    },
    "genre": ["Drama"],
    "plot": "Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.",
    "ratings": {
        "internet_movie_database": {
            "source": "Internet Movie Database",
            "value": "9.3/10",
            "normalized": 9.3
        },
        "rotten_tomatoes": {
            "source": "Rotten Tomatoes",
            "value": "91%",
            "normalized": 9.1
        },
        "metacritic": {
            "source": "Metacritic",
            "value": "82/100",
            "normalized": 8.2
        }
    },
    "imdb_rating": 9.3,
    "imdb_votes": 2763891,
    "director": ["Frank Darabont"],
    "writer": ["Stephen King", "Frank Darabont"],
    "actors": ["Tim Robbins", "Morgan Freeman", "Bob Gunton"],
    "country": ["United States"],
    "language": ["English"],
    "awards": "Nominated for 7 Oscars. 21 wins & 42 nominations total",
    "production": null,
    "box_office": "$16,015,408",
    "poster": "https://m.media-amazon.com/images/M/MV5BNDE3ODcxYzMtY2YzZC00NmNlLWJiNDMtZDViZWM2MzIxZDYwXkEyXkFqcGdeQXVyNjAwNDUxODI@._V1_SX600.jpg",
    "type": "movie",
    "rated": "R",
    "metascore": 82,
    "dvd_release": null,
    "website": null,
    "trailer": {
        "available": true,
        "video_id": "PLl99DlL6b4",
        "title": "The Shawshank Redemption (1994) - Official Trailer",
        "description": "Academy Award® winner Tim Robbins and Morgan Freeman star in The Shawshank Redemption...",
        "thumbnail_url": "https://i.ytimg.com/vi/PLl99DlL6b4/maxresdefault.jpg",
        "embed_url": "https://www.youtube.com/embed/PLl99DlL6b4",
        "watch_url": "https://www.youtube.com/watch?v=PLl99DlL6b4",
        "channel_title": "Warner Bros. Pictures",
        "published_at": "2014-02-26T16:30:01Z"
    },
    "data_sources": {
        "omdb": {
            "used": true,
            "data_quality": "high"
        },
        "youtube": {
            "used": true,
            "trailer_found": true
        }
    },
    "last_updated": "2023-12-07T15:30:45.123Z",
    "omdb_data": {
        // Original OMDb response preserved for backward compatibility
        "Title": "The Shawshank Redemption",
        "Year": "1994"
        // ... full original response
    }
}
```

## Key Mapping Features

### 1. Data Normalization

The mapper normalizes various data formats:

```php
// Runtime parsing
"Runtime": "142 min" → {
  "minutes": 142,
  "formatted": "142 min",
  "hours_minutes": "2h 22m"
}

// Date parsing
"Released": "14 Oct 1994" → "release_date": "1994-10-14"

// Genre parsing
"Genre": "Drama, Crime" → "genre": ["Drama", "Crime"]

// People parsing
"Director": "Frank Darabont" → "director": ["Frank Darabont"]
```

### 2. Rating Normalization

All ratings are normalized to a 0-10 scale:

```php
"9.3/10" → 9.3      // IMDb (already 0-10)
"91%" → 9.1         // Rotten Tomatoes (percentage to 0-10)
"82/100" → 8.2      // Metacritic (0-100 to 0-10)
```

### 3. Poster Quality Enhancement

```php
// Automatic poster quality upgrade
"Poster": "...SX300.jpg" → "...SX600.jpg"
```

### 4. Error Handling and Fallbacks

The system gracefully handles API failures:

```php
// If YouTube API fails
"trailer": {
  "available": false,
  "video_id": null,
  // ... other fields null
}

// Data source tracking
"data_sources": {
  "omdb": {"used": true, "data_quality": "high"},
  "youtube": {"used": false, "trailer_found": false}
}
```

## Frontend Integration

The frontend automatically adapts to the new structure while maintaining backward compatibility:

```javascript
// In use-movie-details.js
const movieData = await getMovieDetails(imdbId);

// Use new normalized structure with fallback to original
movie.value = movieData.omdb_data || movieData;

// Extract trailer from combined response
if (movieData.trailer?.available && movieData.trailer?.embed_url) {
    trailerUrl.value = movieData.trailer.embed_url;
}
```

## Benefits of Custom Mapping

1. **Single API Call**: Frontend makes one request instead of multiple
2. **Data Consistency**: All data is normalized and validated
3. **Better Performance**: Reduced client-side processing
4. **Error Resilience**: Graceful handling when APIs are unavailable
5. **Enhanced Data**: Higher quality posters, normalized ratings
6. **Backward Compatibility**: Original OMDb data preserved
7. **Comprehensive Logging**: Full audit trail of API calls
8. **Data Quality Assessment**: Automatic evaluation of response completeness

This implementation provides a robust, scalable foundation for combining multiple external APIs into a single, coherent movie data service.
