#!/bin/bash
cd /home/stellahomes/repositories/stellahomes || exit
git pull origin main
php artisan migrate --force
php artisan optimize:clear
