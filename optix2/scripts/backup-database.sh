#!/bin/bash
###############################################################################
# Optix Clinic Management System - Database Backup Script
#
# This script creates automated backups of the Optix database
#
# Usage: ./backup-database.sh
# Cron: 0 2 * * * /var/www/optix/scripts/backup-database.sh
###############################################################################

# Configuration - Update these values for your environment
DB_NAME="${DB_NAME:-optix_clinic_prod}"
DB_USER="${DB_USER:-optix_user}"
DB_PASS="${DB_PASS:-}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/optix/database}"
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
log_message "Starting database backup for $DB_NAME"

# Create backup filename
BACKUP_FILE="$BACKUP_DIR/optix_db_$TIMESTAMP.sql.gz"

# Perform backup
if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null | gzip > "$BACKUP_FILE"; then
    # Get file size
    FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_message "Database backup completed successfully: $BACKUP_FILE (Size: $FILE_SIZE)"

    # Remove old backups
    DELETED_COUNT=$(find "$BACKUP_DIR" -name "optix_db_*.sql.gz" -mtime +$RETENTION_DAYS -delete -print | wc -l)
    if [ "$DELETED_COUNT" -gt 0 ]; then
        log_message "Removed $DELETED_COUNT old backup(s) older than $RETENTION_DAYS days"
    fi

    # Optional: Upload to remote storage (uncomment and configure)
    # rsync -az "$BACKUP_FILE" user@remote-server:/backups/optix/
    # aws s3 cp "$BACKUP_FILE" s3://your-bucket/optix/backups/

    exit 0
else
    log_message "ERROR: Database backup failed for $DB_NAME"
    exit 1
fi
