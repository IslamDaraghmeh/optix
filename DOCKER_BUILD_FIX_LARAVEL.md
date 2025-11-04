# Docker Build Fix - Laravel Artisan Issue

## Problem

Build was failing with error:

```
Could not open input file: artisan
Script @php artisan package:discover --ansi handling the post-autoload-dump event returned with error code 1
```

## Root Cause

Laravel's `composer.json` has a post-autoload-dump script that runs `php artisan package:discover`, but the `artisan` file wasn't copied yet because:

- Composer files were copied first
- `composer install` was run
- Application files were copied after

## Solution

Changed Dockerfile order to copy application files **before** running `composer install`, so the `artisan` file exists when post-install scripts run.

## Fix Applied

Updated `CRM/optical-crm/Dockerfile`:

- Copy composer files first (for layer caching)
- Copy all application files (including artisan)
- Run composer install (now artisan exists)
- Copy entrypoint script

## Rebuild Command

```bash
# Clean rebuild
sudo docker compose build --no-cache crm_php

# Or rebuild all
sudo docker compose build --no-cache
```

## Note

This is a common issue with Laravel Docker builds. The artisan file must exist before composer post-install scripts run.

Fixed! ✅
