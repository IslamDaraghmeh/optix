# Docker Build Fix - ICU Library Issue

## Problem

Build was failing with error:

```
Package 'icu-i18n' not found
```

## Solution

Added ICU development libraries to Dockerfiles:

- `icu-dev` - ICU development headers
- `icu-libs` - ICU runtime libraries

## Fix Applied

Updated both Dockerfiles:

- `optix2/Dockerfile`
- `CRM/optical-crm/Dockerfile`

Added to apk install command:

```dockerfile
icu-dev \
icu-libs
```

## Rebuild Commands

```bash
# Clean rebuild (recommended)
docker-compose build --no-cache

# Or rebuild specific service
docker-compose build --no-cache crm_php
docker-compose build --no-cache optix2_php

# Then start services
docker-compose up -d
```

## What Changed

The `intl` PHP extension requires ICU (International Components for Unicode) libraries to compile. These packages were missing from the Alpine base image, causing the build to fail.

Now fixed! ✅
