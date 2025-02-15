#!/bin/bash

# Run database migrations
php artisan migrate:fresh --force

# Seed the database
php artisan db:seed --force

# Start the Laravel development server
php artisan serve --host=0.0.0.0 --port=8000
