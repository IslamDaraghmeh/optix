# Docker Build Troubleshooting

## Issue

Build completes but fails with "failed to execute bake: exit status 1"

## Possible Causes

1. **Build timeout** - intl extension compilation takes very long
2. **Memory issues** - Building multiple services simultaneously
3. **Docker Compose bake issue** - Internal error

## Solutions

### Option 1: Build Services Separately

```bash
# Build optix2_php first (takes longest)
sudo docker compose build optix2_php

# Then build crm_php
sudo docker compose build crm_php

# Then start all services
sudo docker compose up -d
```

### Option 2: Increase Build Resources

```bash
# Check available memory
free -h

# If low on memory, build one at a time
sudo docker compose build --no-cache optix2_php
```

### Option 3: Check Full Error Output

```bash
# Get more verbose output
sudo docker compose build --progress=plain 2>&1 | tee build.log

# Check the end of the log for actual error
tail -50 build.log
```

### Option 4: Build Without Bake (if using newer Docker Compose)

```bash
# Try building without bake
sudo docker compose build --no-cache
```

### Option 5: Check if Services Can Start

```bash
# If builds completed, try starting anyway
sudo docker compose up -d

# Check status
sudo docker compose ps

# Check logs
sudo docker compose logs
```

## Quick Test

```bash
# Check if images were created
sudo docker images | grep optix

# If images exist, try starting services
sudo docker compose up -d
```

## If Build Keeps Failing

Consider optimizing the Dockerfile to reduce build time or remove intl extension if not needed for optix2.
