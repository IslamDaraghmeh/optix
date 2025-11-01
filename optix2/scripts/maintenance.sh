#!/bin/bash
###############################################################################
# Optix Clinic Management System - Maintenance Script
#
# This script performs routine maintenance tasks:
# - Clear old logs
# - Clear cache
# - Optimize database tables
# - Check disk space
# - Generate health report
#
# Usage: ./maintenance.sh
# Cron: 0 4 * * 1 /var/www/optix/scripts/maintenance.sh (Weekly on Monday)
###############################################################################

# Configuration
APP_DIR="${APP_DIR:-/var/www/optix}"
LOG_RETENTION_DAYS="${LOG_RETENTION_DAYS:-30}"
CACHE_DIR="$APP_DIR/storage/cache"
LOG_DIR="$APP_DIR/storage/logs"
DB_NAME="${DB_NAME:-optix_clinic_prod}"
DB_USER="${DB_USER:-optix_user}"
DB_PASS="${DB_PASS:-}"
DISK_THRESHOLD=90  # Alert if disk usage exceeds this percentage

# Create timestamp
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DATE_READABLE=$(date '+%Y-%m-%d %H:%M:%S')
REPORT_FILE="/tmp/optix_maintenance_report_$TIMESTAMP.txt"

# Initialize report
echo "================================================" > "$REPORT_FILE"
echo "Optix Maintenance Report" >> "$REPORT_FILE"
echo "Generated: $DATE_READABLE" >> "$REPORT_FILE"
echo "================================================" >> "$REPORT_FILE"
echo "" >> "$REPORT_FILE"

# Function to add to report
add_to_report() {
    echo "$1" >> "$REPORT_FILE"
    echo "$1"
}

add_to_report "1. CLEANING OLD LOGS"
add_to_report "-------------------"
if [ -d "$LOG_DIR" ]; then
    BEFORE_SIZE=$(du -sh "$LOG_DIR" 2>/dev/null | cut -f1)
    DELETED=$(find "$LOG_DIR" -name "*.log" -mtime +$LOG_RETENTION_DAYS -delete -print | wc -l)
    AFTER_SIZE=$(du -sh "$LOG_DIR" 2>/dev/null | cut -f1)
    add_to_report "Deleted $DELETED log file(s) older than $LOG_RETENTION_DAYS days"
    add_to_report "Log directory size: $BEFORE_SIZE -> $AFTER_SIZE"
else
    add_to_report "Log directory not found: $LOG_DIR"
fi
add_to_report ""

add_to_report "2. CLEARING CACHE"
add_to_report "-----------------"
if [ -d "$CACHE_DIR" ]; then
    BEFORE_SIZE=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    find "$CACHE_DIR" -type f -delete 2>/dev/null
    AFTER_SIZE=$(du -sh "$CACHE_DIR" 2>/dev/null | cut -f1)
    add_to_report "Cache cleared"
    add_to_report "Cache directory size: $BEFORE_SIZE -> $AFTER_SIZE"
else
    add_to_report "Cache directory not found: $CACHE_DIR"
fi
add_to_report ""

add_to_report "3. DATABASE OPTIMIZATION"
add_to_report "------------------------"
if command -v mysql &> /dev/null; then
    # Get all tables
    TABLES=$(mysql -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "SHOW TABLES;" 2>/dev/null | tail -n +2)

    if [ -n "$TABLES" ]; then
        TABLE_COUNT=0
        while IFS= read -r table; do
            mysql -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "OPTIMIZE TABLE \`$table\`;" &>/dev/null
            ((TABLE_COUNT++))
        done <<< "$TABLES"
        add_to_report "Optimized $TABLE_COUNT database table(s)"

        # Get database size
        DB_SIZE=$(mysql -u "$DB_USER" -p"$DB_PASS" -e "SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.TABLES WHERE table_schema='$DB_NAME';" 2>/dev/null | tail -n 1)
        add_to_report "Database size: ${DB_SIZE} MB"
    else
        add_to_report "No tables found or unable to connect to database"
    fi
else
    add_to_report "MySQL client not available"
fi
add_to_report ""

add_to_report "4. DISK SPACE CHECK"
add_to_report "-------------------"
DISK_USAGE=$(df -h "$APP_DIR" | tail -n 1)
DISK_PERCENT=$(df "$APP_DIR" | tail -n 1 | awk '{print $5}' | sed 's/%//')
add_to_report "$DISK_USAGE"

if [ "$DISK_PERCENT" -gt "$DISK_THRESHOLD" ]; then
    add_to_report "WARNING: Disk usage is at ${DISK_PERCENT}% (threshold: ${DISK_THRESHOLD}%)"
    add_to_report "Consider cleaning up old files or expanding storage"
else
    add_to_report "Disk usage is healthy (${DISK_PERCENT}%)"
fi
add_to_report ""

add_to_report "5. FILE PERMISSIONS CHECK"
add_to_report "-------------------------"
# Check critical directory permissions
STORAGE_PERMS=$(stat -c "%a" "$APP_DIR/storage" 2>/dev/null || stat -f "%A" "$APP_DIR/storage" 2>/dev/null)
LOGS_PERMS=$(stat -c "%a" "$LOG_DIR" 2>/dev/null || stat -f "%A" "$LOG_DIR" 2>/dev/null)

add_to_report "storage/: $STORAGE_PERMS"
add_to_report "storage/logs/: $LOGS_PERMS"

if [ "$STORAGE_PERMS" != "775" ] || [ "$LOGS_PERMS" != "775" ]; then
    add_to_report "WARNING: Some permissions may need adjustment"
fi
add_to_report ""

add_to_report "6. SYSTEM HEALTH"
add_to_report "----------------"
# PHP version
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    add_to_report "PHP: $PHP_VERSION"
fi

# MySQL version
if command -v mysql &> /dev/null; then
    MYSQL_VERSION=$(mysql -V 2>/dev/null)
    add_to_report "MySQL: $MYSQL_VERSION"
fi

# Check web server
if systemctl is-active --quiet apache2; then
    add_to_report "Apache: Running"
elif systemctl is-active --quiet nginx; then
    add_to_report "Nginx: Running"
else
    add_to_report "Web server: Status unknown"
fi

# Memory usage
if command -v free &> /dev/null; then
    MEMORY=$(free -h | grep Mem: | awk '{print "Total: " $2 ", Used: " $3 ", Free: " $4}')
    add_to_report "Memory: $MEMORY"
fi
add_to_report ""

add_to_report "================================================"
add_to_report "Maintenance completed at: $(date '+%Y-%m-%d %H:%M:%S')"
add_to_report "================================================"

# Display report
cat "$REPORT_FILE"

# Optional: Email the report
# mail -s "Optix Maintenance Report - $DATE_READABLE" admin@your-domain.com < "$REPORT_FILE"

# Optional: Save to logs
cp "$REPORT_FILE" "$LOG_DIR/maintenance_$TIMESTAMP.log"

exit 0
