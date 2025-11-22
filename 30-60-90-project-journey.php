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

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($progress_sql);
    dbDelta($signatures_sql);
    dbDelta($notes_sql);
    dbDelta($attachments_sql);

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
        'Settings',
        'Settings',
        'manage_options',
        'project-journey-settings',
        'pj_admin_settings_page'
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
