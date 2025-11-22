# Quick Start Guide
## 30-60-90 Project Journey WordPress Plugin

Get up and running in 5 minutes!

## Step 1: Install mPDF (2 minutes)

Open your terminal and run:

```bash
cd "C:\Users\whieb\OneDrive\Documents\Level 5 - tribe\PrometheanLink LLC\Client Projects\Kim Benedict\created-plugins\30-60-90-project-journey"
composer install
```

**Don't have Composer?** Visit `install-mpdf.php` in your browser for alternative methods.

## Step 2: Upload to WordPress (1 minute)

1. Zip the entire `30-60-90-project-journey` folder
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file
4. Click "Activate"

## Step 3: Configure Settings (1 minute)

1. Go to WordPress Admin → Project Journey → Settings
2. Enter:
   - **Client Name:** Kim Benedict - Sojourn Coaching
   - **Consultant Name:** PrometheanLink (Walter)
   - **Logo URL:** (Upload logo and paste URL)
3. Click "Save Settings"

## Step 4: Create Roadmap Page (1 minute)

1. Go to Pages → Add New
2. Title: "Project Roadmap"
3. Add shortcode: `[project_journey_roadmap]`
4. Publish

## Step 5: Test It! (30 seconds)

1. Visit your new roadmap page
2. Click a checkbox
3. Refresh the page
4. Checkbox should stay checked ✓

## You're Done!

Now you can:
- ✓ Track progress with interactive checkboxes
- ✓ Generate PDF reports (Project Journey → Reports)
- ✓ View all data (Project Journey → Progress Data)
- ✓ Export to CSV

## Need Help?

- **Installation Issues:** See INSTALLATION.md
- **Full Documentation:** See README.md
- **Support:** support@prometheanlink.com

## Plugin Files Overview

```
30-60-90-project-journey/
├── 30-60-90-project-journey.php    # Main plugin file
├── includes/
│   ├── form-handler.php            # Checkbox save logic
│   ├── pdf-generator.php           # PDF creation
│   ├── admin-page.php              # Admin interface
│   └── roadmap-display.php         # Front-end display
├── assets/
│   ├── style.css                   # Roadmap styles
│   ├── script.js                   # Checkbox JavaScript
│   ├── admin-style.css             # Admin styles
│   └── admin-script.js             # Admin JavaScript
├── vendor/                         # mPDF library (after install)
├── composer.json                   # Dependency management
├── README.md                       # Full documentation
├── INSTALLATION.md                 # Detailed install guide
├── CHANGELOG.md                    # Version history
└── QUICKSTART.md                   # This file!
```

## Shortcode Options

**Basic:**
```
[project_journey_roadmap]
```

**With Options:**
```
[project_journey_roadmap project_id="1" editable="true"]
```

**Read-Only View:**
```
[project_journey_roadmap editable="false"]
```

## Common Tasks

### Generate a PDF Report
1. Go to Project Journey → Reports
2. Click "Generate PDF Report"
3. PDF downloads automatically

### Export Data to CSV
1. Go to Project Journey → Progress Data
2. Click "Export to CSV"
3. CSV file downloads

### Check Installation Status
Visit: `yoursite.com/wp-content/plugins/30-60-90-project-journey/install-mpdf.php`

### Test PDF Generation
Visit: `yoursite.com/wp-content/plugins/30-60-90-project-journey/test-mpdf.php`

## Tips

- Use Chrome or Firefox for best experience
- Enable email notifications in settings
- Export data regularly as backup
- Test on staging site first
- Clear cache after updates

---

**Version:** 1.0.0
**Developer:** PrometheanLink
**Client:** Kim Benedict - Sojourn Coaching
