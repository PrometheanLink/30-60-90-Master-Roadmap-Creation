<?php
if (!defined('ABSPATH')) exit;

/**
 * Handle AJAX checkbox save
 */
add_action('wp_ajax_pj_save_progress', 'pj_save_progress_handler');
add_action('wp_ajax_nopriv_pj_save_progress', 'pj_save_progress_handler');

function pj_save_progress_handler() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_save_progress_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_progress';

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $task_id = isset($_POST['task_id']) ? sanitize_text_field($_POST['task_id']) : '';
    $task_text = isset($_POST['task_text']) ? sanitize_textarea_field($_POST['task_text']) : '';
    $phase = isset($_POST['phase']) ? sanitize_text_field($_POST['phase']) : '';
    $objective = isset($_POST['objective']) ? sanitize_text_field($_POST['objective']) : '';
    $completed = isset($_POST['completed']) ? intval($_POST['completed']) : 0;

    if (empty($task_id)) {
        wp_send_json_error(array('message' => 'Task ID is required'));
        return;
    }

    // Get user display name
    $completed_by = null;
    if ($user_id > 0 && $completed) {
        $user = get_userdata($user_id);
        $completed_by = $user ? $user->display_name : 'User #' . $user_id;
    }

    // Check if record exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE user_id = %d AND project_id = %d AND task_id = %s",
        $user_id, $project_id, $task_id
    ));

    if ($exists) {
        // Update existing record
        $result = $wpdb->update(
            $table,
            array(
                'completed' => $completed,
                'completed_by' => $completed ? $completed_by : null,
                'completed_at' => $completed ? current_time('mysql') : null
            ),
            array('id' => $exists),
            array('%d', '%s', '%s'),
            array('%d')
        );
    } else {
        // Insert new record
        $result = $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'project_id' => $project_id,
                'task_id' => $task_id,
                'task_text' => $task_text,
                'phase' => $phase,
                'objective' => $objective,
                'completed' => $completed,
                'completed_by' => $completed ? $completed_by : null,
                'completed_at' => $completed ? current_time('mysql') : null
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
        );
    }

    if ($result !== false) {
        // Send email notification if enabled
        if (get_option('pj_email_notifications') == '1' && $completed) {
            pj_send_completion_notification($task_text, $phase, $objective, $completed_by);
        }

        wp_send_json_success(array(
            'message' => 'Progress saved successfully',
            'completed_by' => $completed_by,
            'completed_at' => $completed ? current_time('mysql') : null
        ));
    } else {
        wp_send_json_error(array('message' => 'Failed to save progress'));
    }
}

/**
 * Send email notification when task is completed
 */
function pj_send_completion_notification($task_text, $phase, $objective, $completed_by) {
    $to = get_option('pj_notification_email', get_option('admin_email'));
    $subject = '30/60/90 Project Journey - Task Completed';

    $message = "A task has been marked as complete:\n\n";
    $message .= "Phase: {$phase}\n";
    $message .= "Objective: {$objective}\n";
    $message .= "Task: {$task_text}\n";
    $message .= "Completed By: {$completed_by}\n";
    $message .= "Completed At: " . current_time('mysql') . "\n\n";
    $message .= "View full progress: " . admin_url('admin.php?page=project-journey-data');

    wp_mail($to, $subject, $message);
}

/**
 * Get user progress for a specific project
 */
function pj_get_user_progress($user_id = 0, $project_id = 1) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_progress';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE user_id = %d AND project_id = %d",
        $user_id, $project_id
    ), ARRAY_A);

    $progress = array();
    foreach ($results as $row) {
        $progress[$row['task_id']] = $row;
    }

    return $progress;
}

/**
 * Get all progress data (for admin)
 */
function pj_get_all_progress($project_id = 1) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_progress';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE project_id = %d ORDER BY created_at DESC",
        $project_id
    ), ARRAY_A);
}

/**
 * Clear all progress data
 */
add_action('wp_ajax_pj_clear_progress', 'pj_clear_progress_handler');
function pj_clear_progress_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_progress';

    $result = $wpdb->query("TRUNCATE TABLE $table");

    if ($result !== false) {
        wp_send_json_success(array('message' => 'All progress data cleared'));
    } else {
        wp_send_json_error(array('message' => 'Failed to clear data'));
    }
}

/**
 * Export progress data as CSV
 */
add_action('admin_post_pj_export_progress', 'pj_export_progress_csv');
function pj_export_progress_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_progress';
    $results = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);

    $filename = 'project-journey-progress-' . date('Y-m-d-H-i-s') . '.csv';

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // CSV Headers
    fputcsv($output, array(
        'ID', 'User ID', 'Project ID', 'Phase', 'Objective', 'Task ID',
        'Task Text', 'Completed', 'Completed By', 'Completed At', 'Created At'
    ));

    // CSV Data
    foreach ($results as $row) {
        fputcsv($output, array(
            $row['id'],
            $row['user_id'],
            $row['project_id'],
            $row['phase'],
            $row['objective'],
            $row['task_id'],
            $row['task_text'],
            $row['completed'] ? 'Yes' : 'No',
            $row['completed_by'],
            $row['completed_at'],
            $row['created_at']
        ));
    }

    fclose($output);
    exit;
}

/**
 * Save signature data
 */
add_action('wp_ajax_pj_save_signature', 'pj_save_signature_handler');
add_action('wp_ajax_nopriv_pj_save_signature', 'pj_save_signature_handler');

function pj_save_signature_handler() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_save_progress_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_signatures';

    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $client_name = isset($_POST['client_name']) ? sanitize_text_field($_POST['client_name']) : '';
    $client_signature = isset($_POST['client_signature']) ? sanitize_textarea_field($_POST['client_signature']) : '';

    $result = $wpdb->insert(
        $table,
        array(
            'project_id' => $project_id,
            'user_id' => $user_id,
            'client_name' => $client_name,
            'client_signature_data' => $client_signature
        ),
        array('%d', '%d', '%s', '%s')
    );

    if ($result !== false) {
        wp_send_json_success(array('message' => 'Signature saved successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to save signature'));
    }
}
