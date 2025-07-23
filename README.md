## 📝 Setup & Environment Variables (Quick Guide)

**Note:** This app is fully set up for Docker, Redis, and MySQL out of the box. You don’t need to install PHP or MySQL on your computer... just use Docker and you’re good to go!

### 1. Environment Variables

Before running the app, you need API keys for OMDb and YouTube.

1. Copy the example env file:
    ```bash
    cp .env.example .env
    ```
2. Open `.env` and fill in your API keys:
    ```env
    OMDB_API_KEY=your_omdb_api_key_here
    YOUTUBE_API_KEY=your_youtube_api_key_here
    ```
    - Get OMDb key: [omdbapi.com/apikey.aspx](http://www.omdbapi.com/apikey.aspx)
    - Get YouTube key: [Google Cloud Console](https://console.cloud.google.com/) (enable YouTube Data API v3)

### 2. Running the App (Local Dev)

1. Install dependencies:
    ```bash
    composer install
    npm install
    ```
2. Start Docker (for PHP, MySQL, Redis):
    ```bash
    docker-compose up -d
    ```
3. Set up Laravel:
    ```bash
    php artisan key:generate
    php artisan migrate
    ```
4. Build frontend:
    ```bash
    npm run dev
    ```
5. Open your browser: [http://localhost:8000](http://localhost:8000)

---

## How Movie Recommendations Work

-   When you view a movie, the backend checks its main genre (like "Action").
-   It then searches for other movies with the same genre using the OMDb API.
-   From the results, it picks 3 random movies (not including the one you’re viewing).
-   These are sent back as recommendations.
-   If there aren’t enough results, it just shows what it can find.

---
