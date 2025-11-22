# 30/60/90 Project Journey Plugin - Complete Schema & Namespace Reference

**Version:** 1.0.0
**Purpose:** Single source of truth for all database tables, functions, options, actions, and namespaces
**DO NOT create parallel/duplicate values - reference this document first!**

---

## 1. DATABASE TABLES

### Table: `wp_project_journey_progress`
Primary table for tracking task completion progress

```sql
CREATE TABLE wp_project_journey_progress (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    project_id int(11) NOT NULL DEFAULT 1,
    phase varchar(50) NOT NULL,
    objective varchar(50) NOT NULL,
    task_id varchar(100) NOT NULL,
    task_text text NOT NULL,
    completed tinyint(1) NOT NULL DEFAULT 0,
    completed_by varchar(255) DEFAULT NULL,
    completed_at datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY task_unique (user_id, project_id, task_id),
    KEY phase_index (phase),
    KEY objective_index (objective)
)
```

**Accessed by:**
- `pj_save_progress_handler()` - Insert/Update
- `pj_get_user_progress()` - Select by user_id and project_id
- `pj_get_all_progress()` - Select all
- `pj_clear_progress_handler()` - Truncate
- `pj_export_progress_csv()` - Export all rows

### Table: `wp_project_journey_signatures`
Table for storing digital signatures (future feature)

```sql
CREATE TABLE wp_project_journey_signatures (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    project_id int(11) NOT NULL DEFAULT 1,
    user_id bigint(20) NOT NULL,
    client_name varchar(255) DEFAULT NULL,
    client_signature_data text DEFAULT NULL,
    consultant_name varchar(255) DEFAULT NULL,
    consultant_signature_data text DEFAULT NULL,
    signed_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY project_user_index (project_id, user_id)
)
```

**Accessed by:**
- `pj_save_signature_handler()` - Insert signatures

---

## 2. WORDPRESS OPTIONS (Settings)

All options use prefix `pj_`

| Option Name | Default Value | Description |
|------------|---------------|-------------|
| `pj_client_name` | `'Kim Benedict - Sojourn Coaching'` | Client name shown on roadmap and PDF |
| `pj_consultant_name` | `'PrometheanLink (Walter)'` | Consultant name shown on roadmap and PDF |
| `pj_logo_url` | `'https://walterh50.sg-host.com/wp-content/uploads/2025/11/sojourn-logo.webp'` | Logo URL for headers and PDF |
| `pj_pdf_header` | `'30/60/90 Project Journey Report'` | PDF header text |
| `pj_email_notifications` | `'0'` | Enable/disable email notifications (1/0) |
| `pj_notification_email` | `get_option('admin_email')` | Email address for notifications |

**Set in:** `pj_create_tables()` - activation hook
**Updated in:** `pj_admin_settings_page()` - settings form

---

## 3. PHP CONSTANTS

Defined in main plugin file `30-60-90-project-journey.php`

| Constant | Value | Description |
|----------|-------|-------------|
| `PJ_PLUGIN_DIR` | `plugin_dir_path(__FILE__)` | Absolute path to plugin directory |
| `PJ_PLUGIN_URL` | `plugin_dir_url(__FILE__)` | URL to plugin directory |
| `PJ_VERSION` | `'1.0.0'` | Plugin version number |

---

## 4. PHP FUNCTIONS

All functions use prefix `pj_`

### Main Plugin File Functions
- `pj_create_tables()` - Activation hook, creates database tables
- `pj_enqueue_assets()` - Enqueues frontend CSS/JS
- `pj_admin_enqueue_assets($hook)` - Enqueues admin CSS/JS
- `pj_roadmap_shortcode($atts)` - Handles `[project_journey_roadmap]` shortcode
- `pj_add_admin_menu()` - Registers admin menu pages

### Form Handler Functions (`includes/form-handler.php`)
- `pj_save_progress_handler()` - AJAX handler for checkbox saves
- `pj_send_completion_notification()` - Sends email on task completion
- `pj_get_user_progress($user_id, $project_id)` - Returns user's progress array
- `pj_get_all_progress($project_id)` - Returns all progress data
- `pj_clear_progress_handler()` - AJAX handler to clear all data
- `pj_export_progress_csv()` - Exports progress to CSV file
- `pj_save_signature_handler()` - AJAX handler for signature saving

### PDF Generator Functions (`includes/pdf-generator.php`)
- `pj_generate_pdf_report()` - Generates and downloads PDF report
- `pj_generate_pdf_html($progress, $client_name, $consultant_name, $logo_url)` - Generates HTML for PDF
- `pj_preview_report()` - Shows PDF preview in browser

### Admin Page Functions (`includes/admin-page.php`)
- `pj_admin_main_page()` - Main dashboard page
- `pj_admin_settings_page()` - Settings configuration page
- `pj_admin_reports_page()` - PDF generation and reports page
- `pj_admin_data_page()` - Progress data table view

---

## 5. WORDPRESS AJAX ACTIONS

All AJAX actions use prefix `pj_`

| Action Name | Handler Function | Privileges | Description |
|------------|------------------|------------|-------------|
| `pj_save_progress` | `pj_save_progress_handler()` | Logged in + Non-logged in | Save checkbox state |
| `pj_clear_progress` | `pj_clear_progress_handler()` | Admin only | Clear all progress data |
| `pj_save_signature` | `pj_save_signature_handler()` | Logged in + Non-logged in | Save signature data |

**Nonce:** `pj_save_progress_nonce` (used for save_progress and save_signature)

---

## 6. WORDPRESS ADMIN-POST ACTIONS

| Action Name | Handler Function | Description |
|------------|------------------|-------------|
| `pj_generate_pdf` | `pj_generate_pdf_report()` | Generate and download PDF |
| `pj_preview_report` | `pj_preview_report()` | Preview PDF in browser |
| `pj_export_progress` | `pj_export_progress_csv()` | Export CSV file |

---

## 7. WORDPRESS HOOKS & FILTERS

### Action Hooks
- `register_activation_hook(__FILE__, 'pj_create_tables')` - Plugin activation
- `add_action('wp_enqueue_scripts', 'pj_enqueue_assets')` - Frontend assets
- `add_action('admin_enqueue_scripts', 'pj_admin_enqueue_assets')` - Admin assets
- `add_action('admin_menu', 'pj_add_admin_menu')` - Admin menu registration
- `add_action('wp_ajax_pj_save_progress', 'pj_save_progress_handler')` - AJAX logged in
- `add_action('wp_ajax_nopriv_pj_save_progress', 'pj_save_progress_handler')` - AJAX non-logged
- `add_action('wp_ajax_pj_clear_progress', 'pj_clear_progress_handler')` - AJAX admin
- `add_action('wp_ajax_pj_save_signature', 'pj_save_signature_handler')` - AJAX logged in
- `add_action('wp_ajax_nopriv_pj_save_signature', 'pj_save_signature_handler')` - AJAX non-logged
- `add_action('admin_post_pj_generate_pdf', 'pj_generate_pdf_report')` - PDF generation
- `add_action('admin_post_pj_preview_report', 'pj_preview_report')` - PDF preview
- `add_action('admin_post_pj_export_progress', 'pj_export_progress_csv')` - CSV export

### Shortcodes
- `add_shortcode('project_journey_roadmap', 'pj_roadmap_shortcode')` - Main roadmap display

---

## 8. ADMIN MENU STRUCTURE

**Parent Menu:**
- **Page Title:** 30/60/90 Project Journey
- **Menu Title:** Project Journey
- **Slug:** `project-journey`
- **Icon:** `dashicons-calendar-alt`
- **Position:** 65
- **Callback:** `pj_admin_main_page()`

**Submenu Pages:**

1. **Settings**
   - Slug: `project-journey-settings`
   - Callback: `pj_admin_settings_page()`

2. **Reports**
   - Slug: `project-journey-reports`
   - Callback: `pj_admin_reports_page()`

3. **Progress Data**
   - Slug: `project-journey-data`
   - Callback: `pj_admin_data_page()`

---

## 9. SHORTCODE ATTRIBUTES

### `[project_journey_roadmap]`

| Attribute | Default | Description |
|-----------|---------|-------------|
| `project_id` | `1` | Project ID to load |
| `editable` | `'true'` | Whether checkboxes are clickable |

**Examples:**
```
[project_journey_roadmap]
[project_journey_roadmap project_id="1"]
[project_journey_roadmap editable="false"]
```

---

## 10. JAVASCRIPT OBJECTS & VARIABLES

### Localized Object: `pjAjax`
Created by `wp_localize_script('pj-roadmap-scripts', 'pjAjax', ...)`

```javascript
pjAjax = {
    ajaxurl: admin_url('admin-ajax.php'),
    nonce: wp_create_nonce('pj_save_progress_nonce'),
    userId: get_current_user_id()
}
```

### jQuery Event Handlers (`assets/script.js`)
- `.wormhole-roadmap .checkbox.clickable` - Click handler for checkboxes
- `updateProgressStats()` - Updates progress counters
- `updatePhaseProgress()` - Updates per-phase completion %
- `escapeHtml(text)` - Sanitizes HTML output

---

## 11. CSS CLASSES & SELECTORS

### Roadmap Container Classes
- `.wormhole-roadmap` - Main roadmap container
- `.phase-marker` - Phase header sections
- `.phase-1`, `.phase-2`, `.phase-3` - Individual phase markers
- `.phase-progress-indicator` - Shows % completion per phase

### Checklist Item Classes
- `.checklist-item` - Individual task row
- `.checklist-item.completed` - Completed task state
- `.checkbox` - Checkbox visual element
- `.checkbox.clickable` - Interactive checkbox
- `.checklist-text` - Task description text
- `.checklist-content` - Task content wrapper
- `.completion-info` - "Completed by X on Y" metadata

### Data Attributes
- `[data-task-id]` - Unique task identifier
- `[data-phase]` - Phase: "phase1", "phase2", "phase3"
- `[data-objective]` - Objective: "A", "B", "C", etc.
- `[data-project-id]` - Project ID number

### Admin Page Classes
- `.pj-admin-dashboard` - Dashboard container
- `.pj-welcome-panel` - Welcome info box
- `.pj-stats-grid` - Statistics grid layout
- `.pj-stat-box` - Individual stat card
- `.pj-stat-number` - Large stat number
- `.pj-stat-label` - Stat description
- `.pj-reports-panel` - Reports page container
- `.pj-data-actions` - Data page action buttons

---

## 12. FILE STRUCTURE

```
30-60-90-project-journey/
├── 30-60-90-project-journey.php    # Main plugin file
├── composer.json                    # Composer dependencies
├── composer.lock                    # Composer lock file
├── vendor/                          # Composer packages (mPDF)
├── includes/
│   ├── form-handler.php            # AJAX handlers, data functions
│   ├── pdf-generator.php           # PDF generation logic
│   ├── admin-page.php              # Admin page callbacks
│   └── roadmap-display.php         # Frontend roadmap template
├── assets/
│   ├── style.css                   # Frontend styles
│   ├── script.js                   # Frontend JavaScript
│   ├── admin-style.css             # Admin styles
│   └── admin-script.js             # Admin JavaScript
├── docker/                          # Docker development setup
├── .env                            # Environment variables
├── docker-compose.yml              # Docker Compose config
├── Dockerfile                      # Custom WordPress image
└── README.md                       # Plugin documentation
```

---

## 13. PHASE & OBJECTIVE VALUES

### Valid Phase Values
- `phase1` - Days 1-30: Discovery, Foundations & Communication
- `phase2` - Days 31-60: Website Build, Booking, Funnel & AI Support
- `phase3` - Days 61-90: Refinement, Launch, and Momentum

### Valid Objective Values
Letters A-Z (commonly A, B, C, D, E per phase)

---

## 14. PDF GENERATION SETTINGS

### mPDF Configuration
```php
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 30,
    'margin_bottom' => 25,
    'margin_header' => 10,
    'margin_footer' => 10,
    'tempDir' => sys_get_temp_dir()  // Uses system temp (no permissions needed)
]);
```

### PDF Output Filename Format
`30-60-90-project-journey-YYYY-MM-DD.pdf`

### CSV Output Filename Format
`project-journey-progress-YYYY-MM-DD-HH-MM-SS.csv`

---

## 15. NONCE VALUES

| Context | Nonce Action | Nonce Field Name |
|---------|--------------|------------------|
| AJAX Progress Save | `pj_save_progress_nonce` | `nonce` (POST param) |
| Settings Form | `pj_save_settings_nonce` | `pj_save_settings_nonce_field` |

---

## 16. SECURITY CHECKS

### Capability Requirements
- Admin pages: `current_user_can('manage_options')`
- PDF generation: `current_user_can('manage_options')`
- CSV export: `current_user_can('manage_options')`
- Data clearing: `current_user_can('manage_options')`
- Progress saving: No capability required (public + logged-in)

### Input Sanitization Functions Used
- `intval()` - User IDs, Project IDs
- `sanitize_text_field()` - Short text inputs
- `sanitize_textarea_field()` - Long text inputs
- `esc_url_raw()` - URL inputs
- `sanitize_email()` - Email inputs
- `esc_html()` - HTML output
- `esc_url()` - URL output
- `esc_attr()` - Attribute output

---

## 17. DOCKER ENVIRONMENT

### Container Names
- `sojourn-wordpress` - WordPress + PHP 8.1 + Composer
- `sojourn-mysql` - MySQL 8.0 database
- `sojourn-phpmyadmin` - Database management UI

### Ports
- WordPress: `8675`
- phpMyAdmin: `8676`

### Database Credentials (.env)
- Database: `wordpress_kb`
- User: `wordpress`
- Password: `wordpress123`
- Root Password: `rootpassword123`

### Volume Mounts
- `sojourn_wordpress_data` - WordPress files
- `sojourn_db_data` - MySQL data
- Plugin directory mounted at: `/var/www/html/wp-content/plugins/30-60-90-project-journey`

---

## 18. NAMING CONVENTIONS

**ALL identifiers in this plugin follow these rules:**

1. **Database tables:** `wp_project_journey_{name}`
2. **Options:** `pj_{name}`
3. **Functions:** `pj_{name}()`
4. **AJAX actions:** `pj_{name}`
5. **Admin-post actions:** `pj_{name}`
6. **CSS classes:** `.pj-{name}` (admin) or `.wormhole-roadmap` (frontend)
7. **JavaScript object:** `pjAjax`
8. **Constants:** `PJ_{NAME}`
9. **Admin menu slugs:** `project-journey-{name}`
10. **Nonces:** `pj_{name}_nonce`

**DO NOT deviate from these patterns!**

---

## 19. KNOWN INTEGRATIONS

### Third-Party Libraries
- **mPDF** v8.2.6 - PDF generation (`vendor/mpdf/mpdf`)
- **Composer** - Dependency management

### WordPress Dependencies
- jQuery (enqueued by WordPress)
- WordPress AJAX API
- WordPress Options API
- WordPress Database API ($wpdb)

---

## 20. CRITICAL RULES

### ⚠️ BEFORE CREATING ANYTHING NEW:

1. **Search this document first** - Does it already exist?
2. **Follow naming conventions** - Use the `pj_` prefix
3. **Don't create parallel systems** - Use existing tables/functions
4. **Don't guess** - If unsure, check the actual code
5. **Update this document** - When adding new features

### ⚠️ NEVER DO THIS:

- ❌ Create `wp_pj_task_completions` (wrong table name!)
- ❌ Create new functions without `pj_` prefix
- ❌ Create new options without `pj_` prefix
- ❌ Hardcode values that should use constants
- ❌ Create duplicate AJAX handlers
- ❌ Use different nonce names
- ❌ Invent new naming patterns

---

## DOCUMENT MAINTENANCE

**Last Updated:** 2025-11-22
**Updated By:** Claude Code (PrometheanLink)
**Plugin Version:** 1.0.0

**When to update this document:**
- Adding new database tables or columns
- Adding new WordPress options
- Adding new functions or AJAX handlers
- Changing naming conventions
- Adding new admin pages or menu items
- Modifying security/capability checks
- Adding new features

---

*This is the SINGLE SOURCE OF TRUTH for the 30/60/90 Project Journey plugin architecture.*
