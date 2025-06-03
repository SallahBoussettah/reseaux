#!/bin/bash
echo "Running Laravel Scheduler..."
cd "$(dirname "$0")"
while true; do
    php artisan schedule:run
    sleep 60
done 