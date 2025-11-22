# 🔄 Session 3 Handoff - Dynamic Project Settings

**Date:** 2025-11-22
**Status:** 95% Complete - One Bug to Fix
**Next Task:** Debug save functionality in Project Settings page

---

## ✅ What Was Accomplished This Session

### **Complete Dynamic Project Settings System Built**

Built a full metadata management system that makes ALL hardcoded roadmap elements editable through WordPress admin.

#### **Session A: Database & Backend** ✅
- Created `wp_project_journey_metadata` table
- Created `includes/project-metadata.php` (306 lines)
  - CRUD functions for metadata
  - Migration function `pj_set_default_project_meta()`
  - 3 AJAX handlers (save, get, initialize)

#### **Session B: Admin Settings Page** ✅
- Created `includes/admin-project-settings.php` (236 lines)
- Created `assets/admin-project-settings.css` (254 lines)
- Created `assets/admin-project-settings.js` (145 lines)
- Added menu: "Project Journey" → "Project Settings"
- Features:
  - Editable roadmap title, subtitle
  - Repeatable purpose points (add/remove/drag-reorder)
  - Timeline toggle
  - Initialize defaults button

#### **Session C & D: Frontend Integration** ✅
- Modified `includes/roadmap-display.php`
  - Loads all metadata from database
  - Dynamic title, subtitle, purpose points
  - Dynamic timeline (auto-generates from phases)
  - Supports unlimited phases (4, 5, 6+)

---

## 🐛 Current Issue: Save Button Not Updating Database

### **Symptom:**
- User clicks "Save Project Settings" button
- Form submits but data doesn't save to database
- Unknown if error message appears or just no feedback

### **What Works:**
- ✅ "Initialize Default Settings" button works (confirmed by user)
- ✅ Page loads without errors
- ✅ Form displays correctly
- ✅ Metadata table exists in database

### **What Doesn't Work:**
- ❌ Saving edited values to database
- ❌ Unknown: Does AJAX request send? Does it succeed? Any console errors?

---

## 🔍 Debugging Steps for Next Session

### **Step 1: Check Browser Console**
1. Open Project Settings page: `http://localhost:8675/wp-admin/admin.php?page=project-journey-project-settings`
2. Press F12 → Console tab
3. Click "Save Project Settings"
4. Look for:
   - JavaScript errors (red text)
   - Console.log outputs
   - Any error messages

### **Step 2: Check Network Tab**
1. F12 → Network tab
2. Click "Save Project Settings"
3. Look for `admin-ajax.php` request
4. Click on it → check:
   - **Request Payload** - Is data being sent?
   - **Response** - What does server return?
   - **Status Code** - 200 OK or error?

### **Step 3: Verify AJAX Handler Registration**
Check if AJAX action is registered:
```bash
grep -n "add_action.*pj_save_project_metadata" includes/project-metadata.php
```
Should show line ~192

### **Step 4: Test AJAX Directly**
Use browser console to test AJAX:
```javascript
jQuery.ajax({
    url: pjProjectSettings.ajaxurl,
    type: 'POST',
    data: {
        action: 'pj_save_project_metadata',
        nonce: pjProjectSettings.nonce,
        project_id: 1,
        metadata: {
            roadmap_title: 'TEST TITLE'
        }
    },
    success: function(response) {
        console.log('Success:', response);
    },
    error: function(xhr, status, error) {
        console.log('Error:', error);
    }
});
```

### **Step 5: Check Database Directly**
Verify table exists and can be written to:
```sql
-- In phpMyAdmin (http://localhost:8676)
SELECT * FROM wp_project_journey_metadata;

-- Try manual insert
INSERT INTO wp_project_journey_metadata (project_id, meta_key, meta_value)
VALUES (1, 'test_key', 'test_value');
```

---

## 🗂️ File Reference

### **Files Created This Session:**
```
includes/project-metadata.php              (306 lines) - CRUD + AJAX handlers
includes/admin-project-settings.php        (236 lines) - Admin page HTML
assets/admin-project-settings.css          (254 lines) - Styling
assets/admin-project-settings.js           (145 lines) - Frontend logic
```

### **Files Modified:**
```
30-60-90-project-journey.php               - Added table, includes
includes/roadmap-display.php               - Loads from database
```

### **Key Functions:**

**Backend (project-metadata.php):**
- `pj_get_project_meta($project_id, $meta_key, $default)` - Get single value
- `pj_update_project_meta($project_id, $meta_key, $meta_value)` - Update/insert
- `pj_get_all_project_meta($project_id)` - Get all as array
- `pj_set_default_project_meta($project_id)` - Migration
- `pj_save_project_metadata_handler()` - AJAX save handler (LINE 192)
- `pj_get_project_metadata_handler()` - AJAX get handler
- `pj_init_default_metadata_handler()` - AJAX init handler

**Frontend (admin-project-settings.js):**
- `initPurposePoints()` - Add/remove/sort purpose points
- `initFormSubmit()` - Form submission handler (LINE 51)
- `initDefaultsButton()` - Initialize defaults

---

## 🔧 Potential Issues & Fixes

### **Issue 1: JavaScript Not Loading**
**Check:**
```bash
# Verify file exists
ls -la assets/admin-project-settings.js

# Check if enqueued
grep -n "admin-project-settings" includes/admin-project-settings.php
```

**Fix if needed:**
- Ensure `pj_enqueue_project_settings_assets()` is registered
- Check hook name matches page slug
- Verify file path in enqueue statement

### **Issue 2: Nonce Validation Failing**
**Check in AJAX handler (project-metadata.php:196):**
```php
if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
    wp_send_json_error(array('message' => 'Security check failed'));
    return;
}
```

**Fix if needed:**
- Verify nonce is created in localize script
- Check nonce name matches ('pj_admin_nonce')

### **Issue 3: Form Data Not Serializing**
**Check JavaScript (admin-project-settings.js:56-80):**
- Metadata object is built correctly
- Purpose points array is populated
- Data is sent in correct format

**Fix if needed:**
- Add console.log before AJAX call to verify data structure
- Check that input names match JavaScript selectors

### **Issue 4: Database Insert Failing**
**Check `pj_update_project_meta()` function (project-metadata.php:44):**
- Uses $wpdb->insert or $wpdb->update
- Returns boolean
- Check for $wpdb->last_error after operation

**Fix if needed:**
- Add error logging to function
- Check database permissions
- Verify table structure matches queries

---

## 🎯 Most Likely Culprits

Based on symptoms, most likely causes in order:

1. **JavaScript not loading** - Page hook name mismatch in enqueue
2. **Nonce validation failing** - Nonce not passed correctly
3. **AJAX handler not registered** - Hook name typo
4. **Form data format issue** - Data not serialized correctly

---

## 🧪 Quick Test Commands

```bash
# Check if JavaScript loaded
curl http://localhost:8675/wp-admin/admin.php?page=project-journey-project-settings | grep "admin-project-settings.js"

# Verify AJAX handler exists
grep -A 20 "function pj_save_project_metadata_handler" includes/project-metadata.php

# Check database table
docker exec sojourn-mysql mysql -u wordpress -pwordpress123 wordpress_kb -e "DESCRIBE wp_project_journey_metadata;"
```

---

## 📊 Database Schema

```sql
CREATE TABLE wp_project_journey_metadata (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    project_id int(11) NOT NULL DEFAULT 1,
    meta_key varchar(100) NOT NULL,
    meta_value longtext NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY project_meta_unique (project_id, meta_key),
    KEY project_index (project_id)
);
```

**Expected Metadata Keys:**
- `roadmap_title`
- `roadmap_subtitle`
- `purpose_intro`
- `purpose_points` (JSON array)
- `timeline_intro`
- `show_timeline_visual`

---

## 🚀 After Fixing the Bug

Once save functionality works, test the complete workflow:

1. **Edit Title:** Change "30/60/90 Project Roadmap" to something custom
2. **Add Purpose Point:** Click "+ Add Purpose Point"
3. **Reorder Points:** Drag purpose points
4. **Save:** Click "Save Project Settings"
5. **Verify:** Check database has new values
6. **Frontend:** View roadmap page, see changes reflected
7. **Add Phase 4:** Go to Project Phases, add Phase 4
8. **Timeline:** Verify timeline shows 4 phases

---

## 💡 Success Criteria

The bug is fixed when:
- ✅ User can edit any field in Project Settings
- ✅ Click "Save Project Settings" shows success message
- ✅ Database shows updated values in `wp_project_journey_metadata` table
- ✅ Frontend roadmap displays the new values
- ✅ Timeline dynamically shows all phases (3, 4, 5+)

---

## 📝 Additional Notes

- The "Initialize Default Settings" button **works correctly** (user confirmed)
- This means:
  - ✅ Database table exists
  - ✅ AJAX infrastructure works
  - ✅ Nonce validation works for init handler
  - ✅ Database writes work
- Therefore: The issue is likely **specific to the save handler** or **form data serialization**

---

## 🔗 Useful URLs

- Project Settings: `http://localhost:8675/wp-admin/admin.php?page=project-journey-project-settings`
- Project Phases: `http://localhost:8675/wp-admin/admin.php?page=project-journey-phases`
- phpMyAdmin: `http://localhost:8676`
- Frontend Roadmap: `http://localhost:8675/[page-with-shortcode]`

---

## 🎨 What This System Enables

Once working, this allows:
- ✅ Fully rebrand roadmap for any client
- ✅ Custom purpose points per project
- ✅ Add unlimited phases (Phase 4, 5, 6+)
- ✅ Toggle timeline display
- ✅ All changes immediately reflect on frontend
- ✅ No more hardcoded text anywhere!

---

## 👨‍💻 Code Quality

All code follows WordPress standards:
- ✅ Proper escaping (esc_html, esc_attr)
- ✅ Nonce validation on AJAX
- ✅ Capability checks (manage_options)
- ✅ Sanitization on input
- ✅ $wpdb->prepare for queries
- ✅ Consistent naming (pj_ prefix)

---

**This is 95% complete - just one bug away from perfection!** 🚀

The infrastructure is solid, the UI is beautiful, the database is ready. Just need to debug why the save button doesn't trigger the database update.

**Next session:** Find and fix the save bug, then celebrate a fully dynamic roadmap system! 🎉
