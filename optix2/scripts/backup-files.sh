#!/bin/bash
###############################################################################
# Optix Clinic Management System - File Backup Script
#
# This script creates automated backups of application files
#
# Usage: ./backup-files.sh
# Cron: 0 3 * * 0 /var/www/optix/scripts/backup-files.sh
###############################################################################

# Configuration - Update these values for your environment
SOURCE_DIR="${SOURCE_DIR:-/var/www/optix}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/optix/files}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
LOG_FILE="${LOG_FILE:-/var/log/optix_backup.log}"

# Create timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DATE_READABLE=$(date '+%Y-%m-%d %H:%M:%S')

# Create backup directory if it doesn't exist
mkdir -p "$BACKUP_DIR"

# Log function
log_message() {
    echo "[$DATE_READABLE] $1" | tee -a "$LOG_FILE"
}

# Start backup
log_message "Starting file backup from $SOURCE_DIR"

# Create backup filename
BACKUP_FILE="$BACKUP_DIR/optix_files_$TIMESTAMP.tar.gz"

# Perform backup (exclude vendor, cache, and logs)
if tar -czf "$BACKUP_FILE" \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/cache/*' \
    --exclude='storage/logs/*.log' \
    --exclude='.git' \
    -C "$(dirname "$SOURCE_DIR")" \
    "$(basename "$SOURCE_DIR")" 2>/dev/null; then

    # Get file size
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_message "File backup completed successfully: $BACKUP_FILE (Size: $FILE_SIZE)"

    # Remove old backups
    DELETED_COUNT=$(find "$BACKUP_DIR" -name "optix_files_*.tar.gz" -mtime +$RETENTION_DAYS -delete -print | wc -l)
    if [ "$DELETED_COUNT" -gt 0 ]; then
        log_message "Removed $DELETED_COUNT old backup(s) older than $RETENTION_DAYS days"
    fi

    # Optional: Upload to remote storage (uncomment and configure)
    # rsync -az "$BACKUP_FILE" user@remote-server:/backups/optix/
    # aws s3 cp "$BACKUP_FILE" s3://your-bucket/optix/backups/

    exit 0
else
    log_message "ERROR: File backup failed from $SOURCE_DIR"
    exit 1
fi
