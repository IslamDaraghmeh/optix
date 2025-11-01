#!/bin/bash
###############################################################################
# Optix Clinic Management System - Database Restore Script
#
# This script restores the database from a backup file
#
# Usage: ./restore-database.sh <backup_file>
# Example: ./restore-database.sh /var/backups/optix/database/optix_db_20251021_020000.sql.gz
###############################################################################

# Configuration - Update these values for your environment
DB_NAME="${DB_NAME:-optix_clinic_prod}"
DB_USER="${DB_USER:-optix_user}"
DB_PASS="${DB_PASS:-}"
LOG_FILE="${LOG_FILE:-/var/log/optix_restore.log}"

# Check if backup file was provided
if [ -z "$1" ]; then
    echo "Error: No backup file specified"
    echo "Usage: $0 <backup_file>"
    echo "Example: $0 /var/backups/optix/database/optix_db_20251021_020000.sql.gz"
    exit 1
fi

BACKUP_FILE="$1"

# Check if backup file exists
if [ ! -f "$BACKUP_FILE" ]; then
    echo "Error: Backup file not found: $BACKUP_FILE"
    exit 1
fi

# Create timestamp
DATE_READABLE=$(date '+%Y-%m-%d %H:%M:%S')

# Log function
log_message() {
    echo "[$DATE_READABLE] $1" | tee -a "$LOG_FILE"
}

# Confirmation prompt
echo "WARNING: This will restore the database '$DB_NAME' from the backup file:"
echo "$BACKUP_FILE"
echo ""
echo "This will OVERWRITE all current data in the database!"
echo ""
read -p "Are you sure you want to continue? (yes/no): " CONFIRM

if [ "$CONFIRM" != "yes" ]; then
    echo "Restore cancelled."
    exit 0
fi

# Create a backup of current database before restore
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
SAFETY_BACKUP="/tmp/optix_db_pre_restore_$TIMESTAMP.sql.gz"

log_message "Creating safety backup of current database to $SAFETY_BACKUP"
if mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null | gzip > "$SAFETY_BACKUP"; then
    log_message "Safety backup created successfully"
else
    log_message "WARNING: Could not create safety backup"
fi

# Perform restore
log_message "Starting database restore from $BACKUP_FILE"

if gunzip < "$BACKUP_FILE" | mysql -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" 2>/dev/null; then
    log_message "Database restore completed successfully"
    log_message "Safety backup retained at: $SAFETY_BACKUP"
    echo ""
    echo "Restore completed successfully!"
    echo "Safety backup of previous database: $SAFETY_BACKUP"
    exit 0
else
    log_message "ERROR: Database restore failed"
    echo ""
    echo "ERROR: Restore failed!"
    echo "Your database may be in an inconsistent state."
    echo "Safety backup available at: $SAFETY_BACKUP"
    exit 1
fi
