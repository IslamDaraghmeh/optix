# Performance Optimization Guide

This document outlines all performance optimizations implemented in the Optical CRM system.

## Overview

The application has been optimized for high performance through database indexing, query optimization, caching strategies, and asset optimization.

## 1. Database Optimizations

### Indexes Added
The following indexes have been added to improve query performance:

#### Patients Table
- `phone` - For phone number searches
- `email` - For email searches

#### Exams Table
- `exam_date` - For date-based filtering
- `patient_id, exam_date` (composite) - For patient exam history queries

#### Glasses Table
- `status` - For filtering by status (pending/ready/delivered)
- `status, created_at` (composite) - For status-based sorting

#### Sales Table
- `sale_date` - For date-based reports
- `remaining_amount` - For outstanding payments queries
- `patient_id, sale_date` (composite) - For patient sales history
- `remaining_amount, sale_date` (composite) - For outstanding payments dashboard

#### Expenses Table
- `expense_date` - For date-based filtering
- `category` - For category-based reports
- `expense_date, category` (composite) - For combined filtering

#### Glasses Stock Table
- `item_type` - For filtering by item type
- `quantity` - For low stock queries
- `item_type, quantity` (composite) - For stock status checks

#### Stock Movements Table
- `movement_type` - For filtering movements
- `stock_id, created_at` (composite) - For stock history

## 2. Query Optimizations

### Dashboard Controller
- **Caching**: Dashboard statistics cached for 5 minutes
- **Selective Columns**: Only necessary columns are loaded
- **Eager Loading**: Related models loaded with `with()` to prevent N+1 queries
- **Optimized Date Queries**: Using `whereBetween()` instead of `whereDate()` for better index utilization

### Report Controllers
- **Pagination**: All reports paginated (50 items per page)
- **Selective Columns**: Only required columns selected
- **Eager Loading**: Patient relationships loaded efficiently
- **Aggregation**: Statistics calculated using database aggregation instead of collection methods
- **Query Cloning**: Separate queries for statistics and paginated results

### Patient Controller
- **Pagination**: 15 items per page
- **withCount**: Using `withCount()` for relationship counts instead of loading all related records
- **Selective Loading**: Only necessary columns loaded in list views

## 3. Caching Strategies

### Application Cache
- **Dashboard Stats**: Cached for 5 minutes with time-based cache keys
- **Patient List**: Cached for 1 hour in report dropdowns
- **Configuration**: Config cached with `php artisan config:cache`
- **Routes**: Routes cached with `php artisan route:cache`
- **Views**: Blade templates compiled and cached

### Cache Configuration
- **Driver**: File-based caching (can be upgraded to Redis for better performance)
- **Lifetime**: Variable based on data volatility

## 4. Frontend Optimizations

### Vite Configuration
- **Minification**: Using Terser for JavaScript minification
- **Console Removal**: All console.log statements removed in production
- **Code Splitting**: Vendor code split into separate chunks
- **CSS Splitting**: CSS code split for optimal loading
- **No Sourcemaps**: Sourcemaps disabled in production for smaller file sizes

### Asset Loading
- **Lazy Loading**: Images and heavy components loaded on demand
- **CDN Ready**: Static assets can be served from CDN

## 5. Server Configuration Recommendations

### PHP Configuration (php.ini)
```ini
memory_limit = 256M
max_execution_time = 60
upload_max_filesize = 20M
post_max_size = 25M
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 60
```

### MySQL Configuration
```ini
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 64M
query_cache_type = 1
```

## 6. Deployment Scripts

### Production Optimization
Run `optimize.bat` to:
1. Clear all caches
2. Optimize Composer autoloader
3. Cache configuration
4. Cache routes
5. Cache views
6. Build production assets

### Development Cache Clearing
Run `dev-clear.bat` to clear all caches during development

## 7. Performance Monitoring

### Laravel Telescope (Optional)
Install Laravel Telescope for monitoring:
```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

### Query Monitoring
Enable query logging in development to identify slow queries:
```php
DB::enableQueryLog();
// Your code
dd(DB::getQueryLog());
```

## 8. Best Practices

### Database Queries
- Always use `select()` to specify needed columns
- Use `with()` for eager loading relationships
- Use pagination for large datasets
- Prefer `whereBetween()` over multiple `whereDate()` calls
- Use database aggregation functions instead of collection methods

### Caching
- Cache expensive queries
- Use appropriate cache lifetimes
- Clear cache when data changes
- Use cache tags for grouped cache management

### Frontend
- Minimize JavaScript bundle size
- Use image optimization tools
- Implement lazy loading for images
- Minimize HTTP requests

## 9. Performance Metrics

### Expected Performance
- **Dashboard Load**: < 200ms (with cache)
- **Patient List**: < 300ms (paginated)
- **Reports**: < 500ms (paginated)
- **Database Queries**: < 50ms average

### Monitoring
- Monitor slow query log
- Track page load times
- Monitor memory usage
- Track cache hit rates

## 10. Future Optimizations

### Recommended Upgrades
1. **Redis Cache**: Upgrade from file cache to Redis for better performance
2. **Database Replication**: Read replicas for heavy read operations
3. **Queue System**: Move heavy operations to background jobs
4. **CDN**: Serve static assets from CDN
5. **Full-Text Search**: Implement Elasticsearch for complex searches
6. **Image Optimization**: Automatic image compression and WebP conversion

### Scaling Strategies
- Horizontal scaling with load balancers
- Database sharding for very large datasets
- Microservices architecture for complex operations
- API caching with Varnish or similar

## Maintenance

### Regular Tasks
1. Run `php artisan optimize` regularly in production
2. Monitor database size and clean old audit logs
3. Review and update indexes based on slow query log
4. Clear old cache entries periodically
5. Update dependencies regularly

### Troubleshooting
If experiencing slow performance:
1. Check database indexes are applied
2. Verify cache is working (`php artisan cache:clear`)
3. Check server resources (CPU, memory, disk)
4. Review slow query log
5. Verify opcache is enabled
6. Check for N+1 query problems

## Conclusion

These optimizations ensure the Optical CRM system performs efficiently even with large datasets. Regular monitoring and maintenance will help maintain optimal performance as the application grows.
