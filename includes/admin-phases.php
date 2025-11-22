<?php
if (!defined('ABSPATH')) exit;

/**
 * Admin Phases Management
 * Manage dynamic roadmap structure (phases, objectives, tasks)
 */

// ============================================================================
// CRUD FUNCTIONS - PHASES
// ============================================================================

/**
 * Get all phases for a project
 */
function pj_get_all_phases($project_id = 1) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE project_id = %d AND is_active = 1 ORDER BY sort_order ASC, phase_number ASC",
        $project_id
    ), ARRAY_A);
}

/**
 * Get single phase by ID
 */
function pj_get_phase($phase_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $phase_id
    ), ARRAY_A);
}

/**
 * Create new phase
 */
function pj_create_phase($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    $result = $wpdb->insert(
        $table,
        array(
            'project_id' => isset($data['project_id']) ? intval($data['project_id']) : 1,
            'phase_key' => sanitize_text_field($data['phase_key']),
            'phase_number' => intval($data['phase_number']),
            'phase_title' => sanitize_text_field($data['phase_title']),
            'phase_subtitle' => isset($data['phase_subtitle']) ? sanitize_textarea_field($data['phase_subtitle']) : '',
            'phase_description' => isset($data['phase_description']) ? sanitize_textarea_field($data['phase_description']) : '',
            'sort_order' => isset($data['sort_order']) ? intval($data['sort_order']) : 0,
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
        ),
        array('%d', '%s', '%d', '%s', '%s', '%s', '%d', '%d')
    );

    if ($result !== false) {
        return $wpdb->insert_id;
    }
    return false;
}

/**
 * Update phase
 */
function pj_update_phase($phase_id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    $update_data = array();
    $formats = array();

    if (isset($data['phase_key'])) {
        $update_data['phase_key'] = sanitize_text_field($data['phase_key']);
        $formats[] = '%s';
    }
    if (isset($data['phase_number'])) {
        $update_data['phase_number'] = intval($data['phase_number']);
        $formats[] = '%d';
    }
    if (isset($data['phase_title'])) {
        $update_data['phase_title'] = sanitize_text_field($data['phase_title']);
        $formats[] = '%s';
    }
    if (isset($data['phase_subtitle'])) {
        $update_data['phase_subtitle'] = sanitize_textarea_field($data['phase_subtitle']);
        $formats[] = '%s';
    }
    if (isset($data['phase_description'])) {
        $update_data['phase_description'] = sanitize_textarea_field($data['phase_description']);
        $formats[] = '%s';
    }
    if (isset($data['sort_order'])) {
        $update_data['sort_order'] = intval($data['sort_order']);
        $formats[] = '%d';
    }
    if (isset($data['is_active'])) {
        $update_data['is_active'] = intval($data['is_active']);
        $formats[] = '%d';
    }

    if (empty($update_data)) {
        return false;
    }

    return $wpdb->update(
        $table,
        $update_data,
        array('id' => $phase_id),
        $formats,
        array('%d')
    );
}

/**
 * Delete phase (soft delete - sets is_active = 0)
 */
function pj_delete_phase($phase_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    // Soft delete
    return $wpdb->update(
        $table,
        array('is_active' => 0),
        array('id' => $phase_id),
        array('%d'),
        array('%d')
    );
}

/**
 * Permanently delete phase (use with caution)
 */
function pj_hard_delete_phase($phase_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    // Also delete associated objectives and tasks
    pj_delete_objectives_by_phase($phase_id);

    return $wpdb->delete($table, array('id' => $phase_id), array('%d'));
}

// ============================================================================
// CRUD FUNCTIONS - OBJECTIVES
// ============================================================================

/**
 * Get all objectives for a phase
 */
function pj_get_objectives_by_phase($phase_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_objectives';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE phase_id = %d AND is_active = 1 ORDER BY sort_order ASC",
        $phase_id
    ), ARRAY_A);
}

/**
 * Get single objective by ID
 */
function pj_get_objective($objective_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_objectives';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $objective_id
    ), ARRAY_A);
}

/**
 * Create new objective
 */
function pj_create_objective($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_objectives';

    $result = $wpdb->insert(
        $table,
        array(
            'project_id' => isset($data['project_id']) ? intval($data['project_id']) : 1,
            'phase_id' => intval($data['phase_id']),
            'objective_key' => sanitize_text_field($data['objective_key']),
            'objective_title' => sanitize_text_field($data['objective_title']),
            'objective_subtitle' => isset($data['objective_subtitle']) ? sanitize_textarea_field($data['objective_subtitle']) : '',
            'sort_order' => isset($data['sort_order']) ? intval($data['sort_order']) : 0,
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
        ),
        array('%d', '%d', '%s', '%s', '%s', '%d', '%d')
    );

    if ($result !== false) {
        return $wpdb->insert_id;
    }
    return false;
}

/**
 * Update objective
 */
function pj_update_objective($objective_id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_objectives';

    $update_data = array();
    $formats = array();

    if (isset($data['phase_id'])) {
        $update_data['phase_id'] = intval($data['phase_id']);
        $formats[] = '%d';
    }
    if (isset($data['objective_key'])) {
        $update_data['objective_key'] = sanitize_text_field($data['objective_key']);
        $formats[] = '%s';
    }
    if (isset($data['objective_title'])) {
        $update_data['objective_title'] = sanitize_text_field($data['objective_title']);
        $formats[] = '%s';
    }
    if (isset($data['objective_subtitle'])) {
        $update_data['objective_subtitle'] = sanitize_textarea_field($data['objective_subtitle']);
        $formats[] = '%s';
    }
    if (isset($data['sort_order'])) {
        $update_data['sort_order'] = intval($data['sort_order']);
        $formats[] = '%d';
    }
    if (isset($data['is_active'])) {
        $update_data['is_active'] = intval($data['is_active']);
        $formats[] = '%d';
    }

    if (empty($update_data)) {
        return false;
    }

    return $wpdb->update(
        $table,
        $update_data,
        array('id' => $objective_id),
        $formats,
        array('%d')
    );
}

/**
 * Delete objective (soft delete)
 */
function pj_delete_objective($objective_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_objectives';

    // Soft delete
    return $wpdb->update(
        $table,
        array('is_active' => 0),
        array('id' => $objective_id),
        array('%d'),
        array('%d')
    );
}

/**
 * Delete all objectives for a phase
 */
function pj_delete_objectives_by_phase($phase_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_objectives';

    $objectives = pj_get_objectives_by_phase($phase_id);
    foreach ($objectives as $objective) {
        pj_delete_tasks_by_objective($objective['id']);
    }

    return $wpdb->update(
        $table,
        array('is_active' => 0),
        array('phase_id' => $phase_id),
        array('%d'),
        array('%d')
    );
}

// ============================================================================
// CRUD FUNCTIONS - TASKS
// ============================================================================

/**
 * Get all tasks for an objective
 */
function pj_get_tasks_by_objective($objective_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE objective_id = %d AND is_active = 1 ORDER BY sort_order ASC",
        $objective_id
    ), ARRAY_A);
}

/**
 * Get single task by ID
 */
function pj_get_task($task_db_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $task_db_id
    ), ARRAY_A);
}

/**
 * Get task by task_id (e.g., "phase1-A-1")
 */
function pj_get_task_by_task_id($task_id, $project_id = 1) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE task_id = %s AND project_id = %d",
        $task_id, $project_id
    ), ARRAY_A);
}

/**
 * Create new task
 */
function pj_create_task($data) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    $result = $wpdb->insert(
        $table,
        array(
            'project_id' => isset($data['project_id']) ? intval($data['project_id']) : 1,
            'phase_id' => intval($data['phase_id']),
            'objective_id' => intval($data['objective_id']),
            'task_id' => sanitize_text_field($data['task_id']),
            'task_text' => sanitize_textarea_field($data['task_text']),
            'task_details' => isset($data['task_details']) ? sanitize_textarea_field($data['task_details']) : '',
            'owner' => isset($data['owner']) ? sanitize_text_field($data['owner']) : 'both',
            'sort_order' => isset($data['sort_order']) ? intval($data['sort_order']) : 0,
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1
        ),
        array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%d')
    );

    if ($result !== false) {
        return $wpdb->insert_id;
    }
    return false;
}

/**
 * Update task
 */
function pj_update_task($task_db_id, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    $update_data = array();
    $formats = array();

    if (isset($data['phase_id'])) {
        $update_data['phase_id'] = intval($data['phase_id']);
        $formats[] = '%d';
    }
    if (isset($data['objective_id'])) {
        $update_data['objective_id'] = intval($data['objective_id']);
        $formats[] = '%d';
    }
    if (isset($data['task_id'])) {
        $update_data['task_id'] = sanitize_text_field($data['task_id']);
        $formats[] = '%s';
    }
    if (isset($data['task_text'])) {
        $update_data['task_text'] = sanitize_textarea_field($data['task_text']);
        $formats[] = '%s';
    }
    if (isset($data['task_details'])) {
        $update_data['task_details'] = sanitize_textarea_field($data['task_details']);
        $formats[] = '%s';
    }
    if (isset($data['owner'])) {
        $update_data['owner'] = sanitize_text_field($data['owner']);
        $formats[] = '%s';
    }
    if (isset($data['sort_order'])) {
        $update_data['sort_order'] = intval($data['sort_order']);
        $formats[] = '%d';
    }
    if (isset($data['is_active'])) {
        $update_data['is_active'] = intval($data['is_active']);
        $formats[] = '%d';
    }

    if (empty($update_data)) {
        return false;
    }

    return $wpdb->update(
        $table,
        $update_data,
        array('id' => $task_db_id),
        $formats,
        array('%d')
    );
}

/**
 * Delete task (soft delete)
 */
function pj_delete_task($task_db_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    // Soft delete
    return $wpdb->update(
        $table,
        array('is_active' => 0),
        array('id' => $task_db_id),
        array('%d'),
        array('%d')
    );
}

/**
 * Delete all tasks for an objective
 */
function pj_delete_tasks_by_objective($objective_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_tasks';

    return $wpdb->update(
        $table,
        array('is_active' => 0),
        array('objective_id' => $objective_id),
        array('%d'),
        array('%d')
    );
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================

/**
 * Auto-generate task_id from phase, objective, and task number
 */
function pj_auto_generate_task_id($phase_key, $objective_key, $task_number) {
    return sanitize_text_field($phase_key . '-' . $objective_key . '-' . $task_number);
}

/**
 * Reorder items (phases, objectives, or tasks)
 */
function pj_reorder_items($table_name, $items_order) {
    global $wpdb;
    $table = $wpdb->prefix . $table_name;

    foreach ($items_order as $index => $item_id) {
        $wpdb->update(
            $table,
            array('sort_order' => $index),
            array('id' => intval($item_id)),
            array('%d'),
            array('%d')
        );
    }

    return true;
}

/**
 * Get full roadmap structure (all phases with objectives and tasks)
 */
function pj_get_full_roadmap($project_id = 1) {
    $roadmap = array();
    $phases = pj_get_all_phases($project_id);

    foreach ($phases as $phase) {
        $phase['objectives'] = array();
        $objectives = pj_get_objectives_by_phase($phase['id']);

        foreach ($objectives as $objective) {
            $objective['tasks'] = pj_get_tasks_by_objective($objective['id']);
            $phase['objectives'][] = $objective;
        }

        $roadmap[] = $phase;
    }

    return $roadmap;
}

/**
 * Export roadmap to JSON
 */
function pj_export_roadmap_json($project_id = 1) {
    $roadmap = pj_get_full_roadmap($project_id);
    return json_encode($roadmap, JSON_PRETTY_PRINT);
}

/**
 * Check if roadmap exists in database
 */
function pj_roadmap_exists($project_id = 1) {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_phases';

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE project_id = %d AND is_active = 1",
        $project_id
    ));

    return $count > 0;
}

// ============================================================================
// AJAX HANDLERS
// ============================================================================

/**
 * AJAX: Save phase
 */
add_action('wp_ajax_pj_save_phase', 'pj_save_phase_handler');
function pj_save_phase_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $phase_id = isset($_POST['phase_id']) ? intval($_POST['phase_id']) : 0;
    $data = array(
        'project_id' => isset($_POST['project_id']) ? intval($_POST['project_id']) : 1,
        'phase_key' => $_POST['phase_key'],
        'phase_number' => $_POST['phase_number'],
        'phase_title' => $_POST['phase_title'],
        'phase_subtitle' => isset($_POST['phase_subtitle']) ? $_POST['phase_subtitle'] : '',
        'phase_description' => isset($_POST['phase_description']) ? $_POST['phase_description'] : '',
        'sort_order' => isset($_POST['sort_order']) ? $_POST['sort_order'] : 0
    );

    if ($phase_id > 0) {
        // Update existing phase
        $result = pj_update_phase($phase_id, $data);
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Phase updated', 'phase_id' => $phase_id));
        }
    } else {
        // Create new phase
        $new_id = pj_create_phase($data);
        if ($new_id) {
            wp_send_json_success(array('message' => 'Phase created', 'phase_id' => $new_id));
        }
    }

    wp_send_json_error(array('message' => 'Failed to save phase'));
}

/**
 * AJAX: Delete phase
 */
add_action('wp_ajax_pj_delete_phase', 'pj_delete_phase_handler');
function pj_delete_phase_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $phase_id = isset($_POST['phase_id']) ? intval($_POST['phase_id']) : 0;

    if ($phase_id > 0) {
        $result = pj_delete_phase($phase_id);
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Phase deleted'));
        }
    }

    wp_send_json_error(array('message' => 'Failed to delete phase'));
}

/**
 * AJAX: Save objective
 */
add_action('wp_ajax_pj_save_objective', 'pj_save_objective_handler');
function pj_save_objective_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $objective_id = isset($_POST['objective_id']) ? intval($_POST['objective_id']) : 0;
    $data = array(
        'project_id' => isset($_POST['project_id']) ? intval($_POST['project_id']) : 1,
        'phase_id' => $_POST['phase_id'],
        'objective_key' => $_POST['objective_key'],
        'objective_title' => $_POST['objective_title'],
        'objective_subtitle' => isset($_POST['objective_subtitle']) ? $_POST['objective_subtitle'] : '',
        'sort_order' => isset($_POST['sort_order']) ? $_POST['sort_order'] : 0
    );

    if ($objective_id > 0) {
        $result = pj_update_objective($objective_id, $data);
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Objective updated', 'objective_id' => $objective_id));
        }
    } else {
        $new_id = pj_create_objective($data);
        if ($new_id) {
            wp_send_json_success(array('message' => 'Objective created', 'objective_id' => $new_id));
        }
    }

    wp_send_json_error(array('message' => 'Failed to save objective'));
}

/**
 * AJAX: Delete objective
 */
add_action('wp_ajax_pj_delete_objective', 'pj_delete_objective_handler');
function pj_delete_objective_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $objective_id = isset($_POST['objective_id']) ? intval($_POST['objective_id']) : 0;

    if ($objective_id > 0) {
        $result = pj_delete_objective($objective_id);
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Objective deleted'));
        }
    }

    wp_send_json_error(array('message' => 'Failed to delete objective'));
}

/**
 * AJAX: Save task
 */
add_action('wp_ajax_pj_save_task', 'pj_save_task_handler');
function pj_save_task_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $task_db_id = isset($_POST['task_db_id']) ? intval($_POST['task_db_id']) : 0;
    $data = array(
        'project_id' => isset($_POST['project_id']) ? intval($_POST['project_id']) : 1,
        'phase_id' => isset($_POST['phase_id']) ? intval($_POST['phase_id']) : 0,
        'objective_id' => isset($_POST['objective_id']) ? intval($_POST['objective_id']) : 0,
        'task_id' => isset($_POST['task_id']) ? sanitize_text_field(wp_unslash($_POST['task_id'])) : '',
        'task_text' => isset($_POST['task_text']) ? sanitize_textarea_field(wp_unslash($_POST['task_text'])) : '',
        'task_details' => isset($_POST['task_details']) ? sanitize_textarea_field(wp_unslash($_POST['task_details'])) : '',
        'owner' => isset($_POST['owner']) ? sanitize_text_field(wp_unslash($_POST['owner'])) : 'both',
        'sort_order' => isset($_POST['sort_order']) ? intval($_POST['sort_order']) : 0
    );

    if ($task_db_id > 0) {
        $result = pj_update_task($task_db_id, $data);
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Task updated', 'task_db_id' => $task_db_id));
        }
    } else {
        $new_id = pj_create_task($data);
        if ($new_id) {
            wp_send_json_success(array('message' => 'Task created', 'task_db_id' => $new_id));
        }
    }

    wp_send_json_error(array('message' => 'Failed to save task'));
}

/**
 * AJAX: Delete task
 */
add_action('wp_ajax_pj_delete_task', 'pj_delete_task_handler');
function pj_delete_task_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $task_db_id = isset($_POST['task_db_id']) ? intval($_POST['task_db_id']) : 0;

    if ($task_db_id > 0) {
        $result = pj_delete_task($task_db_id);
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Task deleted'));
        }
    }

    wp_send_json_error(array('message' => 'Failed to delete task'));
}

/**
 * AJAX: Reorder items
 */
add_action('wp_ajax_pj_reorder_items', 'pj_reorder_items_handler');
function pj_reorder_items_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $table = sanitize_text_field($_POST['table']);
    $items = isset($_POST['items']) ? $_POST['items'] : array();

    if (empty($items)) {
        wp_send_json_error(array('message' => 'No items to reorder'));
        return;
    }

    $result = pj_reorder_items($table, $items);
    if ($result) {
        wp_send_json_success(array('message' => 'Items reordered'));
    }

    wp_send_json_error(array('message' => 'Failed to reorder items'));
}

/**
 * AJAX: Get full roadmap
 */
add_action('wp_ajax_pj_get_roadmap', 'pj_get_roadmap_handler');
function pj_get_roadmap_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 1;
    $roadmap = pj_get_full_roadmap($project_id);

    wp_send_json_success(array('roadmap' => $roadmap));
}

// ============================================================================
// DATA MIGRATION - Hardcoded Roadmap → Database
// ============================================================================

/**
 * Import hardcoded roadmap from roadmap-display.php into database
 * This is a one-time migration to preserve existing roadmap structure
 */
function pj_import_hardcoded_roadmap($project_id = 1) {
    // Check if roadmap already exists
    if (pj_roadmap_exists($project_id)) {
        return array(
            'success' => false,
            'message' => 'Roadmap already exists in database. Skipping import to prevent duplicates.'
        );
    }

    $stats = array(
        'phases' => 0,
        'objectives' => 0,
        'tasks' => 0
    );

    // Define the hardcoded roadmap structure
    $hardcoded_roadmap = array(
        // PHASE 1
        array(
            'phase_key' => 'phase1',
            'phase_number' => 1,
            'phase_title' => 'Phase 1: Days 1–30',
            'phase_subtitle' => 'Discovery, Foundations & Communication',
            'phase_description' => 'Align on brand, client journey, offers, and communication rhythm. Build core intake assets and project roadmap. Begin foundational website and system setup.',
            'objectives' => array(
                array(
                    'objective_key' => 'A',
                    'objective_title' => 'Communication & Working Agreement',
                    'objective_subtitle' => 'Establish a predictable way of working together',
                    'tasks' => array(
                        array('text' => 'Decide preferred communication style (scheduled calls vs. flexible check-ins)', 'owner' => 'client'),
                        array('text' => 'Confirm primary communication channels (e.g. Zoom, email, text, shared folder, etc.)', 'owner' => 'both'),
                        array('text' => 'Agree on basic response time expectations (e.g. 24–48 hours on weekdays)', 'owner' => 'both'),
                        array('text' => 'Create and approve a simple Working Relationship / Accountability Agreement', 'owner' => 'both', 'details' => 'Consultant drafts, Client reviews & approves')
                    )
                ),
                array(
                    'objective_key' => 'B',
                    'objective_title' => 'Client Journey & Coaching Structure',
                    'objective_subtitle' => 'Define how Sojourn Coaching clients move from first contact to transformation',
                    'tasks' => array(
                        array('text' => 'Map the 8-week coaching arc (start → middle → completion)', 'owner' => 'both'),
                        array('text' => 'Define core stages of the client journey (e.g. Discovery → Intake → First Sessions → Midpoint Check-in → Completion & Next Steps)', 'owner' => 'both'),
                        array('text' => 'Outline first consultation structure (intake call flow, key questions)', 'owner' => 'both', 'details' => 'Client leads, Consultant supports'),
                        array('text' => 'Identify 2–3 primary outcomes clients should achieve by the end of 8 weeks', 'owner' => 'both', 'details' => 'Client identifies, Consultant refines wording'),
                        array('text' => 'Draft a simple visual coaching roadmap/diagram (even rough at first)', 'owner' => 'consultant', 'details' => 'Consultant drafts, Client reviews')
                    )
                ),
                array(
                    'objective_key' => 'C',
                    'objective_title' => 'Intake PDF & Client Onboarding',
                    'objective_subtitle' => 'Create a branded intake experience to use once clients sign up',
                    'tasks' => array(
                        array('text' => 'Client drafts raw intake questions/content (checkboxes, comment fields, reflection prompts)', 'owner' => 'client'),
                        array('text' => 'Consultant reviews and structures questions into sections (e.g. Background, Current Challenges, Goals, Boundaries)', 'owner' => 'consultant'),
                        array('text' => 'Consultant designs branded intake PDF (logo, fonts, colors, layout)', 'owner' => 'consultant'),
                        array('text' => 'Client reviews intake PDF and requests any revisions (up to agreed rounds)', 'owner' => 'client'),
                        array('text' => 'Final intake PDF saved in shared folder and linked in onboarding flow (email/site)', 'owner' => 'consultant')
                    )
                ),
                array(
                    'objective_key' => 'D',
                    'objective_title' => 'Website & Tech Foundations – Initial Setup',
                    'objective_subtitle' => 'Stand up the core environment for Sojourn Coaching\'s online presence',
                    'tasks' => array(
                        array('text' => 'Confirm final platform for main site (WordPress install location)', 'owner' => 'both', 'details' => 'Consultant, Client approval'),
                        array('text' => 'Confirm current domain and where it should point (e.g. sojourn-coaching.com → new host)', 'owner' => 'both', 'details' => 'Consultant, Client approval'),
                        array('text' => 'Point domain to new hosting and verify propagation', 'owner' => 'consultant'),
                        array('text' => 'Install and configure base WordPress theme and structure (Home, About, Work with Kim, Contact/Book, etc.)', 'owner' => 'consultant'),
                        array('text' => 'Create shared folder (e.g. Google Drive) for assets: photos, logo, headshots, brand elements', 'owner' => 'both', 'details' => 'Consultant creates, Client uploads content'),
                        array('text' => 'Client provides initial copy: About bio, why Sojourn, a few testimonials if available', 'owner' => 'client')
                    )
                ),
                array(
                    'objective_key' => 'E',
                    'objective_title' => '30/60/90 Master Roadmap Creation',
                    'objective_subtitle' => 'Turn this plan into a living tracker for both parties',
                    'tasks' => array(
                        array('text' => 'Consultant converts this document into a visual 30/60/90 roadmap (phases, milestones, checkboxes)', 'owner' => 'consultant'),
                        array('text' => 'Roadmap uploaded to a private online space / dashboard for Client access', 'owner' => 'consultant'),
                        array('text' => 'Walkthrough of the roadmap on a call to confirm priorities and expectations', 'owner' => 'both')
                    )
                )
            )
        ),
        // PHASE 2
        array(
            'phase_key' => 'phase2',
            'phase_number' => 2,
            'phase_title' => 'Phase 2: Days 31–60',
            'phase_subtitle' => 'Website Build, Booking, Funnel & AI Support',
            'phase_description' => 'Launch a professional website with integrated booking. Set up a simple but effective lead funnel and welcome emails. Introduce AI tools into Kim\'s workflow in a supportive, non-overwhelming way.',
            'objectives' => array(
                array(
                    'objective_key' => 'A',
                    'objective_title' => 'Website Buildout (Core Pages Live)',
                    'objective_subtitle' => 'Make Sojourn Coaching publicly presentable and aligned with the brand',
                    'tasks' => array(
                        array('text' => 'Finalize site navigation (e.g. Home, Work with Kim, About, Testimonials, Book a Clarity Call, Resources/Videos, etc.)', 'owner' => 'both'),
                        array('text' => 'Build and style Home page with clear messaging and CTA (e.g. "Book a Clarity Call")', 'owner' => 'consultant'),
                        array('text' => 'Build Work with Kim page outlining offers, 8-week journey, and who it\'s for', 'owner' => 'both', 'details' => 'Consultant drafts, Client approves/refines wording'),
                        array('text' => 'Build About page with story, values, and credibility markers', 'owner' => 'both', 'details' => 'Consultant drafts, Client provides/refines bio and story'),
                        array('text' => 'Create Book a Clarity Call page (or modal) linking to booking system', 'owner' => 'consultant'),
                        array('text' => 'Connect intake PDF into the onboarding flow where appropriate (e.g. after booking or post-enrollment)', 'owner' => 'consultant')
                    )
                ),
                array(
                    'objective_key' => 'B',
                    'objective_title' => 'Booking & Scheduling System',
                    'objective_subtitle' => 'Give prospects and clients a simple, clear way to book time with Kim',
                    'tasks' => array(
                        array('text' => 'Choose scheduler platform (e.g. Calendly, SimplyBook, or preferred system)', 'owner' => 'both'),
                        array('text' => 'Set up availability blocks for Clarity Calls and Coaching Sessions', 'owner' => 'both', 'details' => 'Client, Consultant guidance'),
                        array('text' => 'Enable automated reminders and timezone handling', 'owner' => 'consultant'),
                        array('text' => 'Embed booking widget or link into "Book a Clarity Call" page and other strategic places on site', 'owner' => 'consultant'),
                        array('text' => 'Test end-to-end booking flow (visitor → booking → confirmation emails)', 'owner' => 'both')
                    )
                ),
                array(
                    'objective_key' => 'C',
                    'objective_title' => 'Funnel & Email Automation',
                    'objective_subtitle' => 'Capture interested leads and nurture them into conversations or clients',
                    'tasks' => array(
                        array('text' => 'Decide on lead magnet concept (e.g. mini-guide, checklist, reflection, Clarity & Confidence Index, short video)', 'owner' => 'both', 'details' => 'Client, Consultant supports format'),
                        array('text' => 'Draft content for the lead magnet (or outline for Consultant to polish)', 'owner' => 'both', 'details' => 'Client, Consultant refines'),
                        array('text' => 'Create Landing Page for lead magnet with clear promise and opt-in form', 'owner' => 'consultant'),
                        array('text' => 'Choose and configure email platform (e.g. MailerLite, ConvertKit, or similar)', 'owner' => 'both', 'details' => 'Consultant, Client approvals'),
                        array('text' => 'Build 3–5 email Welcome Sequence triggered on opt-in: 1) Welcome & story, 2) Value/insight/teaching, 3) Invitation to Clarity Call, (Optional) 4 & 5: Case study, FAQs, soft offer', 'owner' => 'both', 'details' => 'Consultant drafts, Client reviews & tweaks'),
                        array('text' => 'Integrate opt-in form with email platform and test the full flow', 'owner' => 'consultant')
                    )
                ),
                array(
                    'objective_key' => 'D',
                    'objective_title' => 'AI Support – Jumpstart & Workflow Integration',
                    'objective_subtitle' => 'Help Kim confidently use AI tools to support content and communication',
                    'tasks' => array(
                        array('text' => 'Identify core AI tools to use (e.g. ChatGPT for writing, image tools, avatar video intro if desired)', 'owner' => 'consultant'),
                        array('text' => 'Provide a small Prompt Pack tailored to Sojourn Coaching for: Client messaging (bios, intros, DMs), Content ideas (blogs, social posts, email subject lines), Simple scripting for short videos (reels/shorts)', 'owner' => 'consultant'),
                        array('text' => 'Conduct a short AI onboarding session (screen share) to walk through how Kim can safely and simply use these tools', 'owner' => 'both', 'details' => 'Consultant leads, Client participates'),
                        array('text' => 'Save prompts and workflows in a reference doc Kim can revisit', 'owner' => 'consultant')
                    )
                ),
                array(
                    'objective_key' => 'E',
                    'objective_title' => 'Video & Content Planning',
                    'objective_subtitle' => 'Lay groundwork for a library of content Kim can grow over time',
                    'tasks' => array(
                        array('text' => 'Decide whether to start with a private video area or go public (e.g. YouTube later)', 'owner' => 'both', 'details' => 'Client, Consultant input'),
                        array('text' => 'Outline 3–5 starter video topics (stories, teachings, reflections)', 'owner' => 'both', 'details' => 'Client brainstorming, Consultant structures'),
                        array('text' => 'Choose where early videos will live (site, unlisted links, private library, etc.)', 'owner' => 'both'),
                        array('text' => 'Document a simple repeatable flow: idea → outline → record → upload → publish/link on site', 'owner' => 'consultant')
                    )
                )
            )
        ),
        // PHASE 3
        array(
            'phase_key' => 'phase3',
            'phase_number' => 3,
            'phase_title' => 'Phase 3: Days 61–90',
            'phase_subtitle' => 'Refinement, Launch, and Momentum',
            'phase_description' => 'Refine everything based on real feedback. Prepare for public launch or re-launch of Sojourn Coaching. Solidify content rhythm and client experience.',
            'objectives' => array(
                array(
                    'objective_key' => 'A',
                    'objective_title' => 'Website & Funnel Refinement',
                    'objective_subtitle' => 'Polish the experience now that the basics are live',
                    'tasks' => array(
                        array('text' => 'Review analytics and feedback from early visitors (if available)', 'owner' => 'both', 'details' => 'Consultant gathers, Both review'),
                        array('text' => 'Adjust copy and layout on key pages (Home, Work with Kim, Book a Call) for clarity and conversions', 'owner' => 'both', 'details' => 'Consultant, Client approvals'),
                        array('text' => 'Refine any visual elements that feel off-brand or unclear', 'owner' => 'consultant'),
                        array('text' => 'Confirm all links, forms, and booking flows function reliably', 'owner' => 'consultant')
                    )
                ),
                array(
                    'objective_key' => 'B',
                    'objective_title' => 'Coaching System & Client Journey Finalization',
                    'objective_subtitle' => 'Lock in a repeatable and confident client experience',
                    'tasks' => array(
                        array('text' => 'Finalize 8-week coaching roadmap graphic/diagram and add to website and/or onboarding material', 'owner' => 'both', 'details' => 'Consultant creates, Client approves'),
                        array('text' => 'Standardize First Session format (how sessions begin, what\'s reviewed, expectations set)', 'owner' => 'both', 'details' => 'Client, Consultant documents'),
                        array('text' => 'Define simple check-in points (e.g. Week 4 midpoint review, final session recap and next steps)', 'owner' => 'both'),
                        array('text' => 'Create a short "How to Get the Most Out of Coaching with Kim" guide (text or video) for new clients', 'owner' => 'both', 'details' => 'Consultant drafts, Client finalizes tone')
                    )
                ),
                array(
                    'objective_key' => 'C',
                    'objective_title' => 'Social Presence & Launch Support',
                    'objective_subtitle' => 'Create a lightweight but aligned presence on at least one platform',
                    'tasks' => array(
                        array('text' => 'Decide primary platform (e.g. Instagram or LinkedIn) to focus on first', 'owner' => 'both', 'details' => 'Client, Consultant input'),
                        array('text' => 'Set or refine profile (photo, bio, link to Clarity Call/lead magnet)', 'owner' => 'both', 'details' => 'Consultant drafts, Client approves'),
                        array('text' => 'Provide 8–10 simple content prompts aligned to Sojourn Coaching themes', 'owner' => 'consultant'),
                        array('text' => 'Map out a realistic posting rhythm (e.g. 1–2x per week) that feels sustainable', 'owner' => 'both'),
                        array('text' => 'Announce or re-announce Sojourn Coaching publicly with a short, clear launch message', 'owner' => 'both', 'details' => 'Client, Consultant can help draft')
                    )
                ),
                array(
                    'objective_key' => 'D',
                    'objective_title' => 'Launch Review & Ongoing Rhythm',
                    'objective_subtitle' => 'Close the 90-day buildout with clarity on what\'s next',
                    'tasks' => array(
                        array('text' => 'Conduct a 90-day Launch Review Session (what\'s working, what feels heavy, what feels exciting)', 'owner' => 'both'),
                        array('text' => 'Decide what should become ongoing habits (weekly review, content creation block, system check)', 'owner' => 'both', 'details' => 'Client, Consultant suggests structure'),
                        array('text' => 'Identify 2–3 next-phase improvements (e.g. refine packages, add group program, build more content, expand video presence)', 'owner' => 'both'),
                        array('text' => 'Update the roadmap or create a follow-on 90-day plan based on what emerges', 'owner' => 'both', 'details' => 'Consultant drafts, Client approves')
                    )
                )
            )
        )
    );

    // Import the roadmap
    foreach ($hardcoded_roadmap as $phase_index => $phase_data) {
        // Create phase
        $phase_id = pj_create_phase(array(
            'project_id' => $project_id,
            'phase_key' => $phase_data['phase_key'],
            'phase_number' => $phase_data['phase_number'],
            'phase_title' => $phase_data['phase_title'],
            'phase_subtitle' => $phase_data['phase_subtitle'],
            'phase_description' => $phase_data['phase_description'],
            'sort_order' => $phase_index
        ));

        if ($phase_id) {
            $stats['phases']++;

            // Create objectives for this phase
            foreach ($phase_data['objectives'] as $obj_index => $objective_data) {
                $objective_id = pj_create_objective(array(
                    'project_id' => $project_id,
                    'phase_id' => $phase_id,
                    'objective_key' => $objective_data['objective_key'],
                    'objective_title' => $objective_data['objective_title'],
                    'objective_subtitle' => $objective_data['objective_subtitle'],
                    'sort_order' => $obj_index
                ));

                if ($objective_id) {
                    $stats['objectives']++;

                    // Create tasks for this objective
                    foreach ($objective_data['tasks'] as $task_index => $task_data) {
                        $task_number = $task_index + 1;
                        $task_id = pj_auto_generate_task_id(
                            $phase_data['phase_key'],
                            $objective_data['objective_key'],
                            $task_number
                        );

                        $created_task_id = pj_create_task(array(
                            'project_id' => $project_id,
                            'phase_id' => $phase_id,
                            'objective_id' => $objective_id,
                            'task_id' => $task_id,
                            'task_text' => $task_data['text'],
                            'task_details' => isset($task_data['details']) ? $task_data['details'] : '',
                            'owner' => isset($task_data['owner']) ? $task_data['owner'] : 'both',
                            'sort_order' => $task_index
                        ));

                        if ($created_task_id) {
                            $stats['tasks']++;
                        }
                    }
                }
            }
        }
    }

    return array(
        'success' => true,
        'message' => 'Roadmap imported successfully',
        'stats' => $stats
    );
}

/**
 * AJAX: Import hardcoded roadmap
 */
add_action('wp_ajax_pj_import_roadmap', 'pj_import_roadmap_handler');
function pj_import_roadmap_handler() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized'));
        return;
    }

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pj_admin_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed'));
        return;
    }

    $project_id = isset($_POST['project_id']) ? intval($_POST['project_id']) : 1;
    $result = pj_import_hardcoded_roadmap($project_id);

    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result);
    }
}

/**
 * Admin page callback for Project Phases management
 */
function pj_admin_phases_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $project_id = 1; // Default project ID
    $roadmap_exists = pj_roadmap_exists($project_id);
    $roadmap = pj_get_full_roadmap($project_id);
    ?>
    <div class="wrap pj-phases-admin">
        <h1>Project Phases Management</h1>

        <div class="pj-admin-header">
            <p class="description">
                Manage the phases, objectives, and tasks for your 30/60/90 project roadmap.
                Changes made here will immediately affect the frontend roadmap display.
            </p>

            <?php if (!$roadmap_exists): ?>
                <div class="notice notice-warning">
                    <p><strong>No roadmap found in database.</strong> Click the button below to import the hardcoded roadmap.</p>
                </div>
                <button type="button" class="button button-primary" id="pj-import-roadmap">
                    Import Hardcoded Roadmap
                </button>
            <?php else: ?>
                <div class="pj-actions">
                    <button type="button" class="button button-primary" id="pj-add-phase">
                        + Add New Phase
                    </button>
                    <button type="button" class="button" id="pj-export-json">
                        Export as JSON
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($roadmap_exists && !empty($roadmap)): ?>
            <div class="pj-phases-container">
                <?php foreach ($roadmap as $phase): ?>
                    <div class="pj-phase-card" data-phase-id="<?php echo esc_attr($phase['id']); ?>">
                        <div class="pj-phase-header">
                            <span class="pj-drag-handle dashicons dashicons-menu"></span>
                            <div class="pj-phase-title-area">
                                <h2 class="pj-phase-title" contenteditable="false" data-field="phase_title">
                                    <?php echo esc_html($phase['phase_title']); ?>
                                </h2>
                                <p class="pj-phase-subtitle" contenteditable="false" data-field="phase_subtitle">
                                    <?php echo esc_html($phase['phase_subtitle'] ?? ''); ?>
                                </p>
                            </div>
                            <div class="pj-phase-actions">
                                <button class="button button-small pj-edit-phase" title="Edit Phase">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                                <button class="button button-small pj-toggle-phase" title="Collapse/Expand">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                                <button class="button button-small pj-delete-phase" title="Delete Phase">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </div>
                        </div>

                        <div class="pj-phase-content">
                            <div class="pj-phase-description" contenteditable="false" data-field="phase_description">
                                <?php echo esc_html($phase['phase_description'] ?? ''); ?>
                            </div>

                            <button type="button" class="button button-secondary pj-add-objective" data-phase-id="<?php echo esc_attr($phase['id']); ?>">
                                + Add Objective
                            </button>

                            <div class="pj-objectives-container">
                                <?php if (!empty($phase['objectives'])): ?>
                                    <?php foreach ($phase['objectives'] as $objective): ?>
                                        <div class="pj-objective-card" data-objective-id="<?php echo esc_attr($objective['id']); ?>">
                                            <div class="pj-objective-header">
                                                <span class="pj-drag-handle dashicons dashicons-menu"></span>
                                                <h3 class="pj-objective-title" contenteditable="false" data-field="objective_title">
                                                    <?php echo esc_html($objective['objective_title']); ?>
                                                </h3>
                                                <div class="pj-objective-actions">
                                                    <button class="button button-small pj-edit-objective" title="Edit Objective">
                                                        <span class="dashicons dashicons-edit"></span>
                                                    </button>
                                                    <button class="button button-small pj-delete-objective" title="Delete Objective">
                                                        <span class="dashicons dashicons-trash"></span>
                                                    </button>
                                                </div>
                                            </div>

                                            <p class="pj-objective-subtitle" contenteditable="false" data-field="objective_subtitle">
                                                <?php echo esc_html($objective['objective_subtitle'] ?? ''); ?>
                                            </p>

                                            <button type="button" class="button button-secondary pj-add-task" data-objective-id="<?php echo esc_attr($objective['id']); ?>">
                                                + Add Task
                                            </button>

                                            <div class="pj-tasks-container">
                                                <?php if (!empty($objective['tasks'])): ?>
                                                    <?php foreach ($objective['tasks'] as $task): ?>
                                                        <div class="pj-task-item" data-task-id="<?php echo esc_attr($task['id']); ?>">
                                                            <span class="pj-drag-handle dashicons dashicons-menu"></span>
                                                            <div class="pj-task-content">
                                                                <span class="pj-task-id"><?php echo esc_html($task['task_id']); ?></span>
                                                                <span class="pj-task-text" contenteditable="false" data-field="task_text">
                                                                    <?php echo esc_html($task['task_text']); ?>
                                                                </span>
                                                                <?php if (!empty($task['owner'])): ?>
                                                                    <span class="pj-task-owner" contenteditable="false" data-field="owner">
                                                                        (<?php echo esc_html($task['owner']); ?>)
                                                                    </span>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="pj-task-actions">
                                                                <button class="button button-small pj-edit-task" title="Edit Task">
                                                                    <span class="dashicons dashicons-edit"></span>
                                                                </button>
                                                                <button class="button button-small pj-delete-task" title="Delete Task">
                                                                    <span class="dashicons dashicons-trash"></span>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Hidden template for new phase -->
        <script type="text/template" id="pj-phase-template">
            <div class="pj-phase-card" data-phase-id="{{id}}">
                <div class="pj-phase-header">
                    <span class="pj-drag-handle dashicons dashicons-menu"></span>
                    <div class="pj-phase-title-area">
                        <h2 class="pj-phase-title" contenteditable="false" data-field="phase_title">{{phase_title}}</h2>
                        <p class="pj-phase-subtitle" contenteditable="false" data-field="phase_subtitle">{{phase_subtitle}}</p>
                    </div>
                    <div class="pj-phase-actions">
                        <button class="button button-small pj-edit-phase" title="Edit Phase">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button class="button button-small pj-toggle-phase" title="Collapse/Expand">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </button>
                        <button class="button button-small pj-delete-phase" title="Delete Phase">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                </div>
                <div class="pj-phase-content">
                    <div class="pj-phase-description" contenteditable="false" data-field="phase_description">{{phase_description}}</div>
                    <button type="button" class="button button-secondary pj-add-objective" data-phase-id="{{id}}">+ Add Objective</button>
                    <div class="pj-objectives-container"></div>
                </div>
            </div>
        </script>

        <div id="pj-loading-overlay" style="display:none;">
            <div class="pj-spinner"></div>
        </div>
    </div>
    <?php
}
