# Changelog

All notable changes to the 30-60-90 Project Journey plugin will be documented in this file.

## [1.0.0] - 2025-01-21

### Added
- Initial release of 30-60-90 Project Journey plugin
- Interactive roadmap display with clickable checkboxes
- Real-time AJAX saving of task completion status
- Database schema with two tables:
  - `wp_project_journey_progress` - Track task completion
  - `wp_project_journey_signatures` - Store signature data
- Admin dashboard with statistics overview
- Settings page for customization:
  - Client name configuration
  - Consultant name configuration
  - Logo URL setting
  - PDF header customization
  - Email notification toggle
  - Notification email configuration
- Reports page with PDF generation
- Professional PDF reports using mPDF library
- PDF reports include:
  - Progress statistics
  - Completed tasks by phase
  - Signature section
  - Custom branding
- Progress data page with:
  - View all progress data
  - Export to CSV functionality
  - Clear all data option
- Complete roadmap content from JSON payload:
  - Phase 1: Days 1-30 (Discovery, Foundations & Communication)
  - Phase 2: Days 31-60 (Website Build, Booking, Funnel & AI Support)
  - Phase 3: Days 61-90 (Refinement, Launch & Momentum)
- Beautiful CSS styling matching brand colors:
  - Burnt orange (#c16107)
  - Success green (#95c93d)
  - Progress blue (#4a90e2)
- Responsive design for mobile, tablet, and desktop
- Google Fonts integration (Crimson Text & Nunito Sans)
- Task ownership badges (Client, Consultant, Both)
- Completion tracking with user name and timestamp
- Email notifications when tasks are completed
- Shortcode support with options:
  - `[project_journey_roadmap]` - Basic usage
  - `[project_journey_roadmap project_id="1"]` - Specific project
  - `[project_journey_roadmap editable="false"]` - Read-only mode
- JavaScript enhancements:
  - Smooth checkbox animations
  - Progress statistics updates
  - Phase completion indicators
  - Keyboard accessibility (Space/Enter to toggle)
  - ARIA attributes for screen readers
- Admin interface with:
  - Statistics dashboard
  - Settings management
  - Report generation
  - Data export/import
- Helper files:
  - `install-mpdf.php` - Installation checker
  - `test-mpdf.php` - PDF generation tester
  - Comprehensive README.md
  - Detailed INSTALLATION.md
- Composer support for easy mPDF installation
- Security features:
  - WordPress nonces for AJAX
  - Data sanitization
  - SQL injection prevention
  - XSS protection
  - Capability checks

### Technical Details
- Plugin version: 1.0.0
- WordPress compatibility: 5.0+
- PHP requirement: 7.4+
- mPDF version: 8.1+
- Database tables use proper indexing
- AJAX-powered checkbox updates
- Proper WordPress coding standards
- Sanitization and validation throughout
- Responsive CSS with mobile-first approach

### Files Included
- `30-60-90-project-journey.php` - Main plugin file
- `includes/form-handler.php` - AJAX and form processing
- `includes/pdf-generator.php` - PDF report generation
- `includes/admin-page.php` - Admin interface pages
- `includes/roadmap-display.php` - Front-end roadmap display
- `assets/style.css` - Front-end styles
- `assets/script.js` - Front-end JavaScript
- `assets/admin-style.css` - Admin styles
- `assets/admin-script.js` - Admin JavaScript
- `composer.json` - Composer configuration
- `README.md` - Plugin documentation
- `INSTALLATION.md` - Installation guide
- `CHANGELOG.md` - This file
- `install-mpdf.php` - Installation helper
- `test-mpdf.php` - PDF test utility
- `.gitignore` - Git ignore rules

### Credits
- Developed by: PrometheanLink
- For: Kim Benedict - Sojourn Coaching
- Design: Based on Sojourn Coaching brand
- PDF Library: mPDF (https://mpdf.github.io/)
- Fonts: Google Fonts (Crimson Text, Nunito Sans)

## Roadmap for Future Versions

### [1.1.0] - Planned
- User role management for different access levels
- Custom task creation via admin
- Bulk task operations
- Progress reports via email
- Dashboard widgets for WordPress admin
- Integration with project management tools
- Additional export formats (Excel, JSON)

### [1.2.0] - Planned
- Multi-language support
- Custom phases beyond 30/60/90
- Task dependencies and prerequisites
- Automated reminders for pending tasks
- Team collaboration features
- Comments on tasks
- File attachments for tasks

### [1.3.0] - Planned
- Calendar view of tasks
- Gantt chart visualization
- Time tracking per task
- Custom fields for tasks
- API for external integrations
- Mobile app companion

## Support

For support, please contact:
- Email: support@prometheanlink.com
- Documentation: See README.md and INSTALLATION.md

## License

Copyright (c) 2025 PrometheanLink
All rights reserved.

---

**Note:** This plugin is specifically designed for Kim Benedict's Sojourn Coaching project.
It can be customized and adapted for other projects with similar requirements.
