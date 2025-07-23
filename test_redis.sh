#!/bin/bash

echo "=== Testing Redis Connection ==="
echo "Step 1: Check if Redis service is running"
docker-compose exec redis redis-cli ping

echo ""
echo "Step 2: Check if PHP Redis extension is loaded"
docker-compose exec app php -m | grep -i redis

echo ""
echo "Step 3: Test Laravel cache with Redis"
docker-compose exec app php artisan tinker --execute="
Cache::put('test_key', 'test_value', 60);
echo 'Cache Driver: ' . config('cache.default');
echo PHP_EOL;
echo 'Test Value: ' . Cache::get('test_key');
echo PHP_EOL;
"

echo ""
echo "Step 4: Check Redis keys directly"
docker-compose exec redis redis-cli KEYS '*test_key*'

echo ""
echo "Step 5: Clear test cache"
docker-compose exec app php artisan cache:clear

echo ""
echo "=== Test your movie cache ==="
echo "Step 6: Make a movie request and check cache"
echo "Make a request to: http://localhost:8000/api/movies/tt0372784"
echo "Then run: docker-compose exec redis redis-cli KEYS 'movie_details_*'" 