<?php
if (!defined('ABSPATH')) exit;

/**
 * Task Notes & Journal Feature
 * Handles project journal entries, revision history, and file attachments
 */

/**
 * Save task note with revision tracking
 */
add_action('wp_ajax_pj_save_task_note', 'pj_save_task_note_handler');
add_action('wp_ajax_nopriv_pj_save_task_note', 'pj_save_task_note_handler');

function pj_save_task_note_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_save_progress_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_notes';

    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $task_id = isset($_POST['task_id']) ? sanitize_text_field($_POST['task_id']) : '';
    $note_text = isset($_POST['note_text']) ? wp_kses_post($_POST['note_text']) : '';
    $change_reason = isset($_POST['change_reason']) ? sanitize_textarea_field($_POST['change_reason']) : '';
    $user_id = get_current_user_id();

    if (empty($task_id)) {
        wp_send_json_error(array('message' => 'Task ID is required'));
        return;
    }

    if (empty($note_text)) {
        wp_send_json_error(array('message' => 'Note text is required'));
        return;
    }

    // Get user display name
    $user = get_userdata($user_id);
    $user_name = $user ? $user->display_name : 'Guest User';

    // Get current max revision number for this task
    $max_revision = $wpdb->get_var($wpdb->prepare(
        "SELECT MAX(revision_number) FROM $table WHERE project_id = %d AND task_id = %s",
        $project_id, $task_id
    ));

    $new_revision = $max_revision ? ($max_revision + 1) : 1;

    // Insert new note revision
    $result = $wpdb->insert(
        $table,
        array(
            'project_id' => $project_id,
            'task_id' => $task_id,
            'note_text' => $note_text,
            'change_reason' => $change_reason,
            'revision_number' => $new_revision,
            'created_by' => $user_id,
            'created_by_name' => $user_name,
            'created_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
    );

    if ($result !== false) {
        $note_id = $wpdb->insert_id;

        // Send notification if enabled
        if (get_option('pj_email_notifications') == '1') {
            pj_send_note_notification($task_id, $note_text, $user_name, $new_revision);
        }

        wp_send_json_success(array(
            'message' => 'Note saved successfully',
            'note_id' => $note_id,
            'revision_number' => $new_revision,
            'created_by' => $user_name,
            'created_at' => current_time('mysql')
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to save note'));
    }
}

/**
 * Get all notes for a task (revision history)
 */
add_action('wp_ajax_pj_get_task_notes', 'pj_get_task_notes_handler');
add_action('wp_ajax_nopriv_pj_get_task_notes', 'pj_get_task_notes_handler');

function pj_get_task_notes_handler() {
    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 1;
    $task_id = isset($_GET['task_id']) ? sanitize_text_field($_GET['task_id']) : '';

    if (empty($task_id)) {
        wp_send_json_error(array('message' => 'Task ID is required'));
        return;
    }

    $notes = pj_get_task_notes($project_id, $task_id);
    wp_send_json_success(array('notes' => $notes));
}

/**
 * Helper function to get task notes
 */
function pj_get_task_notes($project_id, $task_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_notes';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE project_id = %d AND task_id = %s ORDER BY revision_number DESC",
        $project_id, $task_id
    ), ARRAY_A);
}

/**
 * Get latest note for a task
 */
function pj_get_latest_task_note($project_id, $task_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_notes';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE project_id = %d AND task_id = %s ORDER BY revision_number DESC LIMIT 1",
        $project_id, $task_id
    ), ARRAY_A);
}

/**
 * Upload task attachment
 */
add_action('wp_ajax_pj_upload_task_attachment', 'pj_upload_task_attachment_handler');
add_action('wp_ajax_nopriv_pj_upload_task_attachment', 'pj_upload_task_attachment_handler');

function pj_upload_task_attachment_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_save_progress_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    // Check if file was uploaded
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error(array('message' => 'No file uploaded or upload error'));
        return;
    }

    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $task_id = isset($_POST['task_id']) ? sanitize_text_field($_POST['task_id']) : '';
    $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : null;
    $user_id = get_current_user_id();

    if (empty($task_id)) {
        wp_send_json_error(array('message' => 'Task ID is required'));
        return;
    }

    // Get user display name
    $user = get_userdata($user_id);
    $user_name = $user ? $user->display_name : 'Guest User';

    // Validate file type (security) - comprehensive list for business communications
    $allowed_types = array(
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp',
        // Documents
        'pdf', 'doc', 'docx', 'odt', 'rtf', 'txt',
        // Spreadsheets
        'xls', 'xlsx', 'ods', 'csv',
        // Presentations
        'ppt', 'pptx', 'odp',
        // Archives
        'zip', 'rar', '7z', 'tar', 'gz',
        // Audio
        'mp3', 'wav', 'ogg', 'm4a',
        // Video
        'mp4', 'mov', 'avi', 'wmv', 'flv', 'webm',
        // Other
        'json', 'xml', 'css', 'html', 'js'
    );

    $file_info = pathinfo($_FILES['file']['name']);
    $file_ext = strtolower($file_info['extension']);

    if (!in_array($file_ext, $allowed_types)) {
        wp_send_json_error(array('message' => 'File type not allowed'));
        return;
    }

    // Validate file size (10MB max)
    $max_size = 10 * 1024 * 1024; // 10MB in bytes
    if ($_FILES['file']['size'] > $max_size) {
        wp_send_json_error(array('message' => 'File size exceeds 10MB limit'));
        return;
    }

    // Use WordPress upload handling
    require_once(ABSPATH . 'wp-admin/includes/file.php');

    $upload_overrides = array('test_form' => false);
    $uploaded_file = wp_handle_upload($_FILES['file'], $upload_overrides);

    if (isset($uploaded_file['error'])) {
        wp_send_json_error(array('message' => $uploaded_file['error']));
        return;
    }

    // Save to database
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_attachments';

    $result = $wpdb->insert(
        $table,
        array(
            'note_id' => $note_id,
            'project_id' => $project_id,
            'task_id' => $task_id,
            'file_name' => sanitize_file_name($file_info['basename']),
            'file_path' => $uploaded_file['url'],
            'file_type' => $uploaded_file['type'],
            'file_size' => $_FILES['file']['size'],
            'uploaded_by' => $user_id,
            'uploaded_by_name' => $user_name,
            'uploaded_at' => current_time('mysql')
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
    );

    if ($result !== false) {
        $attachment_id = $wpdb->insert_id;

        wp_send_json_success(array(
            'message' => 'File uploaded successfully',
            'attachment_id' => $attachment_id,
            'file_name' => $file_info['basename'],
            'file_url' => $uploaded_file['url'],
            'file_type' => $uploaded_file['type'],
            'file_size' => $_FILES['file']['size'],
            'uploaded_by' => $user_name
        ));
    } else {
        // Delete uploaded file if database insert fails
        @unlink($uploaded_file['file']);
        wp_send_json_error(array('message' => 'Failed to save attachment to database'));
    }
}

/**
 * Get attachments for a task
 */
add_action('wp_ajax_pj_get_task_attachments', 'pj_get_task_attachments_handler');
add_action('wp_ajax_nopriv_pj_get_task_attachments', 'pj_get_task_attachments_handler');

function pj_get_task_attachments_handler() {
    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 1;
    $task_id = isset($_GET['task_id']) ? sanitize_text_field($_GET['task_id']) : '';

    if (empty($task_id)) {
        wp_send_json_error(array('message' => 'Task ID is required'));
        return;
    }

    $attachments = pj_get_task_attachments($project_id, $task_id);
    wp_send_json_success(array('attachments' => $attachments));
}

/**
 * Helper function to get task attachments
 */
function pj_get_task_attachments($project_id, $task_id, $note_id = null) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_attachments';

    if ($note_id) {
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE project_id = %d AND task_id = %s AND note_id = %d ORDER BY uploaded_at DESC",
            $project_id, $task_id, $note_id
        ), ARRAY_A);
    } else {
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table WHERE project_id = %d AND task_id = %s ORDER BY uploaded_at DESC",
            $project_id, $task_id
        ), ARRAY_A);
    }
}

/**
 * Delete attachment
 */
add_action('wp_ajax_pj_delete_task_attachment', 'pj_delete_task_attachment_handler');

function pj_delete_task_attachment_handler() {
    // Only admins can delete attachments
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_save_progress_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;

    if (!$attachment_id) {
        wp_send_json_error(array('message' => 'Attachment ID is required'));
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_attachments';

    // Get file path before deleting from database
    $attachment = $wpdb->get_row($wpdb->prepare(
        "SELECT file_path FROM $table WHERE id = %d",
        $attachment_id
    ), ARRAY_A);

    if (!$attachment) {
        wp_send_json_error(array('message' => 'Attachment not found'));
        return;
    }

    // Delete from database
    $result = $wpdb->delete($table, array('id' => $attachment_id), array('%d'));

    if ($result !== false) {
        // Try to delete physical file
        $file_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $attachment['file_path']);
        @unlink($file_path);

        wp_send_json_success(array('message' => 'Attachment deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to delete attachment'));
    }
}

/**
 * Send email notification when note is added
 */
function pj_send_note_notification($task_id, $note_text, $user_name, $revision_number) {
    $to = get_option('pj_notification_email', get_option('admin_email'));
    $subject = '30/60/90 Project Journey - Task Note Added';

    $message = "A new note has been added to a task:\n\n";
    $message .= "Task ID: {$task_id}\n";
    $message .= "Revision: #{$revision_number}\n";
    $message .= "Added By: {$user_name}\n";
    $message .= "Note:\n" . wp_strip_all_tags($note_text) . "\n\n";
    $message .= "View full progress: " . admin_url('admin.php?page=project-journey-data');

    wp_mail($to, $subject, $message);
}

/**
 * Get task note count (for display)
 */
function pj_get_task_note_count($project_id, $task_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_notes';

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE project_id = %d AND task_id = %s",
        $project_id, $task_id
    ));
}

/**
 * Get task attachment count (for display)
 */
function pj_get_task_attachment_count($project_id, $task_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_task_attachments';

    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE project_id = %d AND task_id = %s",
        $project_id, $task_id
    ));
}
