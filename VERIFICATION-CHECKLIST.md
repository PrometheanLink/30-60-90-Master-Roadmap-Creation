# Verification Checklist for 30-60-90 Project Journey Plugin

Use this checklist to verify that the plugin has been installed and is working correctly.

## Pre-Installation Verification

- [ ] WordPress version is 5.0 or higher
- [ ] PHP version is 7.4 or higher
- [ ] MySQL version is 5.6 or higher
- [ ] Server has at least 256M PHP memory limit
- [ ] Have access to WordPress admin panel
- [ ] Have FTP or file manager access (if needed)

## Installation Verification

### Step 1: Plugin Files
- [ ] Plugin folder exists in `/wp-content/plugins/`
- [ ] All required files are present (see PLUGIN-SUMMARY.md)
- [ ] File permissions are correct (755 for folders, 644 for files)
- [ ] `.gitignore` file is in place (optional, for version control)

### Step 2: mPDF Library
- [ ] mPDF installed via Composer OR manually
- [ ] File exists: `vendor/mpdf/autoload.php`
- [ ] Visit `install-mpdf.php` shows success message
- [ ] Visit `test-mpdf.php` generates test PDF successfully

### Step 3: Plugin Activation
- [ ] Plugin appears in WordPress Admin → Plugins list
- [ ] Plugin name: "30-60-90 Project Journey"
- [ ] Plugin activated without errors
- [ ] No PHP errors in debug log
- [ ] Admin menu "Project Journey" appears in WordPress admin

### Step 4: Database Tables
- [ ] Table exists: `wp_project_journey_progress`
- [ ] Table exists: `wp_project_journey_signatures`
- [ ] Tables have correct structure (see PLUGIN-SUMMARY.md)
- [ ] Tables have proper indexes

**Check using phpMyAdmin or run this SQL:**
```sql
SHOW TABLES LIKE '%project_journey%';
DESCRIBE wp_project_journey_progress;
DESCRIBE wp_project_journey_signatures;
```

## Configuration Verification

### Settings Page
- [ ] Navigate to: Project Journey → Settings
- [ ] Page loads without errors
- [ ] All settings fields are present:
  - [ ] Client Name field
  - [ ] Consultant Name field
  - [ ] Logo URL field
  - [ ] PDF Header Text field
  - [ ] Email Notifications checkbox
  - [ ] Notification Email field
- [ ] Save Settings button works
- [ ] Success message appears after saving
- [ ] Settings persist after page refresh

### Settings Configuration
- [ ] Set Client Name to: "Kim Benedict - Sojourn Coaching"
- [ ] Set Consultant Name to: "PrometheanLink (Walter)"
- [ ] Upload logo and set Logo URL
- [ ] Verify logo preview appears
- [ ] Set PDF Header Text
- [ ] Enable/configure email notifications (optional)
- [ ] Save all settings

## Frontend Verification

### Shortcode Page
- [ ] Created new page or post
- [ ] Added shortcode: `[project_journey_roadmap]`
- [ ] Published page
- [ ] Page URL is accessible

### Roadmap Display
- [ ] Navigate to roadmap page
- [ ] Page loads without errors
- [ ] Logo displays at top (if configured)
- [ ] Main heading displays: "30/60/90 Project Roadmap"
- [ ] Subtitle displays correctly
- [ ] Project Purpose section displays
- [ ] Project Roles cards display with names
- [ ] Phase Timeline displays (30, 60, 90 circles)
- [ ] All three phases display
- [ ] Phase 1 background color is correct (#fff9f5)
- [ ] Phase 2 background color is correct (#eeedeb)
- [ ] Phase 3 background color is correct (#eeedeb)

### Phase 1 Verification
- [ ] "Phase 1: Days 1–30" heading displays
- [ ] Section intro displays
- [ ] Goals grid displays (3 goal items)
- [ ] Objective A displays (Communication & Working Agreement)
- [ ] Objective B displays (Client Journey & Coaching Structure)
- [ ] Objective C displays (Intake PDF & Client Onboarding)
- [ ] Objective D displays (Website & Tech Foundations)
- [ ] Objective E displays (30/60/90 Master Roadmap Creation)
- [ ] All objective cards have proper styling
- [ ] Objective letters (A, B, C, D, E) display in orange circles
- [ ] All tasks display under each objective
- [ ] Checkboxes are visible and styled correctly

### Phase 2 Verification
- [ ] "Phase 2: Days 31–60" heading displays
- [ ] Section intro displays
- [ ] Goals grid displays (3 goal items)
- [ ] Objective A displays (Website Buildout)
- [ ] Objective B displays (Booking & Scheduling System)
- [ ] Objective C displays (Funnel & Email Automation)
- [ ] Objective D displays (AI Support)
- [ ] Objective E displays (Video & Content Planning)

### Phase 3 Verification
- [ ] "Phase 3: Days 61–90" heading displays
- [ ] Section intro displays
- [ ] Goals grid displays (3 goal items)
- [ ] Objective A displays (Website & Funnel Refinement)
- [ ] Objective B displays (Coaching System Finalization)
- [ ] Objective C displays (Social Presence & Launch Support)
- [ ] Objective D displays (Launch Review & Ongoing Rhythm)

### Summary Section
- [ ] "90-Day Buildout Summary" box displays
- [ ] Summary text is readable
- [ ] Box has gradient background (orange to tan)
- [ ] Text is white and centered

## Functionality Verification

### Checkbox Functionality
- [ ] Click a checkbox in Phase 1
- [ ] Checkbox shows checkmark immediately
- [ ] Task item background changes to light green
- [ ] Border color changes to green
- [ ] No JavaScript errors in console
- [ ] Refresh the page
- [ ] Checkbox remains checked
- [ ] Click checkbox again to uncheck
- [ ] Checkbox unchecks successfully
- [ ] Refresh page
- [ ] Checkbox remains unchecked

### Multiple Checkboxes
- [ ] Check 3-5 different tasks
- [ ] All save successfully
- [ ] Each shows completion styling
- [ ] Refresh page
- [ ] All remain checked
- [ ] Uncheck all tasks
- [ ] All uncheck successfully

### Completion Information
- [ ] Check a task
- [ ] Completion info appears below task
- [ ] Shows "Completed by [User Name]"
- [ ] Shows completion date
- [ ] Styling is correct (light green background)

### Owner Badges
- [ ] "Owner: Client" badges display correctly (orange background)
- [ ] "Owner: Consultant" badges display correctly (blue background)
- [ ] "Owner: Both" badges display correctly (green background)
- [ ] Custom owner text displays where applicable

## Admin Panel Verification

### Dashboard
- [ ] Navigate to: Project Journey (main menu item)
- [ ] Dashboard page loads
- [ ] Welcome panel displays
- [ ] Getting Started instructions visible
- [ ] Shortcode documentation visible
- [ ] Statistics boxes display:
  - [ ] Total Tasks Tracked
  - [ ] Tasks Completed
  - [ ] Active Users
  - [ ] Overall Progress percentage
- [ ] Statistics are accurate
- [ ] Statistics update after checking tasks

### Reports Page
- [ ] Navigate to: Project Journey → Reports
- [ ] Reports page loads
- [ ] "Generate PDF Report" section displays
- [ ] User ID field present
- [ ] Project ID field present
- [ ] "Generate PDF Report" button present
- [ ] "Preview in Browser" button present
- [ ] Recent Activity section displays
- [ ] Recent completed tasks show in table (if any)

### Progress Data Page
- [ ] Navigate to: Project Journey → Progress Data
- [ ] Progress Data page loads
- [ ] "Export to CSV" button present
- [ ] "Clear All Data" button present
- [ ] Data table displays
- [ ] Table shows all columns:
  - [ ] ID
  - [ ] User ID
  - [ ] Phase
  - [ ] Objective
  - [ ] Task
  - [ ] Completed
  - [ ] Completed By
  - [ ] Completed At
- [ ] Data is accurate

## PDF Generation Verification

### Preview Report
- [ ] Go to: Project Journey → Reports
- [ ] Click "Preview in Browser"
- [ ] New tab/window opens
- [ ] Report HTML displays correctly
- [ ] Logo displays (if configured)
- [ ] Client and Consultant names display
- [ ] Statistics box shows correct numbers
- [ ] Completed tasks display
- [ ] Phase sections organized correctly
- [ ] Signature section displays at bottom

### Generate PDF
- [ ] Go to: Project Journey → Reports
- [ ] Enter User ID: 0
- [ ] Enter Project ID: 1
- [ ] Click "Generate PDF Report"
- [ ] PDF downloads automatically
- [ ] Open downloaded PDF
- [ ] PDF displays correctly
- [ ] All content is readable
- [ ] Logo is visible (if configured)
- [ ] Formatting is professional
- [ ] Page numbers display in footer
- [ ] Signature section is present

## Export/Import Verification

### CSV Export
- [ ] Go to: Project Journey → Progress Data
- [ ] Click "Export to CSV"
- [ ] CSV file downloads
- [ ] Open CSV file
- [ ] All columns present
- [ ] Data is accurate
- [ ] No formatting issues
- [ ] Can open in Excel/Google Sheets

### Clear Data
- [ ] Go to: Project Journey → Progress Data
- [ ] Click "Clear All Data"
- [ ] Confirmation dialog appears
- [ ] Click "OK" to confirm
- [ ] Success message appears
- [ ] Page reloads
- [ ] Table is empty
- [ ] Go to frontend roadmap
- [ ] All checkboxes are unchecked

## Email Notification Verification (if enabled)

- [ ] Enable email notifications in settings
- [ ] Set notification email address
- [ ] Save settings
- [ ] Go to roadmap page
- [ ] Check a task
- [ ] Wait 1-2 minutes
- [ ] Check email inbox
- [ ] Notification email received
- [ ] Email contains:
  - [ ] Task description
  - [ ] Phase information
  - [ ] Objective information
  - [ ] Completed by user name
  - [ ] Completion timestamp
  - [ ] Link to view full progress

## Responsive Design Verification

### Desktop (1920x1080)
- [ ] Roadmap displays correctly
- [ ] All sections are readable
- [ ] Layout is centered
- [ ] Images scale properly

### Tablet (768x1024)
- [ ] Roadmap displays correctly
- [ ] Phase timeline adapts
- [ ] Roles cards stack or adapt
- [ ] Goals grid adapts
- [ ] Checkboxes remain clickable

### Mobile (375x667)
- [ ] Roadmap displays correctly
- [ ] Logo scales down
- [ ] Heading sizes reduce
- [ ] Phase timeline stacks vertically
- [ ] Roles cards stack vertically
- [ ] Goals grid becomes single column
- [ ] Objective cards are readable
- [ ] Checkboxes are touch-friendly
- [ ] Text is readable without zooming

## Browser Compatibility Verification

### Chrome
- [ ] Roadmap loads correctly
- [ ] Checkboxes work
- [ ] PDF generates
- [ ] No console errors

### Firefox
- [ ] Roadmap loads correctly
- [ ] Checkboxes work
- [ ] PDF generates
- [ ] No console errors

### Safari
- [ ] Roadmap loads correctly
- [ ] Checkboxes work
- [ ] PDF generates
- [ ] No console errors

### Edge
- [ ] Roadmap loads correctly
- [ ] Checkboxes work
- [ ] PDF generates
- [ ] No console errors

## Performance Verification

- [ ] Roadmap page loads in under 3 seconds
- [ ] Checkbox clicks respond immediately
- [ ] AJAX requests complete in under 1 second
- [ ] PDF generation completes in under 10 seconds
- [ ] CSV export completes in under 5 seconds
- [ ] No memory errors
- [ ] No timeout errors

## Security Verification

- [ ] Non-admin users cannot access admin pages
- [ ] AJAX requests require valid nonces
- [ ] Database queries use prepared statements
- [ ] User input is sanitized
- [ ] Output is escaped
- [ ] No SQL injection vulnerabilities
- [ ] No XSS vulnerabilities

## Accessibility Verification

- [ ] Checkboxes can be accessed via Tab key
- [ ] Space or Enter key toggles checkboxes
- [ ] ARIA attributes are present
- [ ] Screen reader compatible
- [ ] High contrast mode works
- [ ] Focus indicators are visible
- [ ] Semantic HTML structure

## Final Verification

- [ ] All features work as expected
- [ ] No errors in WordPress debug log
- [ ] No JavaScript errors in console
- [ ] No PHP warnings or notices
- [ ] Plugin doesn't conflict with other plugins
- [ ] Theme compatibility verified
- [ ] Documentation is accurate
- [ ] Ready for production use

## Post-Deployment Checks

After deploying to production:

- [ ] Take database backup
- [ ] Verify plugin is activated
- [ ] Test roadmap page publicly
- [ ] Verify admin access
- [ ] Monitor for errors in first 24 hours
- [ ] Check email notifications (if enabled)
- [ ] Verify PDF generation
- [ ] Test on actual client devices
- [ ] Get client approval

## Troubleshooting Reference

If any check fails, refer to:
- **Installation Issues:** INSTALLATION.md
- **General Help:** README.md
- **Quick Fixes:** QUICKSTART.md
- **File Structure:** PLUGIN-SUMMARY.md

## Support

If you encounter issues that can't be resolved:
- Email: support@prometheanlink.com
- Include: WordPress version, PHP version, specific error messages
- Attach: Screenshots of issues, debug log excerpts

---

**Checklist Version:** 1.0.0
**Plugin Version:** 1.0.0
**Last Updated:** January 2025
