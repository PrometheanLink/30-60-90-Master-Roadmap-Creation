# Installation Guide for 30-60-90 Project Journey Plugin

## Quick Start

1. Upload plugin folder to WordPress
2. Install mPDF library
3. Activate plugin
4. Configure settings
5. Add shortcode to page

## Detailed Installation Steps

### Step 1: Upload Plugin to WordPress

**Option A: Via WordPress Admin (Recommended for beginners)**

1. Zip the entire `30-60-90-project-journey` folder
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin"
4. Choose the ZIP file and click "Install Now"
5. Wait for upload to complete

**Option B: Via FTP/File Manager**

1. Upload the `30-60-90-project-journey` folder to:
   ```
   /wp-content/plugins/
   ```
2. Ensure proper permissions (755 for folders, 644 for files)

**Option C: Via WP-CLI**

```bash
wp plugin install /path/to/30-60-90-project-journey --activate
```

### Step 2: Install mPDF Library

The plugin requires mPDF to generate PDF reports. Choose ONE of these methods:

#### Method 1: Using Composer (Recommended)

If you have Composer installed on your server:

```bash
cd wp-content/plugins/30-60-90-project-journey
composer install
```

This will automatically download and install mPDF and all dependencies.

#### Method 2: Manual Installation

1. Download mPDF from: https://github.com/mpdf/mpdf/releases/latest
2. Extract the downloaded ZIP file
3. Look for the `mpdf` folder inside the extracted files
4. Upload the entire `mpdf` folder to:
   ```
   wp-content/plugins/30-60-90-project-journey/vendor/mpdf/
   ```
5. Verify this file exists:
   ```
   wp-content/plugins/30-60-90-project-journey/vendor/mpdf/autoload.php
   ```

#### Method 3: Using Installation Helper

1. Navigate to: `http://yoursite.com/wp-content/plugins/30-60-90-project-journey/install-mpdf.php`
2. Follow the on-screen instructions
3. The helper will check if mPDF is installed and guide you through the process

#### Verify mPDF Installation

Visit: `http://yoursite.com/wp-content/plugins/30-60-90-project-journey/test-mpdf.php`

If you see a PDF generated successfully, mPDF is working!

### Step 3: Activate the Plugin

1. Go to WordPress Admin → Plugins
2. Find "30-60-90 Project Journey"
3. Click "Activate"

Upon activation, the plugin will automatically:
- Create database tables (`wp_project_journey_progress` and `wp_project_journey_signatures`)
- Set default options
- Create admin menu items

### Step 4: Configure Settings

1. Go to WordPress Admin → Project Journey → Settings
2. Configure the following:

   **Client Name**
   - Enter: `Kim Benedict - Sojourn Coaching`
   - This appears on the roadmap and reports

   **Consultant Name**
   - Enter: `PrometheanLink (Walter)`
   - This appears on the roadmap and reports

   **Logo URL**
   - Upload your logo to Media Library
   - Copy the image URL
   - Paste into Logo URL field
   - Example: `https://yoursite.com/wp-content/uploads/2025/11/logo.png`

   **PDF Header Text**
   - Enter: `30/60/90 Project Journey Report`
   - This appears at the top of PDF reports

   **Email Notifications**
   - Check to enable email notifications when tasks are completed
   - Enter notification email address

3. Click "Save Settings"

### Step 5: Create Roadmap Page

1. Go to WordPress Admin → Pages → Add New
2. Enter page title: `Project Roadmap` (or your preferred title)
3. In the content editor, add the shortcode:
   ```
   [project_journey_roadmap]
   ```
4. Publish the page

**Advanced Shortcode Options:**

```
[project_journey_roadmap project_id="1" editable="true"]
```

- `project_id` - Specify project ID (default: 1)
- `editable` - Set to "false" for read-only view (default: true)

### Step 6: Test the Roadmap

1. Visit the page you just created
2. You should see the beautiful 30/60/90 roadmap
3. Try clicking a checkbox to mark a task complete
4. Refresh the page - the checkbox should remain checked

## Post-Installation Configuration

### Customize Logo

1. Upload your logo to WordPress Media Library
2. Copy the image URL
3. Go to Project Journey → Settings
4. Paste URL in "Logo URL" field
5. Save settings

### Set Up Email Notifications

1. Go to Project Journey → Settings
2. Check "Send email notification when a task is completed"
3. Enter your email address
4. Save settings
5. Complete a task to test

### Generate Your First PDF Report

1. Go to Project Journey → Reports
2. Enter User ID (0 for all users)
3. Enter Project ID (1 is default)
4. Click "Generate PDF Report"
5. PDF will download automatically

## Troubleshooting

### mPDF Not Found Error

**Problem:** "mPDF is not installed" error when generating PDFs

**Solution:**
1. Verify mPDF is in correct location: `vendor/mpdf/autoload.php`
2. Run the installation helper: `install-mpdf.php`
3. Try manual installation steps again
4. Check file permissions

### Checkboxes Not Saving

**Problem:** Checkboxes uncheck when page is refreshed

**Solutions:**
1. Check browser console for JavaScript errors
2. Verify AJAX is working (check Network tab in browser dev tools)
3. Clear browser cache
4. Ensure user is logged in (for logged-in users) or guest tracking is enabled
5. Check database table exists: `wp_project_journey_progress`

### Database Tables Not Created

**Problem:** Plugin activated but tables don't exist

**Solutions:**
1. Deactivate and reactivate the plugin
2. Check database user has CREATE TABLE permissions
3. Manually run the SQL from the plugin file
4. Check for database errors in WordPress debug log

### PDF Generation Fails

**Problem:** Error when generating PDF

**Solutions:**
1. Verify mPDF is installed correctly
2. Test mPDF with: `test-mpdf.php`
3. Check PHP memory limit (increase to 256M if needed)
4. Check file write permissions in `vendor` folder
5. Enable WordPress debug mode to see detailed errors

### Styling Issues

**Problem:** Roadmap doesn't look right

**Solutions:**
1. Clear browser cache
2. Clear WordPress cache (if using caching plugin)
3. Check for theme conflicts (try default WordPress theme)
4. Verify CSS file is loading (check browser Network tab)
5. Check for JavaScript errors in console

## Server Requirements

- **PHP:** 7.4 or higher (8.0+ recommended)
- **WordPress:** 5.0 or higher (6.0+ recommended)
- **MySQL:** 5.6 or higher
- **Memory:** 128M minimum (256M recommended for PDF generation)
- **Disk Space:** 50MB for plugin and mPDF library

## File Permissions

Recommended permissions:
- Folders: 755
- Files: 644
- vendor/mpdf folder: 755 (must be readable)

## Security Notes

1. The plugin uses WordPress nonces for AJAX security
2. All data is sanitized before database insertion
3. User capabilities are checked for admin functions
4. SQL queries use prepared statements
5. Output is escaped to prevent XSS

## Getting Help

If you encounter issues:

1. **Check the documentation:**
   - README.md
   - This INSTALLATION.md file

2. **Run diagnostic tools:**
   - `install-mpdf.php` - Check mPDF installation
   - `test-mpdf.php` - Test PDF generation

3. **Enable WordPress Debug:**
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   ```

4. **Contact Support:**
   - Email: support@prometheanlink.com
   - Include: WordPress version, PHP version, error messages

## Next Steps

After installation:

1. ✓ Configure all settings
2. ✓ Test checkbox functionality
3. ✓ Generate a test PDF report
4. ✓ Customize logo and branding
5. ✓ Set up email notifications
6. ✓ Share roadmap page with team

## Updating the Plugin

When updating:

1. Backup your database first
2. Upload new plugin files
3. Database tables will be updated automatically if needed
4. Clear cache after updating

## Uninstallation

To completely remove the plugin:

1. Deactivate the plugin
2. Go to Project Journey → Progress Data
3. Export data if you want to keep it
4. Delete the plugin
5. Manually delete database tables if desired:
   - `wp_project_journey_progress`
   - `wp_project_journey_signatures`

---

**Plugin Version:** 1.0.0
**Last Updated:** 2025
**Developer:** PrometheanLink
**For:** Kim Benedict - Sojourn Coaching
