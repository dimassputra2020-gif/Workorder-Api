#!/bin/bash
set -e

echo "Copying .env.publik to .env..."
cp .env.publik .env

echo "Copying docker-compose.windows.yml to docker-compose.yml..."
cp docker-compose.windows.yml docker-compose.yml

echo "Building docker compose..."
docker compose up -d --build --remove-orphans --wait --timestamps
echo "Docker compose built..."

echo "Docker compose running..."
echo "Waiting for docker compose to finish..."
wait

echo "Installing composer dependencies..."
docker compose exec app composer install
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app bash -c "chmod -R 777 storage/"
docker compose exec app bash -c "chmod -R 777 vendor/"
docker compose exec app php artisan key:generate --force
# docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache

echo "Docker compose finished..."
echo "Docker compose logs:"
docker compose logs -f --tail=100