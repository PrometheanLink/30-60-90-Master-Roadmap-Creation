# Docker Development Environment Setup
## 30/60/90 Project Journey - Kim Benedict / Sojourn Coaching

Complete guide to setting up a local WordPress development environment with Docker.

---

## Prerequisites

- Docker Desktop installed and running
- At least 4GB RAM available for Docker
- Ports 8080 and 8081 available on your system

---

## Quick Start (Windows)

### Option 1: Using the Setup Script (Easiest)

1. Open Command Prompt in this directory
2. Run:
   ```cmd
   docker-setup.bat
   ```
3. Wait for containers to start (about 30 seconds)
4. Go to http://localhost:8080

### Option 2: Manual Setup

```cmd
# Build the containers
docker-compose build

# Start the containers
docker-compose up -d

# Check status
docker-compose ps
```

---

## What Gets Created

**3 Docker Containers:**

1. **kb-wordpress** (WordPress + PHP + Composer)
   - Accessible at: http://localhost:8080
   - Has Composer pre-installed
   - PHP extensions: gd, mbstring, zip, intl, dom, xml
   - Upload limits: 512MB (for .wpress imports)

2. **kb-mysql** (MySQL 8.0 Database)
   - Database: wordpress_kb
   - Username: wordpress
   - Password: wordpress123

3. **kb-phpmyadmin** (Database Management)
   - Accessible at: http://localhost:8081
   - Login with database credentials above

**2 Docker Volumes:**
- `wordpress_data` - WordPress files
- `db_data` - MySQL database (persists between restarts)

---

## Step-by-Step Setup

### Step 1: Start the Environment

```cmd
docker-compose up -d
```

**Expected output:**
```
Creating network "30-60-90-project-journey_wordpress-network" with driver "bridge"
Creating volume "30-60-90-project-journey_wordpress_data" with local driver
Creating volume "30-60-90-project-journey_db_data" with local driver
Creating kb-mysql ... done
Creating kb-wordpress ... done
Creating kb-phpmyadmin ... done
```

### Step 2: Initial WordPress Setup

1. Open browser to http://localhost:8080
2. Select language
3. Complete the WordPress installation:
   - Site Title: **Sojourn Coaching - Dev**
   - Username: **admin**
   - Password: (choose a strong password)
   - Email: your email
4. Click "Install WordPress"
5. Log in to WordPress admin

### Step 3: Install All-in-One WP Migration

**Via WordPress Admin:**
1. Go to Plugins → Add New
2. Search for "All-in-One WP Migration"
3. Install and Activate
4. Also install "All-in-One WP Migration Unlimited Extension" (if you have it)

**OR via Command Line:**
```cmd
docker-compose exec wordpress wp plugin install all-in-one-wp-migration --activate
```

### Step 4: Import Your Site Backup

1. Go to **All-in-One WP Migration → Import**
2. Click **Import From → File**
3. Select your `.wpress` file from walterh50.sg-host.com
4. Wait for upload and import (may take 5-10 minutes)
5. Click "Permalink Settings" when prompted
6. **Important:** You'll be logged out - log back in with the production credentials

### Step 5: Install the 30/60/90 Plugin

**Option A: Via WordPress Admin (Recommended)**
1. Zip the `30-60-90-project-journey` folder
2. Go to Plugins → Add New → Upload Plugin
3. Upload the zip file
4. Activate the plugin

**Option B: Plugin is Already Mounted**
The plugin is already mounted as a volume, so it should appear in your plugins list automatically!

### Step 6: Install mPDF Dependencies

**Enter the WordPress container:**
```cmd
docker-compose exec wordpress bash
```

**Navigate to plugin directory:**
```bash
cd wp-content/plugins/30-60-90-project-journey
```

**Install Composer dependencies:**
```bash
composer install
```

**Expected output:**
```
Installing dependencies from lock file
...
Package operations: 3 installs, 0 updates, 0 removals
  - Installing mpdf/mpdf
  - Installing ...
Writing lock file
Generating autoload files
```

**Exit the container:**
```bash
exit
```

### Step 7: Configure Plugin Settings

1. Go to **Project Journey → Settings**
2. Set:
   - Client Name: **Kim Benedict - Sojourn Coaching**
   - Consultant Name: **PrometheanLink (Walter)**
   - Logo URL: `https://walterh50.sg-host.com/wp-content/uploads/2025/11/sojourn-logo.webp`
3. Save Settings

### Step 8: Create Roadmap Page

1. Go to Pages → Add New
2. Title: **Project Roadmap**
3. Add the shortcode:
   ```
   [project_journey_roadmap]
   ```
4. Publish the page
5. View the page to see your beautiful roadmap!

---

## Common Commands

### Container Management

```cmd
# Start containers
docker-compose up -d

# Stop containers (keeps data)
docker-compose down

# Stop and remove all data (CAREFUL!)
docker-compose down -v

# View logs
docker-compose logs -f

# View logs for specific container
docker-compose logs -f wordpress

# Restart containers
docker-compose restart

# Check status
docker-compose ps
```

### WordPress Container Access

```cmd
# Enter WordPress container bash
docker-compose exec wordpress bash

# Run WP-CLI commands
docker-compose exec wordpress wp plugin list
docker-compose exec wordpress wp user list
docker-compose exec wordpress wp db export

# Install Composer dependencies (from host)
docker-compose exec wordpress composer install --working-dir=/var/www/html/wp-content/plugins/30-60-90-project-journey
```

### Database Management

```cmd
# Enter MySQL container
docker-compose exec db mysql -u wordpress -p

# Export database
docker-compose exec db mysqldump -u wordpress -p wordpress_kb > backup.sql

# Import database
docker-compose exec -T db mysql -u wordpress -p wordpress_kb < backup.sql
```

---

## Troubleshooting

### Port Already in Use

**Error:** `Bind for 0.0.0.0:8080 failed: port is already allocated`

**Solution:** Edit `.env` file and change ports:
```env
WORDPRESS_PORT=8082
PHPMYADMIN_PORT=8083
```

### WordPress Shows Database Connection Error

**Solution:** Wait 30 seconds for MySQL to fully start, then refresh.

### Can't Upload Large .wpress File

**Solution:** The upload limit is already set to 512MB in `docker/uploads.ini`. If you still have issues:

1. Check the file size
2. Try the "Import from URL" method in All-in-One WP Migration
3. Or split the import using the paid extension

### Plugin Not Showing Up

**Solution:**
```cmd
docker-compose exec wordpress wp plugin list
```
If not listed, the volume mount may not be working. Try:
```cmd
docker-compose down
docker-compose up -d
```

### Composer Install Fails

**Error:** `composer: command not found`

**Solution:** Rebuild the container:
```cmd
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Permission Errors

**Solution:** Fix permissions inside container:
```cmd
docker-compose exec wordpress chown -R www-data:www-data /var/www/html
```

---

## File Locations

### On Your Computer (Windows)

```
C:\Users\whieb\OneDrive\Documents\Level 5 - tribe\PrometheanLink LLC\Client Projects\Kim Benedict\created-plugins\30-60-90-project-journey\
```

### Inside the Container

```
/var/www/html/wp-content/plugins/30-60-90-project-journey/
```

### Volume Mounts

The plugin directory is **mounted as a volume**, so:
- Edit files on Windows → Changes appear instantly in container
- No need to rebuild or restart containers
- Perfect for development!

---

## URLs

| Service | URL | Credentials |
|---------|-----|-------------|
| WordPress | http://localhost:8080 | admin / (your password) |
| WordPress Admin | http://localhost:8080/wp-admin | admin / (your password) |
| phpMyAdmin | http://localhost:8081 | wordpress / wordpress123 |

---

## Backup & Export

### Export Everything

```cmd
# Export database
docker-compose exec db mysqldump -u wordpress -p wordpress_kb > kb-backup.sql

# Export WordPress files (All-in-One WP Migration recommended)
# Go to All-in-One WP Migration → Export → Export To → File
```

### Copy Files from Container

```cmd
# Copy a file from container to your computer
docker cp kb-wordpress:/var/www/html/wp-content/uploads/backup.wpress ./backup.wpress
```

---

## Cleanup

### Remove Everything (Start Fresh)

```cmd
# Stop and remove containers, volumes, and networks
docker-compose down -v

# Remove all unused Docker resources
docker system prune -a
```

**Warning:** This will delete all data! Export database first if needed.

---

## Next Steps After Setup

1. ✅ Verify plugin is activated
2. ✅ Test the roadmap page
3. ✅ Click some checkboxes to test saving
4. ✅ Go to Project Journey → Reports
5. ✅ Generate a test PDF report
6. ✅ Verify signatures work
7. ✅ Export progress data as CSV

---

## Production Deployment

Once tested locally, deploy to production:

1. Export the plugin folder
2. Zip it up
3. Upload to walterh50.sg-host.com via WordPress Admin
4. Run `composer install` on the production server
5. Activate plugin
6. Configure settings

---

## Support

**Plugin Issues:**
- Check `VERIFICATION-CHECKLIST.md`
- Review `README.md`
- Check WordPress error logs

**Docker Issues:**
- View logs: `docker-compose logs -f`
- Restart containers: `docker-compose restart`
- Rebuild: `docker-compose build --no-cache`

---

## Summary

You now have a **complete local WordPress development environment** with:

✅ WordPress latest version
✅ MySQL 8.0 database
✅ phpMyAdmin for DB management
✅ Composer pre-installed
✅ All PHP extensions for mPDF
✅ Large upload limits for .wpress imports
✅ Volume mounts for easy development
✅ Persistent data between restarts

**Happy developing! 🚀**
