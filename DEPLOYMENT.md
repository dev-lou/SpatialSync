# Production Deployment Guide - Render.com

## Overview
This guide covers everything needed to deploy BlueprintFlow to Render.com with a Docker container running Laravel, Nginx, and PHP-FPM.

## Prerequisites
- GitHub repository set up
- Supabase project created (PostgreSQL database)
- Render.com account

## Step 1: Generate Laravel App Key

**Locally, before pushing to GitHub:**

```bash
php artisan key:generate
```

Copy the `APP_KEY` value from your `.env` file. You'll need this for Render.

## Step 2: Prepare Your .env Variables

These must be set as **environment variables** in Render Dashboard:

### Required Variables:
```
APP_ENV=production
APP_DEBUG=false
APP_NAME=BlueprintFlow
APP_KEY=base64:YOUR_KEY_HERE
APP_URL=https://your-render-domain.onrender.com

# Supabase
SUPABASE_URL=https://YOUR_PROJECT.supabase.co
SUPABASE_SERVICE_KEY=your-service-role-key-here
SUPABASE_ANON_KEY=your-anon-key-here

# Database (Supabase PostgreSQL)
DB_CONNECTION=pgsql
DB_HOST=db.YOUR_PROJECT.supabase.co
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-database-password

# Session & Cache (file-based for single instance)
SESSION_DRIVER=file
CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

## Step 3: Push to GitHub

```bash
git add .
git commit -m "Production ready: Fixed Alpine.js dependency"
git push origin main
```

## Step 4: Create Render Service

1. **Connect GitHub Repository**
   - Go to [Render Dashboard](https://dashboard.render.com)
   - Click "New" → "Web Service"
   - Select "Build and deploy from a Git repository"
   - Connect your GitHub account and select the BlueprintFlow repository

2. **Service Configuration**
   - **Name**: `blueprintflow`
   - **Region**: `Oregon` (or closest to you)
   - **Branch**: `main`
   - **Runtime**: `Docker`
   - **Build Command**: Leave empty (Dockerfile handles it)
   - **Start Command**: Leave empty (Dockerfile handles it)

3. **Plan Selection**
   - Select **Standard** plan for production
   - This includes auto-scaling and better performance

4. **Environment Variables**
   - Click "Advanced" → "Environment Variables"
   - Add all variables from Step 2
   - **IMPORTANT**: Never commit `.env` with real keys to GitHub

5. **Persistent Disk** (Optional but Recommended)
   - Add a persistent disk for `/var/www/html/storage`
   - Size: 10GB (or based on usage)
   - This preserves logs and cache across deployments

## Step 5: Database Setup

After the initial deployment:

1. **Connect to Supabase Console** and verify your database is running
2. **Run migrations** (optional - if using Laravel migrations):
   ```bash
   # In Render Dashboard, click the service → Shell
   php artisan migrate --force
   ```

## Step 6: Verify Deployment

After deployment completes:

1. **Check Health**
   - Visit your service URL: `https://your-service-name.onrender.com`
   - You should see the Laravel welcome page or your app

2. **Check Logs**
   - Click "Logs" tab in Render Dashboard
   - Look for any errors

3. **Test API Endpoints**
   - Test a few endpoints to ensure the app is working
   - Check browser console for any JavaScript errors

## Step 7: Configure Custom Domain (Optional)

1. In Render Dashboard → Service Settings
2. Go to "Custom Domains"
3. Add your domain (e.g., `blueprintflow.com`)
4. Configure DNS records according to Render's instructions

## Step 8: Enable SSL/TLS

Render automatically provides free SSL certificates. No action needed!

## Performance Optimization

1. **Enable Compression**
   - Already configured in Nginx (in Dockerfile)

2. **Browser Caching**
   - Already configured in Nginx

3. **CDN** (Optional)
   - Consider adding Cloudflare for better performance

4. **Database Optimization**
   - Add indexes in Supabase as needed
   - Monitor query performance

## Monitoring & Maintenance

### Logs
- Check logs regularly in Render Dashboard
- Set up alerts for errors

### Updates
- Update dependencies: `npm audit fix`, `composer update`
- Test in staging before deploying to production
- Use GitHub Actions for automated testing (recommended)

### Backups
- Supabase handles database backups automatically
- Enable point-in-time recovery in Supabase settings

## Troubleshooting

### Build Fails
1. **Check logs** in Render Dashboard
2. **Common issues**:
   - Missing npm dependencies → Fixed by updating package.json
   - Missing PHP extensions → Check Dockerfile
   - Database connection errors → Verify DB credentials

### App Not Responding
1. Check health indicator in Render Dashboard
2. Review application logs
3. Verify all environment variables are set
4. Check database connection

### Slow Performance
1. Enable persistent disk for storage
2. Consider upgrading plan
3. Add CDN (Cloudflare)
4. Optimize database queries

## Rollback Plan

If deployment has issues:

1. Previous deployments are stored in Render
2. Click "Deployments" tab
3. Select previous working deployment
4. Click "Redeploy"

## Production Checklist

- [ ] Generated APP_KEY locally
- [ ] All environment variables set in Render Dashboard
- [ ] Database connection verified
- [ ] Application loads without errors
- [ ] API endpoints working
- [ ] No sensitive data in GitHub
- [ ] .env file is in .gitignore
- [ ] SSL/TLS is active (automatic)
- [ ] Error pages are working (404, 500, etc.)
- [ ] Logging is configured
- [ ] Backup strategy planned
- [ ] Domain configured (if using custom domain)

## Additional Resources

- [Render Documentation](https://render.com/docs)
- [Docker Documentation](https://docs.docker.com)
- [Laravel Production Deployment](https://laravel.com/docs/deployment)
- [Supabase Documentation](https://supabase.com/docs)

## Support

For deployment issues:
1. Check Render logs first
2. Review this guide
3. Contact Render support: https://support.render.com

---

**Version**: 1.0  
**Last Updated**: 2026-05-04  
**Status**: Ready for Production
