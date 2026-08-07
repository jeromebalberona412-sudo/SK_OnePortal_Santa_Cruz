# Render Deployment Guide for SK OnePortal

## Step 1: Prepare GitHub Repository

Ensure all Docker files are committed:

```bash
git add Dockerfile nginx.conf supervisord.conf entrypoint.sh .dockerignore
git commit -m "Add production-ready Docker deployment setup"
git push
```

## Step 2: Create Render Web Service

1. Go to [Render Dashboard](https://dashboard.render.com/)
2. Click **New** → **Web Service**
3. Connect your GitHub repository

## Step 3: Configure Service Settings

### Basic Settings
- **Name**: `sk-oneportal` (or your preferred name)
- **Region**: Select nearest region
- **Branch**: `main`

### Build & Deploy Settings
- **Environment**: Docker
- **Docker Context**: `/` (root of SK_Officials)
- **Dockerfile Path**: `Dockerfile` (leave as default)
- **Build Command**: (leave blank - Dockerfile handles this)
- **Start Command**: (leave blank - Dockerfile handles this)

### Runtime Settings
- **Instance Type**: Paid (Free doesn't support background processes)
- **RAM**: 1GB minimum (2GB recommended for Laravel)
- **CPU**: 1 minimum (2 recommended)

## Step 4: Environment Variables

Add these environment variables in Render:

### Application Settings
```
APP_NAME=SK OnePortal
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.onrender.com
APP_KEY=your_generated_app_key
```

### Database (PostgreSQL on Render)
```
DB_CONNECTION=pgsql
DB_HOST=your-render-postgres-host
DB_PORT=5432
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

### Session & Cache
```
SESSION_DRIVER=redis
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
```

### Cloudflare Turnstile
```
TURNSTILE_ENABLED=true
TURNSTILE_SITE_KEY=your_turnstile_site_key
TURNSTILE_SECRET_KEY=your_turnstile_secret_key
TURNSTILE_VERIFY_URL=https://challenges.cloudflare.com/turnstile/v0/siteverify
```

### Other Services
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

### Deployment Settings
```
RUN_MIGRATIONS=true
```

## Step 5: Add Required Services

### PostgreSQL Database
1. Create a new PostgreSQL database on Render
2. Copy the connection details
3. Add to environment variables above

### Redis (for sessions/cache/queues)
1. Create a new Redis instance on Render
2. Copy the connection details
3. Add to environment variables above

## Step 6: Health Check

In the Render service settings, add a health check:

- **Path**: `/health` (or `/api/health` if you have a health endpoint)
- **Check interval**: 30 seconds
- **Timeout**: 10 seconds
- **Grace period**: 30 seconds

## Step 7: Deploy

Click **Create Web Service** and Render will:
1. Build the Docker image
2. Run migrations (if `RUN_MIGRATIONS=true`)
3. Start the application
4. Make it available at `https://your-app-name.onrender.com`

## Post-Deployment Steps

### 1. Generate APP_KEY
If you don't have an APP_KEY, generate one locally:
```bash
php artisan key:generate
```
Then add it to Render environment variables.

### 2. Configure Mail
Set up SMTP service (Mailtrap, SendGrid, etc.) for production emails.

### 3. Configure Cloudinary
Ensure your Cloudinary account is properly configured for file uploads.

### 4. Test Application
- Visit your Render URL
- Test login with Turnstile
- Test file uploads
- Test all major features

## Troubleshooting

### Build Fails
- Check Render build logs
- Ensure Dockerfile syntax is correct
- Verify all environment variables are set

### Database Connection Issues
- Verify PostgreSQL service is running
- Check DB_HOST, DB_PORT, DB_DATABASE credentials
- Ensure Render network allows connections

### Storage Permissions
- The entrypoint script should handle this automatically
- Check logs for permission errors

### Turnstile Issues
- Verify TURNSTILE_SITE_KEY and TURNSTILE_SECRET_KEY are correct
- Ensure widget domains include your Render URL

## Performance Optimization

For better performance on Render:
- Use 2GB RAM instance
- Enable Redis for sessions and cache
- Use Render's PostgreSQL for database
- Monitor metrics in Render dashboard
- Consider CDN for static assets

## Monitoring

Monitor your deployment using:
- Render's built-in metrics
- Laravel Telescope (if enabled in dev)
- External monitoring services

## Scaling

To scale your application:
- Increase instance size in Render
- Add load balancer if needed
- Consider Redis for better session management
