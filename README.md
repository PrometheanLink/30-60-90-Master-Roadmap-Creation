# 30-60-90 Project Journey WordPress Plugin

Track 30/60/90 day roadmap progress with interactive checkboxes that save to database and generate professional PDF reports.

## Features

- **Interactive Roadmap**: Beautiful, responsive roadmap display with clickable checkboxes
- **Database Storage**: All progress is saved to WordPress database with user tracking
- **PDF Reports**: Generate professional PDF reports with completion data and signatures
- **Admin Dashboard**: Complete admin interface to view progress, manage settings, and download reports
- **Email Notifications**: Optional email notifications when tasks are completed
- **Progress Tracking**: Track who completed each task and when
- **Responsive Design**: Works perfectly on desktop, tablet, and mobile devices

## Installation

1. **Upload Plugin**
   - Upload the `30-60-90-project-journey` folder to `/wp-content/plugins/`
   - Or zip the folder and upload via WordPress admin

2. **Install mPDF Library**
   - Download mPDF from: https://github.com/mpdf/mpdf/releases
   - Extract the mPDF files to the `vendor/mpdf/` directory inside the plugin folder
   - Or install via Composer (see below)

3. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Find "30-60-90 Project Journey" and click Activate
   - Database tables will be created automatically

## Installing mPDF via Composer (Recommended)

If you have Composer installed:

```bash
cd wp-content/plugins/30-60-90-project-journey
composer require mpdf/mpdf
```

## Manual mPDF Installation

1. Download mPDF from: https://github.com/mpdf/mpdf/releases/latest
2. Extract the downloaded file
3. Copy the `mpdf` folder to: `wp-content/plugins/30-60-90-project-journey/vendor/mpdf/`
4. Ensure the path exists: `vendor/mpdf/autoload.php`

## Usage

### Display Roadmap

Add this shortcode to any page or post:

```
[project_journey_roadmap]
```

### Shortcode Options

```
[project_journey_roadmap project_id="1" editable="true"]
```

- `project_id` - Specify project ID (default: 1)
- `editable` - Set to "false" for read-only display (default: true)

### Admin Configuration

1. **Settings** (Project Journey → Settings)
   - Configure client name
   - Configure consultant name
   - Set logo URL
   - Customize PDF header
   - Enable/disable email notifications
   - Set notification email address

2. **Reports** (Project Journey → Reports)
   - Generate PDF reports
   - Preview reports in browser
   - View recent activity

3. **Progress Data** (Project Journey → Progress Data)
   - View all tracked progress
   - Export data to CSV
   - Clear all progress data

## Database Schema

### wp_project_journey_progress

Stores task completion data:
- `id` - Auto-increment primary key
- `user_id` - WordPress user ID
- `project_id` - Project identifier
- `phase` - Phase (phase1, phase2, phase3)
- `objective` - Objective letter (A, B, C, etc.)
- `task_id` - Unique task identifier
- `task_text` - Full task description
- `completed` - Boolean completion status
- `completed_by` - Name of person who completed
- `completed_at` - Timestamp of completion
- `created_at` - Record creation timestamp

### wp_project_journey_signatures

Stores signature data for reports:
- `id` - Auto-increment primary key
- `project_id` - Project identifier
- `user_id` - WordPress user ID
- `client_name` - Client name for signature
- `client_signature_data` - Client signature data
- `consultant_name` - Consultant name
- `consultant_signature_data` - Consultant signature data
- `signed_at` - Signature timestamp

## Customization

### Custom CSS

The plugin uses CSS variables for easy customization. Add custom styles to your theme:

```css
.wormhole-roadmap {
    --color-burnt-orange: #your-color;
    --color-success: #your-color;
    --color-progress: #your-color;
}
```

### Custom Logo

1. Upload your logo to WordPress Media Library
2. Copy the image URL
3. Go to Project Journey → Settings
4. Paste URL in "Logo URL" field
5. Save settings

## Support

For issues or questions:
- Email: support@prometheanlink.com
- Documentation: https://prometheanlink.com/docs

## Changelog

### Version 1.0.0
- Initial release
- Interactive roadmap with checkboxes
- Database storage for progress tracking
- PDF report generation
- Admin dashboard
- Email notifications
- CSV export functionality

## Credits

- Developed by: PrometheanLink
- For: Kim Benedict - Sojourn Coaching
- mPDF Library: https://mpdf.github.io/

## License

Copyright (c) 2025 PrometheanLink
All rights reserved.
