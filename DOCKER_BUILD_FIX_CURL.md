# Docker Build Fix - Curl Extension Issue

## Problem

Build failing with error:

```
Package 'libcurl' not found
configure: error: Package requirements (libcurl >= 7.29.0) were not met
```

## Root Cause

The `curl` PHP extension requires `curl-dev` (libcurl development libraries) to compile, but it wasn't installed.

## Solution

Added `curl-dev` package to both Dockerfiles:

- `optix2/Dockerfile` - Added `curl-dev`
- `CRM/optical-crm/Dockerfile` - Added `curl-dev`

## Fix Applied

Updated system dependencies to include:

```dockerfile
curl-dev
```

This provides the necessary headers and libraries for compiling the PHP curl extension.

## Rebuild Command

```bash
# Clean rebuild
sudo docker compose build --no-cache

# Or rebuild specific service
sudo docker compose build --no-cache optix2_php
```

## Note

- `curl` package provides the runtime library
- `curl-dev` package provides development headers needed for compilation
- Both are needed: `curl` for runtime, `curl-dev` for building the extension

Fixed! ✅
