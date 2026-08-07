# Render Deployment Guide - SK OnePortal (Laravel 12)

## Overview
Complete production-ready Docker deployment setup for Laravel 12 with:
- PHP 8.3-FPM
- PostgreSQL (Supabase)
- Vite frontend build
- Nginx web server
- Supervisor process manager
- Cloudinary integration
- Cloudflare Turnstile

## Prerequisites

### Required Services on Render
1. **PostgreSQL Database** (for Laravel)
2. **Redis** (for sessions, cache, queues)
3. **Web Service** (Docker-based)

### Local Requirements
- Git repository with all files committed
- Docker files properly configured
- Environment variables documented

## Step 1: Commit All Changes

```bash
cd "C:\Users\admin\Documents\GitHub\SK_OnePortal_Santa_Cruz"
git add .
git commit -m "Complete Docker deployment setup for Render"
git push
```

## Step 2: Create Render Services

### 2.1 PostgreSQL Database
1. Go to Render Dashboard → New → PostgreSQL
2. Name: `sk-oneportal-db`
3. Database: PostgreSQL
4. Version: 16 (latest)
5. Region: Same as your web service
6. **Save connection details** (will need for environment variables)

### 2.2 Redis Instance
1. Go to Render Dashboard → New → Redis
2. Name: `sk-oneportal-redis`
3. Region: Same as your web service
4. **Save connection details** (will need for environment variables)

### 2.3 Web Service
1. Go to Render Dashboard → New → Web Service
2. **Repository Settings**:
   - Connect your GitHub repository
   - Branch: `main`
   - Root Directory: `SK_Officials` (IMPORTANT!)
   - Runtime: Docker

3. **Build & Deploy Settings**:
   - Docker Context: `/`
   - Dockerfile Path: `Dockerfile`
   - Build Command: (leave blank)
   - Start Command: (leave blank)

4. **Environment Variables** (see below)

## Step 3: Configure Environment Variables

### Application Settings
```
APP_NAME=SK OnePortal
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
APP_KEY=your_generated_app_key
```

### Database (PostgreSQL)
```
DB_CONNECTION=pgsql
DB_HOST=your-render-postgres-host
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### Session & Cache (Redis)
```
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=your-render-redis-host
REDIS_PASSWORD=your_render_redis_password
REDIS_PORT=6379
```

### Cloudinary
```
CLOUDINARY_CLOUD_NAME=your_cloudinary_cloud_name
CLOUDINARY_API_KEY=your_cloudinary_api_key
CLOUDINARY_API_SECRET=your_cloudinary_api_secret
CLOUDINARY_FOLDER=sk_oneportal/posts
CLOUDINARY_PROFILE_UPLOAD_PRESET=profile_images
CLOUDINARY_PROFILE_FOLDER=profile_images
CLOUDINARY_SUPPORTING_DOCS_UPLOAD_PRESET=Supporting_Documents
CLOUDINARY_SUPPORTING_DOCS_FOLDER=Supporting_Documents
```

### Cloudflare Turnstile
```
TURNSTILE_ENABLED=true
TURNSTILE_SITE_KEY=your_turnstile_site_key
TURNSTILE_SECRET_KEY=your_turnstile_secret_key
TURNSTILE_VERIFY_URL=https://challenges.cloudflare.com/turnstile/v0/siteverify
```

### Mail Configuration
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=SK OnePortal
```

### Laravel Settings
```
LOG_CHANNEL=stack
LOG_LEVEL=warning
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_COOKIE=sk_oneportal_session
```

### Deployment Settings
```
RUN_MIGRATIONS=true
RENDER_PORT=8080
```

## Step 4: Configure Health Check

In the Web Service settings:
- **Health Check Path**: `/health`
- **Check Interval**: 30 seconds
- **Timeout**: 10 seconds
- **Grace Period**: 30 seconds

## Step 5: Deploy

1. Click **Create Web Service**
2. Render will:
   - Build Docker image
   - Run migrations (if `RUN_MIGRATIONS=true`)
   - Start services
   - Make available at `https://your-app-name.onrender.com`

## Step 6: Post-Deployment Configuration

### Generate APP_KEY
If you don't have an APP_KEY:
```bash
php artisan key:generate
```
Add it to Render environment variables.

### Configure Domain (Optional)
1. Go to Web Service → Settings → Custom Domains
2. Add your custom domain
3. Update `APP_URL` environment variable

### Test Application
- Visit your Render URL
- Test login with Turnstile
- Test file uploads (Cloudinary)
- Test all major features

## Troubleshooting

### Build Fails
- Check Render build logs
- Verify Dockerfile syntax
- Ensure all environment variables are set
- Check for missing files (composer.lock, package-lock.json)

### Database Connection Issues
- Verify PostgreSQL service is running
- Check DB_HOST, DB_PORT, DB_DATABASE credentials
- Ensure Render network allows connections
- Check if `DB_HOST` includes the port

### Redis Connection Issues
- Verify Redis service is running
- Check REDIS_HOST and REDIS_PASSWORD
- Ensure Redis port is correct (usually 6379)

### Storage Permissions
- The entrypoint script handles this automatically
- Check logs for permission errors
- Ensure www-data user has proper access

### Turnstile Issues
- Verify TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY
- Ensure widget domains include your Render URL
- Check that Turnstile is enabled

### Application Won't Start
- Check Supervisor logs in Render
- Verify PHP-FPM is running
- Check Nginx configuration
- Review entrypoint script logs

## Performance Optimization

### Instance Type
- **Minimum**: 1GB RAM, 1 CPU
- **Recommended**: 2GB RAM, 2 CPU
- **For High Traffic**: 4GB RAM, 2-4 CPU

### Caching Strategy
- Redis for sessions, cache, and queues
- Laravel's route:cache, view:cache, config:cache
- Nginx static asset caching

### Database Optimization
- Use connection pooling
- Consider read replicas for high traffic
- Optimize database queries

## Monitoring

### Render Dashboard
- Monitor CPU, memory, and response times
- Check logs for errors
- Set up alerts for failures

### Laravel Telescope (Optional)
Enable in development for debugging
```php
composer require laravel/telescope --dev
php artisan telescope:install
```

## Security Best Practices

- **Never commit** .env file with real credentials
- Use strong passwords for database and Redis
- Enable HTTPS (automatic on Render)
- Keep dependencies updated
- Monitor for security advisories
- Use Render's automatic SSL certificates

## Backup Strategy

- Render automatically backs up PostgreSQL
- Consider nightly backups for user data
- Test restore procedures regularly
- Document recovery process

## Scaling

### Horizontal Scaling
- Add more instances under load
- Use load balancer if needed
- Configure Redis for shared sessions

### Vertical Scaling
- Increase instance size for better performance
- Monitor resource usage before scaling
- Consider burst instances for traffic spikes

## Cost Optimization

- Use appropriate instance sizes
- Scale down during low-traffic periods
- Use Render's free tier for development
- Monitor and optimize resource usage

## Support

For issues specific to:
- **Render**: https://render.com/docs
- **Laravel**: https://laravel.com/docs
- **Docker**: https://docs.docker.com
- **Nginx**: https://nginx.org/en/docs/
