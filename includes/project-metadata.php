<?php
/**
 * Project Metadata Management
 * Handles storage and retrieval of project-level settings like roadmap title, subtitle, purpose points
 */

if (!defined('ABSPATH')) exit;

/**
 * Get a single project metadata value
 *
 * @param int $project_id The project ID
 * @param string $meta_key The metadata key
 * @param mixed $default Default value if not found
 * @return mixed The metadata value or default
 */
function pj_get_project_meta($project_id, $meta_key, $default = '') {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_metadata';

    $value = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM $table WHERE project_id = %d AND meta_key = %s",
        $project_id,
        $meta_key
    ));

    if ($value === null) {
        return $default;
    }

    // Try to decode JSON values
    $decoded = json_decode($value, true);
    return $decoded !== null ? $decoded : $value;
}

/**
 * Update or insert a project metadata value
 *
 * @param int $project_id The project ID
 * @param string $meta_key The metadata key
 * @param mixed $meta_value The metadata value (will be JSON encoded if array)
 * @return bool Success status
 */
function pj_update_project_meta($project_id, $meta_key, $meta_value) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_metadata';

    // Encode arrays as JSON
    if (is_array($meta_value)) {
        $meta_value = json_encode($meta_value);
    }

    // Check if exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table WHERE project_id = %d AND meta_key = %s",
        $project_id,
        $meta_key
    ));

    if ($exists) {
        // Update existing
        return $wpdb->update(
            $table,
            array('meta_value' => $meta_value),
            array('project_id' => $project_id, 'meta_key' => $meta_key),
            array('%s'),
            array('%d', '%s')
        ) !== false;
    } else {
        // Insert new
        return $wpdb->insert(
            $table,
            array(
                'project_id' => $project_id,
                'meta_key' => $meta_key,
                'meta_value' => $meta_value
            ),
            array('%d', '%s', '%s')
        ) !== false;
    }
}

/**
 * Delete a project metadata value
 *
 * @param int $project_id The project ID
 * @param string $meta_key The metadata key
 * @return bool Success status
 */
function pj_delete_project_meta($project_id, $meta_key) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_metadata';

    return $wpdb->delete(
        $table,
        array('project_id' => $project_id, 'meta_key' => $meta_key),
        array('%d', '%s')
    ) !== false;
}

/**
 * Get all project metadata as associative array
 *
 * @param int $project_id The project ID
 * @return array Associative array of meta_key => meta_value
 */
function pj_get_all_project_meta($project_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_metadata';

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT meta_key, meta_value FROM $table WHERE project_id = %d",
        $project_id
    ), ARRAY_A);

    $metadata = array();
    foreach ($results as $row) {
        $key = $row['meta_key'];
        $value = $row['meta_value'];

        // Try to decode JSON
        $decoded = json_decode($value, true);
        $metadata[$key] = $decoded !== null ? $decoded : $value;
    }

    return $metadata;
}

/**
 * Check if project has any metadata set
 *
 * @param int $project_id The project ID
 * @return bool True if metadata exists
 */
function pj_project_meta_exists($project_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_metadata';

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE project_id = %d",
        $project_id
    ));

    return $count > 0;
}

/**
 * Set default project metadata (migration from hardcoded values)
 *
 * @param int $project_id The project ID
 * @return array Result with success status and message
 */
function pj_set_default_project_meta($project_id = 1) {
    // Check if already set
    if (pj_project_meta_exists($project_id)) {
        return array(
            'success' => false,
            'message' => 'Project metadata already exists. Skipping defaults.'
        );
    }

    // Default metadata values extracted from hardcoded roadmap-display.php
    $defaults = array(
        'roadmap_title' => '30/60/90 Project Roadmap',
        'roadmap_subtitle' => 'Moving Sojourn Coaching from idea and uncertainty into a confident, operational, and client-ready coaching practice',
        'purpose_intro' => 'By the end of 90 days, you will have:',
        'purpose_points' => array(
            'A clear client journey and offers',
            'A professional website and booking system',
            'An intake and onboarding flow',
            'Simple funnels and email automations',
            'A growing library of content and video assets',
            'A sustainable communication rhythm between Client and Consultant'
        ),
        'timeline_intro' => 'The project unfolds in strategic phases',
        'show_timeline_visual' => 'yes'
    );

    $count = 0;
    foreach ($defaults as $key => $value) {
        if (pj_update_project_meta($project_id, $key, $value)) {
            $count++;
        }
    }

    return array(
        'success' => true,
        'message' => 'Default project metadata set successfully.',
        'count' => $count
    );
}

/**
 * AJAX: Save project metadata (single or bulk)
 */
add_action('wp_ajax_pj_save_project_metadata', 'pj_save_project_metadata_handler');
function pj_save_project_metadata_handler() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $metadata = isset($_POST['metadata']) ? $_POST['metadata'] : array();

    if (empty($metadata)) {
        wp_send_json_error(array('message' => 'No metadata provided'));
        return;
    }

    $count = 0;
    foreach ($metadata as $key => $value) {
        // Sanitize based on type
        if ($key === 'purpose_points' && is_array($value)) {
            // Sanitize each array element
            $value = array_map('sanitize_text_field', $value);
        } else if (is_string($value)) {
            $value = sanitize_textarea_field($value);
        }

        if (pj_update_project_meta($project_id, $key, $value)) {
            $count++;
        }
    }

    wp_send_json_success(array(
        'message' => 'Project metadata saved successfully',
        'count' => $count
    ));
}

/**
 * AJAX: Get all project metadata
 */
add_action('wp_ajax_pj_get_project_metadata', 'pj_get_project_metadata_handler');
function pj_get_project_metadata_handler() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $metadata = pj_get_all_project_meta($project_id);

    wp_send_json_success(array('metadata' => $metadata));
}

/**
 * AJAX: Initialize default metadata
 */
add_action('wp_ajax_pj_init_default_metadata', 'pj_init_default_metadata_handler');
function pj_init_default_metadata_handler() {
    // Security checks
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $result = pj_set_default_project_meta($project_id);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}
