<?php
/**
 * Plugin Name: 30-60-90 Project Journey
 * Description: Track 30/60/90 day roadmap progress with interactive checkboxes, save to database, and generate professional PDF reports
 * Version: 1.0.0
 * Author: PrometheanLink
 * Author URI: https://prometheanlink.com
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define plugin constants
define('PJ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PJ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PJ_VERSION', '1.0.0');

// Include required files
include_once PJ_PLUGIN_DIR . 'includes/form-handler.php';
include_once PJ_PLUGIN_DIR . 'includes/pdf-generator.php';
include_once PJ_PLUGIN_DIR . 'includes/admin-page.php';
include_once PJ_PLUGIN_DIR . 'includes/task-notes.php';
include_once PJ_PLUGIN_DIR . 'includes/admin-phases.php';
include_once PJ_PLUGIN_DIR . 'includes/project-metadata.php';
include_once PJ_PLUGIN_DIR . 'includes/admin-project-settings.php';

/**
 * Activation hook to create database tables
 */
register_activation_hook(__FILE__, 'pj_create_tables');
function pj_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Table for progress tracking
    $progress_table = $wpdb->prefix . 'project_journey_progress';
    $progress_sql = "CREATE TABLE IF NOT EXISTS $progress_table (
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
    ) $charset_collate;";

    // Table for signatures
    $signatures_table = $wpdb->prefix . 'project_journey_signatures';
    $signatures_sql = "CREATE TABLE IF NOT EXISTS $signatures_table (
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
    ) $charset_collate;";

    // Table for task notes (project journal with revision history)
    $notes_table = $wpdb->prefix . 'project_journey_task_notes';
    $notes_sql = "CREATE TABLE IF NOT EXISTS $notes_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id int(11) NOT NULL DEFAULT 1,
        task_id varchar(100) NOT NULL,
        note_text longtext NOT NULL,
        change_reason text DEFAULT NULL,
        revision_number int(11) NOT NULL DEFAULT 1,
        created_by bigint(20) NOT NULL,
        created_by_name varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY task_index (task_id),
        KEY project_task_index (project_id, task_id),
        KEY created_by_index (created_by)
    ) $charset_collate;";

    // Table for task attachments
    $attachments_table = $wpdb->prefix . 'project_journey_task_attachments';
    $attachments_sql = "CREATE TABLE IF NOT EXISTS $attachments_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        note_id bigint(20) DEFAULT NULL,
        project_id int(11) NOT NULL DEFAULT 1,
        task_id varchar(100) NOT NULL,
        file_name varchar(255) NOT NULL,
        file_path varchar(500) NOT NULL,
        file_type varchar(100) DEFAULT NULL,
        file_size bigint(20) DEFAULT NULL,
        uploaded_by bigint(20) NOT NULL,
        uploaded_by_name varchar(255) NOT NULL,
        uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY note_index (note_id),
        KEY task_index (task_id),
        KEY project_task_index (project_id, task_id)
    ) $charset_collate;";

    // Table for dynamic phases (admin-editable roadmap structure)
    $phases_table = $wpdb->prefix . 'project_journey_phases';
    $phases_sql = "CREATE TABLE IF NOT EXISTS $phases_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id int(11) NOT NULL DEFAULT 1,
        phase_key varchar(50) NOT NULL,
        phase_number int(11) NOT NULL,
        phase_title varchar(255) NOT NULL,
        phase_subtitle text,
        phase_description text,
        sort_order int(11) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY phase_unique (project_id, phase_key),
        KEY project_phase_index (project_id, phase_number)
    ) $charset_collate;";

    // Table for dynamic objectives
    $objectives_table = $wpdb->prefix . 'project_journey_objectives';
    $objectives_sql = "CREATE TABLE IF NOT EXISTS $objectives_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id int(11) NOT NULL DEFAULT 1,
        phase_id bigint(20) NOT NULL,
        objective_key varchar(50) NOT NULL,
        objective_title varchar(255) NOT NULL,
        objective_subtitle text,
        sort_order int(11) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY phase_index (phase_id),
        KEY project_phase_objective (project_id, phase_id, sort_order)
    ) $charset_collate;";

    // Table for dynamic tasks
    $tasks_table = $wpdb->prefix . 'project_journey_tasks';
    $tasks_sql = "CREATE TABLE IF NOT EXISTS $tasks_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id int(11) NOT NULL DEFAULT 1,
        phase_id bigint(20) NOT NULL,
        objective_id bigint(20) NOT NULL,
        task_id varchar(100) NOT NULL,
        task_text text NOT NULL,
        task_details text,
        owner varchar(50),
        sort_order int(11) DEFAULT 0,
        is_active tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY task_unique (project_id, task_id),
        KEY objective_index (objective_id),
        KEY project_objective_task (project_id, objective_id, sort_order)
    ) $charset_collate;";

    // Table for project metadata (roadmap title, subtitle, purpose points, etc.)
    $metadata_table = $wpdb->prefix . 'project_journey_metadata';
    $metadata_sql = "CREATE TABLE IF NOT EXISTS $metadata_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        project_id int(11) NOT NULL DEFAULT 1,
        meta_key varchar(100) NOT NULL,
        meta_value longtext NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY project_meta_unique (project_id, meta_key),
        KEY project_index (project_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($progress_sql);
    dbDelta($signatures_sql);
    dbDelta($notes_sql);
    dbDelta($attachments_sql);
    dbDelta($phases_sql);
    dbDelta($objectives_sql);
    dbDelta($tasks_sql);
    dbDelta($metadata_sql);

    // Set default options
    if (get_option('pj_client_name') === false) {
        update_option('pj_client_name', 'Kim Benedict - Sojourn Coaching');
    }
    if (get_option('pj_consultant_name') === false) {
        update_option('pj_consultant_name', 'PrometheanLink (Walter)');
    }
    if (get_option('pj_logo_url') === false) {
        update_option('pj_logo_url', 'https://walterh50.sg-host.com/wp-content/uploads/2025/11/sojourn-logo.webp');
    }
    if (get_option('pj_pdf_header') === false) {
        update_option('pj_pdf_header', '30/60/90 Project Journey Report');
    }
    if (get_option('pj_email_notifications') === false) {
        update_option('pj_email_notifications', '0');
    }
    if (get_option('pj_notification_email') === false) {
        update_option('pj_notification_email', get_option('admin_email'));
    }

    // Auto-import hardcoded roadmap if database is empty
    if (!pj_roadmap_exists(1)) {
        pj_import_hardcoded_roadmap(1);
    }

    // Auto-initialize default project metadata if not set
    if (!pj_project_meta_exists(1)) {
        pj_set_default_project_meta(1);
    }
}

/**
 * Enqueue styles and scripts
 */
add_action('wp_enqueue_scripts', 'pj_enqueue_assets');
function pj_enqueue_assets() {
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'project_journey_roadmap')) {
        // Enqueue dashicons for file upload icons
        wp_enqueue_style('dashicons');

        wp_enqueue_style('pj-roadmap-styles', PJ_PLUGIN_URL . 'assets/style.css', array(), PJ_VERSION);
        wp_enqueue_script('pj-roadmap-scripts', PJ_PLUGIN_URL . 'assets/script.js', array('jquery'), PJ_VERSION, true);

        // Localize script for AJAX
        wp_localize_script('pj-roadmap-scripts', 'pjAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pj_save_progress_nonce'),
            'userId' => get_current_user_id()
        ));
    }
}

/**
 * Admin enqueue scripts
 */
add_action('admin_enqueue_scripts', 'pj_admin_enqueue_assets');
function pj_admin_enqueue_assets($hook) {
    if (strpos($hook, 'project-journey') !== false) {
        wp_enqueue_style('pj-admin-styles', PJ_PLUGIN_URL . 'assets/admin-style.css', array(), PJ_VERSION);
        wp_enqueue_script('pj-admin-scripts', PJ_PLUGIN_URL . 'assets/admin-script.js', array('jquery'), PJ_VERSION, true);

        // Load phases management assets on phases page
        if ($hook === 'project-journey_page_project-journey-phases') {
            wp_enqueue_style('pj-admin-phases-styles', PJ_PLUGIN_URL . 'assets/admin-phases.css', array(), PJ_VERSION);
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('pj-admin-phases-scripts', PJ_PLUGIN_URL . 'assets/admin-phases.js', array('jquery', 'jquery-ui-sortable'), PJ_VERSION, true);

            // Localize script for AJAX
            wp_localize_script('pj-admin-phases-scripts', 'pjPhasesAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pj_admin_nonce')
            ));
        }
    }
}

/**
 * Register shortcode
 */
add_shortcode('project_journey_roadmap', 'pj_roadmap_shortcode');
function pj_roadmap_shortcode($atts) {
    $atts = shortcode_atts(array(
        'project_id' => 1,
        'editable' => 'true'
    ), $atts);

    ob_start();
    include PJ_PLUGIN_DIR . 'includes/roadmap-display.php';
    return ob_get_clean();
}

/**
 * Add admin menu
 */
add_action('admin_menu', 'pj_add_admin_menu');
function pj_add_admin_menu() {
    add_menu_page(
        '30/60/90 Project Journey',
        'Project Journey',
        'manage_options',
        'project-journey',
        'pj_admin_main_page',
        'dashicons-calendar-alt',
        65
    );

    add_submenu_page(
        'project-journey',
        'Project Phases',
        'Project Phases',
        'manage_options',
        'project-journey-phases',
        'pj_admin_phases_page'
    );

    add_submenu_page(
        'project-journey',
        'Settings',
        'Settings',
        'manage_options',
        'project-journey-settings',
        'pj_admin_project_settings_page'
    );

    add_submenu_page(
        'project-journey',
        'Reports',
        'Reports',
        'manage_options',
        'project-journey-reports',
        'pj_admin_reports_page'
    );

    add_submenu_page(
        'project-journey',
        'Progress Data',
        'Progress Data',
        'manage_options',
        'project-journey-data',
        'pj_admin_data_page'
    );
}
