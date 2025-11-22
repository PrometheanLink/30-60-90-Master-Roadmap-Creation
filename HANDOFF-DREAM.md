# 🌟 Dream Features Implementation - Client Portal & Templates

**Date:** 2025-11-22
**Features:** Client Portal + Roadmap Templates
**Priority:** High-Value Features
**Complexity:** Medium (3-5 sessions each)

---

## 📋 Overview

This document provides complete implementation specifications for two high-value features:

1. **Client Portal** - Allow clients to view and interact with their roadmap
2. **Roadmap Templates** - Save and reuse roadmap structures

These features transform the plugin from a single-project tool into a scalable, multi-client solution.

---

# 🎯 FEATURE 1: Client Portal

## Vision

Give clients a beautiful, simplified view of their project progress without WordPress admin access.

**What Clients Can Do:**
- ✅ View their roadmap in real-time
- ✅ See which tasks are complete
- ✅ Read all notes and attachments
- ✅ Add comments to tasks (not edit)
- ✅ View revision history
- ✅ Download PDF reports
- ✅ No admin dashboard access needed

**What Clients CANNOT Do:**
- ❌ Edit tasks or roadmap structure
- ❌ Delete notes or attachments
- ❌ Access WordPress admin
- ❌ See other clients' projects
- ❌ Change plugin settings

---

## 🗄️ Database Schema

### New Table: `wp_project_journey_client_access`

Controls which clients can access which projects.

```sql
CREATE TABLE wp_project_journey_client_access (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    project_id int(11) NOT NULL,
    user_id bigint(20) NOT NULL,              -- WordPress user ID
    access_level varchar(50) DEFAULT 'view',  -- 'view', 'comment', 'edit' (future)
    can_download_pdf tinyint(1) DEFAULT 1,
    can_view_notes tinyint(1) DEFAULT 1,
    can_add_comments tinyint(1) DEFAULT 1,
    is_active tinyint(1) DEFAULT 1,
    granted_by bigint(20) NOT NULL,           -- Admin who granted access
    granted_at datetime DEFAULT CURRENT_TIMESTAMP,
    expires_at datetime DEFAULT NULL,          -- Optional expiration
    last_accessed datetime DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY user_project (user_id, project_id),
    KEY project_index (project_id),
    KEY user_index (user_id)
) $charset_collate;
```

### New Table: `wp_project_journey_comments`

Allow clients to comment on tasks (separate from notes).

```sql
CREATE TABLE wp_project_journey_comments (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    project_id int(11) NOT NULL,
    task_id varchar(100) NOT NULL,
    user_id bigint(20) NOT NULL,
    comment_text text NOT NULL,
    parent_comment_id bigint(20) DEFAULT NULL,  -- For threaded comments
    is_private tinyint(1) DEFAULT 0,            -- Hide from client
    created_by_name varchar(255) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY task_index (task_id),
    KEY user_index (user_id),
    KEY parent_index (parent_comment_id),
    KEY project_task (project_id, task_id, created_at)
) $charset_collate;
```

### Modify Existing: `wp_project_journey_progress`

Add field to track client name for the project:

```sql
ALTER TABLE wp_project_journey_progress
ADD COLUMN client_display_name varchar(255) DEFAULT NULL AFTER project_id;
```

---

## 📁 Files to Create

### 1. `includes/client-portal.php`

Main client portal functionality.

```php
<?php
/**
 * Client Portal - Read-only roadmap view for clients
 */

// Register custom user role
function pj_register_client_role() {
    add_role('project_client', 'Project Client', array(
        'read' => true,
        'level_0' => true
    ));
}
register_activation_hook(__FILE__, 'pj_register_client_role');

// Grant access to client
function pj_grant_client_access($user_id, $project_id, $options = array()) { }

// Revoke access from client
function pj_revoke_client_access($user_id, $project_id) { }

// Check if user has access to project
function pj_user_can_access_project($user_id, $project_id) { }

// Get all projects user has access to
function pj_get_user_projects($user_id) { }

// Client portal page callback
function pj_client_portal_page() { }

// Client dashboard (list of their projects)
function pj_client_dashboard() { }

// Client roadmap view (read-only)
function pj_client_roadmap_view($project_id) { }

// AJAX: Add comment to task
add_action('wp_ajax_pj_add_comment', 'pj_add_comment_handler');
add_action('wp_ajax_nopriv_pj_add_comment', 'pj_add_comment_handler');
function pj_add_comment_handler() { }

// AJAX: Get comments for task
add_action('wp_ajax_pj_get_comments', 'pj_get_comments_handler');
add_action('wp_ajax_nopriv_pj_get_comments', 'pj_get_comments_handler');
function pj_get_comments_handler() { }

// Email: Notify admin when client comments
function pj_notify_admin_new_comment($task_id, $comment_text, $client_name) { }
```

### 2. `includes/client-dashboard-page.php`

HTML template for client dashboard.

```php
<?php
/**
 * Client Dashboard Template
 * Shows all projects the client has access to
 */

// Get current user projects
$user_id = get_current_user_id();
$projects = pj_get_user_projects($user_id);
?>

<div class="pj-client-dashboard">
    <header>
        <h1>Welcome, <?php echo wp_get_current_user()->display_name; ?>!</h1>
        <p>Your Project Dashboard</p>
    </header>

    <div class="projects-grid">
        <?php foreach ($projects as $project): ?>
            <div class="project-card">
                <h3><?php echo esc_html($project['name']); ?></h3>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo $project['completion']; ?>%"></div>
                </div>
                <p><?php echo $project['completion']; ?>% Complete</p>
                <a href="<?php echo pj_get_client_portal_url($project['id']); ?>" class="button">View Roadmap</a>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

### 3. `assets/client-portal.css`

Client-facing styles (cleaner, simpler than admin).

```css
/* Clean, minimal design for clients */
.pj-client-dashboard {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 24px;
}

.project-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    transition: all 0.3s ease;
}

.project-card:hover {
    border-color: #10b981;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

/* Progress bar styling */
.progress-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981, #059669);
    transition: width 0.5s ease;
}

/* Client roadmap - read-only styles */
.pj-client-roadmap .checkbox {
    cursor: default; /* Not clickable for clients */
}

.pj-client-roadmap .accordion-toggle {
    background: #3b82f6; /* Blue instead of green (view mode) */
}
```

### 4. `assets/client-portal.js`

JavaScript for client portal interactions.

```javascript
jQuery(document).ready(function($) {
    // Client comment submission
    $('.pj-client-portal').on('click', '.submit-comment', function() {
        var $button = $(this);
        var $item = $button.closest('.checklist-item');
        var taskId = $item.data('task-id');
        var projectId = pjClientPortal.projectId;
        var $textarea = $item.find('.client-comment-textarea');
        var commentText = $textarea.val().trim();

        if (!commentText) {
            alert('Please enter a comment.');
            return;
        }

        $button.prop('disabled', true).text('Posting...');

        $.ajax({
            url: pjClientPortal.ajaxurl,
            type: 'POST',
            data: {
                action: 'pj_add_comment',
                nonce: pjClientPortal.nonce,
                project_id: projectId,
                task_id: taskId,
                comment_text: commentText
            },
            success: function(response) {
                if (response.success) {
                    $textarea.val('');
                    loadTaskComments($item);
                    showNotification('Comment posted successfully!', 'success');
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error'));
                }
                $button.prop('disabled', false).text('Post Comment');
            },
            error: function() {
                alert('Error posting comment. Please try again.');
                $button.prop('disabled', false).text('Post Comment');
            }
        });
    });

    // Load comments for task
    function loadTaskComments($item) {
        var taskId = $item.data('task-id');
        var projectId = pjClientPortal.projectId;
        var $commentsList = $item.find('.comments-list');

        $.ajax({
            url: pjClientPortal.ajaxurl,
            type: 'GET',
            data: {
                action: 'pj_get_comments',
                project_id: projectId,
                task_id: taskId
            },
            success: function(response) {
                if (response.success) {
                    renderComments($commentsList, response.data.comments);
                }
            }
        });
    }

    function renderComments($container, comments) {
        var html = '';
        comments.forEach(function(comment) {
            html += '<div class="comment-item">';
            html += '<div class="comment-header">';
            html += '<strong>' + escapeHtml(comment.created_by_name) + '</strong>';
            html += '<span class="comment-date">' + formatDate(comment.created_at) + '</span>';
            html += '</div>';
            html += '<div class="comment-text">' + escapeHtml(comment.comment_text) + '</div>';
            html += '</div>';
        });
        $container.html(html || '<p class="no-comments">No comments yet.</p>');
    }
});
```

---

## 🔐 Security & Permissions

### WordPress Capabilities

**New Role:** `project_client`
- Can: Read own projects
- Cannot: Access admin dashboard
- Cannot: Edit any WordPress content

### Access Control

```php
// Check before showing client portal
function pj_check_client_access($project_id) {
    $user_id = get_current_user_id();

    // Admins can access everything
    if (current_user_can('manage_options')) {
        return true;
    }

    // Check if user has explicit access
    global $wpdb;
    $access = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}project_journey_client_access
         WHERE user_id = %d AND project_id = %d AND is_active = 1",
        $user_id, $project_id
    ));

    if (!$access) {
        wp_die('You do not have access to this project.');
    }

    // Check expiration
    if ($access->expires_at && strtotime($access->expires_at) < time()) {
        wp_die('Your access to this project has expired.');
    }

    // Update last accessed time
    $wpdb->update(
        $wpdb->prefix . 'project_journey_client_access',
        array('last_accessed' => current_time('mysql')),
        array('id' => $access->id)
    );

    return true;
}
```

---

## 🎨 Client Portal UI/UX

### URL Structure

```
/client-portal/              → Client dashboard (list projects)
/client-portal/{project-id}/ → View specific roadmap
```

### Navigation Flow

```
Client Login
    ↓
Client Dashboard (shows all their projects)
    ↓
Select Project
    ↓
View Roadmap (read-only + comments)
    ↓
Can: View notes, attachments, comments
Can: Add new comments
Can: Download PDF
Cannot: Edit anything
```

---

## 📊 Admin Management

### New Admin Submenu: "Client Access"

Location: **Project Journey → Client Access**

**Features:**
- List all clients with access
- Grant access to new clients
- Revoke access
- Set expiration dates
- Configure permissions per client
- View client activity log

**Admin Page Wireframe:**

```
┌────────────────────────────────────────────────────────┐
│ Client Access Management          [+ Grant New Access] │
├────────────────────────────────────────────────────────┤
│                                                         │
│ ┌─ Active Clients ─────────────────────────────────┐  │
│ │                                                    │  │
│ │ Client Name    | Project      | Access | Actions │  │
│ │ ─────────────────────────────────────────────────│  │
│ │ Kim Benedict   | Project 1    | View   | [Revoke]│  │
│ │ John Doe       | Project 2    | Comment| [Edit]  │  │
│ │                                                    │  │
│ └────────────────────────────────────────────────────┘  │
│                                                         │
│ ┌─ Grant Access Modal ─────────────────────────────┐  │
│ │ Select User:     [Dropdown]                       │  │
│ │ Project:         [Dropdown]                       │  │
│ │ Access Level:    [ ] View Only                    │  │
│ │                  [✓] Can Comment                  │  │
│ │ Can Download PDF [✓]                              │  │
│ │ Expires:         [Date Picker] (Optional)         │  │
│ │                  [Grant Access] [Cancel]          │  │
│ └────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────┘
```

---

## 🚀 Implementation Checklist - Client Portal

**Session 1: Database & Roles**
- [ ] Create `wp_project_journey_client_access` table
- [ ] Create `wp_project_journey_comments` table
- [ ] Register `project_client` user role
- [ ] Add activation hook for tables and role

**Session 2: Backend Functions**
- [ ] Write `pj_grant_client_access()`
- [ ] Write `pj_revoke_client_access()`
- [ ] Write `pj_user_can_access_project()`
- [ ] Write `pj_get_user_projects()`
- [ ] AJAX handler for comments

**Session 3: Client Dashboard UI**
- [ ] Create client dashboard page
- [ ] Show list of projects with progress
- [ ] Add CSS for client portal
- [ ] Create client navigation menu

**Session 4: Client Roadmap View**
- [ ] Read-only roadmap display
- [ ] Show notes/attachments (no edit)
- [ ] Add comment section per task
- [ ] Test all permissions

**Session 5: Admin Management**
- [ ] Admin page for client access
- [ ] Grant/revoke UI
- [ ] Activity log
- [ ] Email notifications

---

# 🎯 FEATURE 2: Roadmap Templates

## Vision

Save successful roadmap structures as reusable templates. Never recreate the same roadmap twice!

**What This Enables:**
- ✅ Save any roadmap as a template
- ✅ Load template for new projects
- ✅ Template library (browse all saved templates)
- ✅ Export/Import templates as JSON
- ✅ Clone from existing project
- ✅ Share templates between sites

---

## 🗄️ Database Schema

### New Table: `wp_project_journey_templates`

Stores template metadata.

```sql
CREATE TABLE wp_project_journey_templates (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    template_name varchar(255) NOT NULL,
    template_description text,
    template_category varchar(100) DEFAULT 'general',  -- 'coaching', 'marketing', 'development'
    created_by bigint(20) NOT NULL,
    created_by_name varchar(255) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_public tinyint(1) DEFAULT 0,                    -- Share with other admins
    times_used int(11) DEFAULT 0,
    last_used datetime DEFAULT NULL,
    thumbnail_url varchar(500) DEFAULT NULL,            -- Optional preview image
    PRIMARY KEY (id),
    KEY created_by_index (created_by),
    KEY category_index (template_category)
) $charset_collate;
```

### New Table: `wp_project_journey_template_phases`

Template phases (mirrors structure of actual phases).

```sql
CREATE TABLE wp_project_journey_template_phases (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    template_id bigint(20) NOT NULL,
    phase_key varchar(50) NOT NULL,
    phase_number int(11) NOT NULL,
    phase_title varchar(255) NOT NULL,
    phase_subtitle text,
    phase_description text,
    sort_order int(11) DEFAULT 0,
    PRIMARY KEY (id),
    KEY template_index (template_id),
    KEY template_phase (template_id, sort_order)
) $charset_collate;
```

### New Table: `wp_project_journey_template_objectives`

Template objectives.

```sql
CREATE TABLE wp_project_journey_template_objectives (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    template_id bigint(20) NOT NULL,
    phase_id bigint(20) NOT NULL,
    objective_key varchar(50) NOT NULL,
    objective_title varchar(255) NOT NULL,
    objective_subtitle text,
    sort_order int(11) DEFAULT 0,
    PRIMARY KEY (id),
    KEY template_index (template_id),
    KEY phase_index (phase_id),
    KEY template_phase_objective (template_id, phase_id, sort_order)
) $charset_collate;
```

### New Table: `wp_project_journey_template_tasks`

Template tasks.

```sql
CREATE TABLE wp_project_journey_template_tasks (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    template_id bigint(20) NOT NULL,
    phase_id bigint(20) NOT NULL,
    objective_id bigint(20) NOT NULL,
    task_text text NOT NULL,
    task_details text,
    owner varchar(50) DEFAULT 'both',
    estimated_hours int(11) DEFAULT NULL,               -- Optional
    sort_order int(11) DEFAULT 0,
    PRIMARY KEY (id),
    KEY template_index (template_id),
    KEY objective_index (objective_id),
    KEY template_objective_task (template_id, objective_id, sort_order)
) $charset_collate;
```

---

## 📁 Files to Create

### 1. `includes/templates.php`

Template management functions.

```php
<?php
/**
 * Roadmap Templates
 * Save and reuse roadmap structures
 */

// Save current roadmap as template
function pj_save_as_template($project_id, $template_name, $template_data) {
    global $wpdb;

    // Create template record
    $template_id = $wpdb->insert(
        $wpdb->prefix . 'project_journey_templates',
        array(
            'template_name' => $template_name,
            'template_description' => $template_data['description'],
            'template_category' => $template_data['category'],
            'created_by' => get_current_user_id(),
            'created_by_name' => wp_get_current_user()->display_name
        )
    );

    // Copy all phases, objectives, tasks from project to template
    // ... implementation

    return $template_id;
}

// Load template into new project
function pj_load_template($template_id, $project_id) {
    // Copy all template phases/objectives/tasks to project
    // Generate new task IDs
    // Return success/failure
}

// Get all templates
function pj_get_all_templates($category = null) { }

// Get template by ID
function pj_get_template($template_id) { }

// Delete template
function pj_delete_template($template_id) { }

// Export template as JSON
function pj_export_template_json($template_id) { }

// Import template from JSON
function pj_import_template_json($json_data) { }

// Clone existing project as template
function pj_clone_project_as_template($project_id, $template_name) { }

// Update times used counter
function pj_increment_template_usage($template_id) { }

// AJAX: Save as template
add_action('wp_ajax_pj_save_template', 'pj_save_template_handler');
function pj_save_template_handler() { }

// AJAX: Load template
add_action('wp_ajax_pj_load_template', 'pj_load_template_handler');
function pj_load_template_handler() { }

// AJAX: Delete template
add_action('wp_ajax_pj_delete_template', 'pj_delete_template_handler');
function pj_delete_template_handler() { }

// AJAX: Export template
add_action('wp_ajax_pj_export_template', 'pj_export_template_handler');
function pj_export_template_handler() { }

// AJAX: Import template
add_action('wp_ajax_pj_import_template', 'pj_import_template_handler');
function pj_import_template_handler() { }
```

---

## 🎨 Admin UI - Template Library

### New Admin Submenu: "Templates"

Location: **Project Journey → Templates**

**Features:**
- Browse all saved templates
- Preview template structure
- Load template for project
- Create new template
- Export/Import templates
- Delete templates

**Admin Page Wireframe:**

```
┌──────────────────────────────────────────────────────────┐
│ Roadmap Templates                   [+ Create Template]  │
├──────────────────────────────────────────────────────────┤
│                                                           │
│ Filter: [All Categories ▼]  Search: [____________] 🔍    │
│                                                           │
│ ┌─ 30/60/90 Coaching Roadmap ────────────── [Actions ▼]─┐│
│ │ Category: Coaching                                     ││
│ │ Created by: Walter Hieber on Nov 22, 2025             ││
│ │ Used: 15 times | Last used: 2 days ago                ││
│ │                                                         ││
│ │ 📋 3 Phases • 12 Objectives • 87 Tasks                ││
│ │                                                         ││
│ │ [Load Template] [Preview] [Export JSON] [Delete]      ││
│ └─────────────────────────────────────────────────────────┘│
│                                                           │
│ ┌─ Marketing Launch Plan ──────────────── [Actions ▼]─┐  │
│ │ Category: Marketing                                   │  │
│ │ Created by: Walter Hieber on Nov 20, 2025            │  │
│ │ Used: 8 times | Last used: 1 week ago                │  │
│ │                                                        │  │
│ │ 📋 4 Phases • 16 Objectives • 124 Tasks              │  │
│ │                                                        │  │
│ │ [Load Template] [Preview] [Export JSON] [Delete]     │  │
│ └────────────────────────────────────────────────────────┘  │
│                                                           │
│ ┌─ Quick Actions ──────────────────────────────────────┐  │
│ │ [Save Current Project as Template]                    │  │
│ │ [Import Template from JSON]                           │  │
│ │ [Browse Template Marketplace] (Future)                │  │
│ └───────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────┘
```

---

## 🎯 Template Categories

Pre-defined categories for organizing templates:

- **Coaching** - Life coaching, business coaching, wellness
- **Marketing** - Campaign launches, content strategy, SEO
- **Development** - Software projects, website builds, app development
- **Consulting** - Business consulting, strategy, transformation
- **Creative** - Design projects, branding, content creation
- **General** - Miscellaneous or custom

---

## 📤 Export/Import Format

### JSON Structure

```json
{
  "template_name": "30/60/90 Coaching Roadmap",
  "template_description": "Complete coaching practice setup roadmap",
  "template_category": "coaching",
  "version": "1.0",
  "created_at": "2025-11-22",
  "phases": [
    {
      "phase_key": "phase1",
      "phase_number": 1,
      "phase_title": "Phase 1: Days 1–30",
      "phase_subtitle": "Discovery, Foundations & Communication",
      "objectives": [
        {
          "objective_key": "A",
          "objective_title": "Communication & Working Agreement",
          "objective_subtitle": "Establish a predictable way of working together",
          "tasks": [
            {
              "task_text": "Decide preferred communication style",
              "owner": "client",
              "estimated_hours": 1
            },
            {
              "task_text": "Confirm primary communication channels",
              "owner": "both",
              "estimated_hours": 1
            }
          ]
        }
      ]
    }
  ]
}
```

---

## 🚀 Implementation Checklist - Templates

**Session 1: Database**
- [ ] Create 4 new template tables
- [ ] Add activation hook for tables
- [ ] Write migration function

**Session 2: Core Functions**
- [ ] `pj_save_as_template()`
- [ ] `pj_load_template()`
- [ ] `pj_get_all_templates()`
- [ ] `pj_export_template_json()`
- [ ] `pj_import_template_json()`

**Session 3: Admin UI**
- [ ] Template library page
- [ ] Template cards with preview
- [ ] Create/Delete/Load buttons
- [ ] Category filtering

**Session 4: Export/Import**
- [ ] JSON export functionality
- [ ] JSON import with validation
- [ ] File download/upload UI
- [ ] Error handling

**Session 5: Integration**
- [ ] Add "Save as Template" to admin menu
- [ ] Add "Load Template" when creating project
- [ ] Template preview modal
- [ ] Usage statistics

---

## 🔗 Integration Between Features

**Client Portal + Templates:**
- Admins can load template for new client project
- Each client sees their specific project (not template)
- Template statistics show how often used per client

**Workflow:**
```
1. Admin loads "Coaching Roadmap" template
2. Admin grants client access to Project #5
3. Client logs in and sees their customized roadmap
4. Both work on the project together
5. Admin can save customized version as new template
```

---

## 📊 Success Metrics

**Client Portal Success:**
- ✅ Clients can log in without admin credentials
- ✅ Clients can view their roadmap
- ✅ Clients can add comments
- ✅ Admins receive notifications
- ✅ Zero security issues

**Templates Success:**
- ✅ Can save any roadmap as template
- ✅ Can load template in < 5 seconds
- ✅ Export/Import works flawlessly
- ✅ Templates reduce setup time by 80%

---

## 🎉 Future Enhancements

**Client Portal v2:**
- [ ] Client file uploads
- [ ] Client task approval workflow
- [ ] Client calendar view
- [ ] Client mobile app

**Templates v2:**
- [ ] Template Marketplace (buy/sell)
- [ ] Community templates
- [ ] AI-generated templates
- [ ] Template versioning
- [ ] Template merging (combine 2 templates)

---

## 💡 Quick Start Guide

**To implement both features:**

1. **Week 1:** Client Portal database + backend
2. **Week 2:** Client Portal UI + testing
3. **Week 3:** Templates database + backend
4. **Week 4:** Templates UI + testing
5. **Week 5:** Integration + polish

**Total Estimated Time:** 5-6 weeks for both features

---

## 📞 Questions to Answer

**Before Starting:**
1. Should clients be WordPress users or custom auth?
2. Multiple clients per project or 1:1?
3. Template categories - need more/less?
4. Should templates include existing notes/attachments?
5. Template permissions - who can share templates?

---

**End of Dream Features Implementation Guide**

*These two features will 10x the value of this plugin!* 🚀
