<?php
/**
 * Admin Page: Project Settings
 * Manage roadmap title, subtitle, purpose points, and other project-level metadata
 */

if (!defined('ABSPATH')) exit;

/**
 * Register admin menu for Project Settings
 * Note: Menu registration moved to main plugin file to avoid conflicts
 * This submenu is registered as "Settings" in the main admin menu
 */

/**
 * Enqueue assets for Project Settings page
 */
add_action('admin_enqueue_scripts', 'pj_enqueue_project_settings_assets');
function pj_enqueue_project_settings_assets($hook) {
    if ($hook === 'project-journey_page_project-journey-settings') {
        wp_enqueue_style('pj-admin-project-settings-styles', PJ_PLUGIN_URL . 'assets/admin-project-settings.css', array(), PJ_VERSION);
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('pj-admin-project-settings-scripts', PJ_PLUGIN_URL . 'assets/admin-project-settings.js', array('jquery', 'jquery-ui-sortable'), PJ_VERSION, true);

        wp_localize_script('pj-admin-project-settings-scripts', 'pjProjectSettings', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pj_admin_nonce')
        ));
    }
}

/**
 * Admin page callback for Project Settings
 */
function pj_admin_project_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $project_id = 1; // Default project ID
    $meta_exists = pj_project_meta_exists($project_id);
    $metadata = pj_get_all_project_meta($project_id);

    // Default values if not set
    $roadmap_title = isset($metadata['roadmap_title']) ? $metadata['roadmap_title'] : '30/60/90 Project Roadmap';
    $roadmap_subtitle = isset($metadata['roadmap_subtitle']) ? $metadata['roadmap_subtitle'] : 'Moving Sojourn Coaching from idea and uncertainty into a confident, operational, and client-ready coaching practice';
    $purpose_intro = isset($metadata['purpose_intro']) ? $metadata['purpose_intro'] : 'By the end of 90 days, you will have:';
    $purpose_points = isset($metadata['purpose_points']) ? $metadata['purpose_points'] : array(
        'A clear client journey and offers',
        'A professional website and booking system',
        'An intake and onboarding flow',
        'Simple funnels and email automations',
        'A growing library of content and video assets',
        'A sustainable communication rhythm between Client and Consultant'
    );
    $timeline_intro = isset($metadata['timeline_intro']) ? $metadata['timeline_intro'] : 'The project unfolds in strategic phases';
    $show_timeline = isset($metadata['show_timeline_visual']) ? $metadata['show_timeline_visual'] : 'yes';
    ?>
    <div class="wrap pj-project-settings">
        <h1>Project Settings</h1>

        <div class="pj-settings-header">
            <p class="description">
                Customize the roadmap display header, project purpose, and other project-level settings.
                These values will appear on the frontend roadmap.
            </p>

            <?php if (!$meta_exists): ?>
                <div class="notice notice-warning">
                    <p><strong>No project settings found.</strong> Click the button below to initialize with default values.</p>
                </div>
                <button type="button" class="button button-primary" id="pj-init-defaults">
                    Initialize Default Settings
                </button>
            <?php endif; ?>
        </div>

        <form id="pj-project-settings-form" method="post">
            <input type="hidden" name="project_id" value="<?php echo esc_attr($project_id); ?>">

            <!-- Roadmap Header Section -->
            <div class="pj-settings-section">
                <h2>Roadmap Header</h2>
                <p class="description">The main title and subtitle displayed at the top of the roadmap.</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="roadmap_title">Roadmap Title</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="roadmap_title"
                                   name="roadmap_title"
                                   value="<?php echo esc_attr($roadmap_title); ?>"
                                   class="regular-text"
                                   placeholder="30/60/90 Project Roadmap">
                            <p class="description">The main headline displayed at the top of the roadmap (e.g., "30/60/90 Project Roadmap")</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="roadmap_subtitle">Roadmap Subtitle</label>
                        </th>
                        <td>
                            <textarea id="roadmap_subtitle"
                                      name="roadmap_subtitle"
                                      rows="3"
                                      class="large-text"
                                      placeholder="Moving your project from vision to reality..."><?php echo esc_textarea($roadmap_subtitle); ?></textarea>
                            <p class="description">Supporting text under the main title</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Project Purpose Section -->
            <div class="pj-settings-section">
                <h2>Project Purpose</h2>
                <p class="description">Define what the project will accomplish.</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="purpose_intro">Purpose Introduction</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="purpose_intro"
                                   name="purpose_intro"
                                   value="<?php echo esc_attr($purpose_intro); ?>"
                                   class="regular-text"
                                   placeholder="By the end of 90 days, you will have:">
                            <p class="description">Text before the bullet point list</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label>Purpose Points</label>
                        </th>
                        <td>
                            <div id="pj-purpose-points-container">
                                <?php foreach ($purpose_points as $index => $point): ?>
                                    <div class="pj-purpose-point-row">
                                        <span class="dashicons dashicons-menu pj-drag-handle"></span>
                                        <input type="text"
                                               name="purpose_points[]"
                                               value="<?php echo esc_attr($point); ?>"
                                               class="regular-text"
                                               placeholder="Enter a purpose point...">
                                        <button type="button" class="button pj-remove-point" title="Remove">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="button" id="pj-add-purpose-point">
                                <span class="dashicons dashicons-plus-alt2"></span> Add Purpose Point
                            </button>
                            <p class="description">Bullet points describing project outcomes</p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Timeline Settings Section -->
            <div class="pj-settings-section">
                <h2>Timeline Display</h2>
                <p class="description">Configure how the project timeline is displayed.</p>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="timeline_intro">Timeline Introduction</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="timeline_intro"
                                   name="timeline_intro"
                                   value="<?php echo esc_attr($timeline_intro); ?>"
                                   class="regular-text"
                                   placeholder="The project unfolds in strategic phases">
                            <p class="description">Introductory text for the timeline section</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="show_timeline_visual">Show Visual Timeline</label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       id="show_timeline_visual"
                                       name="show_timeline_visual"
                                       value="yes"
                                       <?php checked($show_timeline, 'yes'); ?>>
                                Display the visual phase timeline (circles with day markers)
                            </label>
                            <p class="description">Uncheck to hide the visual timeline and only show phase cards</p>
                        </td>
                    </tr>
                </table>
            </div>

            <p class="submit">
                <button type="submit" class="button button-primary button-large" id="pj-save-settings">
                    Save Project Settings
                </button>
                <span class="pj-save-status"></span>
            </p>
        </form>

        <!-- Hidden template for new purpose point -->
        <script type="text/template" id="pj-purpose-point-template">
            <div class="pj-purpose-point-row">
                <span class="dashicons dashicons-menu pj-drag-handle"></span>
                <input type="text"
                       name="purpose_points[]"
                       value=""
                       class="regular-text"
                       placeholder="Enter a purpose point...">
                <button type="button" class="button pj-remove-point" title="Remove">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
        </script>

        <div id="pj-loading-overlay" style="display:none;">
            <div class="pj-spinner"></div>
        </div>
    </div>
    <?php
}
