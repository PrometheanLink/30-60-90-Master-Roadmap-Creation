# 🚀 Quick Start - Docker Setup (5 Minutes)

Get your local WordPress development environment running in 5 minutes!

---

## Step 1: Run the Setup Script (1 minute)

Open Command Prompt in the plugin directory and run:

```cmd
cd "C:\Users\whieb\OneDrive\Documents\Level 5 - tribe\PrometheanLink LLC\Client Projects\Kim Benedict\created-plugins\30-60-90-project-journey"

docker-setup.bat
```

**What this does:**
- Builds Docker containers with WordPress, MySQL, phpMyAdmin
- Installs Composer and all PHP extensions
- Starts everything and waits for initialization

---

## Step 2: Complete WordPress Install (1 minute)

1. Open browser to **http://localhost:8675**
2. Select language → **English**
3. Fill in:
   - Site Title: **Sojourn Coaching - Dev**
   - Username: **admin**
   - Password: (choose one)
   - Email: your email
4. Click **Install WordPress**
5. Log in

---

## Step 3: Install All-in-One WP Migration (30 seconds)

In WordPress Admin:
1. Go to **Plugins → Add New**
2. Search: **All-in-One WP Migration**
3. Click **Install Now**
4. Click **Activate**

---

## Step 4: Import Your Site Backup (2-5 minutes)

1. Go to **All-in-One WP Migration → Import**
2. Click **Import From → File**
3. Select your `.wpress` backup file from walterh50.sg-host.com
4. Wait for upload and import
5. When done, you'll be logged out
6. **Log back in** using your **production site credentials**

---

## Step 5: Install mPDF via Composer (1 minute)

Open Command Prompt and run:

```cmd
docker-compose exec wordpress bash
cd wp-content/plugins/30-60-90-project-journey
composer install
exit
```

**Expected output:**
```
Installing dependencies...
  - Installing mpdf/mpdf
  - Installing dependencies
Writing lock file
Done!
```

---

## Step 6: Configure Plugin (30 seconds)

In WordPress Admin:

1. Go to **Project Journey → Settings**
2. Set:
   - **Client Name:** Kim Benedict - Sojourn Coaching
   - **Consultant Name:** PrometheanLink (Walter)
   - **Logo URL:** https://walterh50.sg-host.com/wp-content/uploads/2025/11/sojourn-logo.webp
3. Click **Save Settings**

---

## Step 7: Test It! (30 seconds)

1. Go to **Pages → Add New**
2. Title: **Project Roadmap**
3. Add shortcode: `[project_journey_roadmap]`
4. Click **Publish**
5. Click **View Page**

**You should see:**
- Beautiful 30/60/90 roadmap
- All phases, objectives, and tasks
- Clickable checkboxes
- Progress tracking!

---

## ✅ You're Done!

**Your environment is ready!**

### What You Have:
- ✅ WordPress running at http://localhost:8675
- ✅ phpMyAdmin at http://localhost:8676
- ✅ Full Kim Benedict site imported
- ✅ 30/60/90 plugin installed
- ✅ mPDF dependencies ready
- ✅ Ready for development!

### Test the Plugin:
1. Click some checkboxes on the roadmap
2. Go to **Project Journey → Reports**
3. Click **Generate PDF Report**
4. Download and view the beautiful PDF!

---

## Common Commands

```cmd
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# Access WordPress container
docker-compose exec wordpress bash

# Restart everything
docker-compose restart
```

---

## Need Help?

- **Full Guide:** See `DOCKER-GUIDE.md`
- **Plugin Docs:** See `README.md`
- **Installation:** See `INSTALLATION.md`
- **Verification:** See `VERIFICATION-CHECKLIST.md`

---

## URLs

| Service | URL |
|---------|-----|
| WordPress | http://localhost:8675 |
| Admin | http://localhost:8675/wp-admin |
| phpMyAdmin | http://localhost:8676 |

---

## Next Steps

Now that you have a working environment:

1. ✅ Test all plugin features
2. ✅ Make changes to the code (they'll update automatically!)
3. ✅ Generate PDF reports
4. ✅ Verify checkbox saving
5. ✅ Export progress data
6. ✅ When ready, deploy to production!

**Happy developing! 🎉**
