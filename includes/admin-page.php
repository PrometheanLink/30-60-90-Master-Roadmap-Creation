<?php
if (!defined('ABSPATH')) exit;

/**
 * Main admin page
 */
function pj_admin_main_page() {
    ?>
    <div class="wrap">
        <h1>30/60/90 Project Journey</h1>
        <div class="pj-admin-dashboard">
            <div class="pj-welcome-panel">
                <h2>Welcome to Project Journey</h2>
                <p>Track 30/60/90 day roadmap progress with interactive checkboxes that save to database and generate professional PDF reports.</p>

                <h3>Getting Started</h3>
                <ol>
                    <li><strong>Configure Settings:</strong> Go to <a href="<?php echo admin_url('admin.php?page=project-journey-settings'); ?>">Settings</a> to customize client name, consultant name, logo, and email notifications.</li>
                    <li><strong>Add Shortcode to Page:</strong> Use <code>[project_journey_roadmap]</code> on any page to display the interactive roadmap.</li>
                    <li><strong>Track Progress:</strong> Users can check off tasks, and progress is automatically saved to the database.</li>
                    <li><strong>Generate Reports:</strong> Go to <a href="<?php echo admin_url('admin.php?page=project-journey-reports'); ?>">Reports</a> to generate and download PDF reports.</li>
                    <li><strong>View Data:</strong> See all progress data in the <a href="<?php echo admin_url('admin.php?page=project-journey-data'); ?>">Progress Data</a> section.</li>
                </ol>

                <h3>Shortcode Options</h3>
                <ul>
                    <li><code>[project_journey_roadmap]</code> - Default roadmap (editable)</li>
                    <li><code>[project_journey_roadmap project_id="1"]</code> - Specific project ID</li>
                    <li><code>[project_journey_roadmap editable="false"]</code> - Read-only view</li>
                </ul>
            </div>

            <?php
            // Display statistics
            global $wpdb;
            $table = $wpdb->prefix . 'project_journey_progress';
            $total_tasks = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            $completed_tasks = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE completed = 1");
            $total_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table");
            ?>

            <div class="pj-stats-grid">
                <div class="pj-stat-box">
                    <div class="pj-stat-number"><?php echo $total_tasks; ?></div>
                    <div class="pj-stat-label">Total Tasks Tracked</div>
                </div>
                <div class="pj-stat-box">
                    <div class="pj-stat-number"><?php echo $completed_tasks; ?></div>
                    <div class="pj-stat-label">Tasks Completed</div>
                </div>
                <div class="pj-stat-box">
                    <div class="pj-stat-number"><?php echo $total_users; ?></div>
                    <div class="pj-stat-label">Active Users</div>
                </div>
                <div class="pj-stat-box">
                    <div class="pj-stat-number">
                        <?php echo $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0; ?>%
                    </div>
                    <div class="pj-stat-label">Overall Progress</div>
                </div>
            </div>

            <style>
                .pj-welcome-panel {
                    background: white;
                    padding: 20px;
                    margin: 20px 0;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                .pj-stats-grid {
                    display: grid;
                    grid-template-columns: repeat(4, 1fr);
                    gap: 20px;
                    margin: 20px 0;
                }
                .pj-stat-box {
                    background: white;
                    padding: 20px;
                    text-align: center;
                    border: 1px solid #ccd0d4;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                .pj-stat-number {
                    font-size: 36px;
                    font-weight: bold;
                    color: #c16107;
                }
                .pj-stat-label {
                    font-size: 14px;
                    color: #666;
                    margin-top: 5px;
                }
            </style>
        </div>
    </div>
    <?php
}

/**
 * Settings page
 */
function pj_admin_settings_page() {
    // Handle form submission
    if (isset($_POST['pj_save_settings'])) {
        check_admin_referer('pj_save_settings_nonce', 'pj_save_settings_nonce_field');

        update_option('pj_client_name', sanitize_text_field($_POST['pj_client_name']));
        update_option('pj_consultant_name', sanitize_text_field($_POST['pj_consultant_name']));
        update_option('pj_logo_url', esc_url_raw($_POST['pj_logo_url']));
        update_option('pj_pdf_header', sanitize_text_field($_POST['pj_pdf_header']));
        update_option('pj_email_notifications', isset($_POST['pj_email_notifications']) ? '1' : '0');
        update_option('pj_notification_email', sanitize_email($_POST['pj_notification_email']));

        echo '<div class="updated"><p>Settings saved successfully.</p></div>';
    }

    // Get current settings
    $client_name = get_option('pj_client_name', '');
    $consultant_name = get_option('pj_consultant_name', '');
    $logo_url = get_option('pj_logo_url', '');
    $pdf_header = get_option('pj_pdf_header', '');
    $email_notifications = get_option('pj_email_notifications', '0');
    $notification_email = get_option('pj_notification_email', get_option('admin_email'));
    ?>
    <div class="wrap">
        <h1>Project Journey Settings</h1>
        <form method="post">
            <?php wp_nonce_field('pj_save_settings_nonce', 'pj_save_settings_nonce_field'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pj_client_name">Client Name</label></th>
                    <td>
                        <input type="text" name="pj_client_name" id="pj_client_name"
                               value="<?php echo esc_attr($client_name); ?>" class="regular-text">
                        <p class="description">Name of the client (displayed on roadmap and reports)</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="pj_consultant_name">Consultant Name</label></th>
                    <td>
                        <input type="text" name="pj_consultant_name" id="pj_consultant_name"
                               value="<?php echo esc_attr($consultant_name); ?>" class="regular-text">
                        <p class="description">Name of the consultant (displayed on roadmap and reports)</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="pj_logo_url">Logo URL</label></th>
                    <td>
                        <input type="url" name="pj_logo_url" id="pj_logo_url"
                               value="<?php echo esc_attr($logo_url); ?>" class="regular-text">
                        <p class="description">Full URL to the logo image (used in roadmap header and PDF reports)</p>
                        <?php if (!empty($logo_url)): ?>
                            <p><img src="<?php echo esc_url($logo_url); ?>" style="max-width: 200px; margin-top: 10px;"></p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="pj_pdf_header">PDF Header Text</label></th>
                    <td>
                        <input type="text" name="pj_pdf_header" id="pj_pdf_header"
                               value="<?php echo esc_attr($pdf_header); ?>" class="regular-text">
                        <p class="description">Header text for PDF reports</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="pj_email_notifications">Email Notifications</label></th>
                    <td>
                        <label>
                            <input type="checkbox" name="pj_email_notifications" id="pj_email_notifications"
                                   value="1" <?php checked($email_notifications, '1'); ?>>
                            Send email notification when a task is completed
                        </label>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="pj_notification_email">Notification Email</label></th>
                    <td>
                        <input type="email" name="pj_notification_email" id="pj_notification_email"
                               value="<?php echo esc_attr($notification_email); ?>" class="regular-text">
                        <p class="description">Email address to receive completion notifications</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="pj_save_settings" class="button-primary" value="Save Settings">
            </p>
        </form>
    </div>
    <?php
}

/**
 * Reports page
 */
function pj_admin_reports_page() {
    ?>
    <div class="wrap">
        <h1>Project Journey Reports</h1>

        <div class="pj-reports-panel">
            <h2>Generate PDF Report</h2>
            <p>Generate a professional PDF report showing all completed tasks with signatures section.</p>

            <form method="get" action="<?php echo admin_url('admin-post.php'); ?>" style="margin: 20px 0;">
                <input type="hidden" name="action" value="pj_generate_pdf">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="user_id">User ID</label></th>
                        <td>
                            <input type="number" name="user_id" id="user_id" value="0" class="small-text">
                            <p class="description">Enter 0 for all users, or specific user ID</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="project_id">Project ID</label></th>
                        <td>
                            <input type="number" name="project_id" id="project_id" value="1" class="small-text">
                            <p class="description">Default is 1</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="Generate PDF Report">
                    <a href="<?php echo admin_url('admin-post.php?action=pj_preview_report&user_id=0&project_id=1'); ?>"
                       class="button" target="_blank">Preview in Browser</a>
                </p>
            </form>

            <hr>

            <h2>Recent Activity</h2>
            <?php
            global $wpdb;
            $table = $wpdb->prefix . 'project_journey_progress';
            $recent = $wpdb->get_results(
                "SELECT * FROM $table WHERE completed = 1 ORDER BY completed_at DESC LIMIT 10",
                ARRAY_A
            );

            if (!empty($recent)) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr>';
                echo '<th>Task</th><th>Phase</th><th>Completed By</th><th>Completed At</th>';
                echo '</tr></thead>';
                echo '<tbody>';
                foreach ($recent as $task) {
                    echo '<tr>';
                    echo '<td>' . esc_html($task['task_text']) . '</td>';
                    echo '<td>' . esc_html($task['phase']) . '</td>';
                    echo '<td>' . esc_html($task['completed_by']) . '</td>';
                    echo '<td>' . esc_html($task['completed_at']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p>No completed tasks yet.</p>';
            }
            ?>
        </div>

        <style>
            .pj-reports-panel {
                background: white;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
            }
        </style>
    </div>
    <?php
}

/**
 * Progress Data page
 */
function pj_admin_data_page() {
    global $wpdb;
    $table = $wpdb->prefix . 'project_journey_progress';

    $all_progress = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
    ?>
    <div class="wrap">
        <h1>Progress Data</h1>

        <div class="pj-data-actions">
            <a href="<?php echo admin_url('admin-post.php?action=pj_export_progress'); ?>"
               class="button button-primary">Export to CSV</a>
            <button id="pj-clear-all-data" class="button button-secondary">Clear All Data</button>
        </div>

        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th style="width: 80px;">User ID</th>
                    <th style="width: 100px;">Phase</th>
                    <th style="width: 80px;">Objective</th>
                    <th>Task</th>
                    <th style="width: 100px;">Completed</th>
                    <th style="width: 150px;">Completed By</th>
                    <th style="width: 150px;">Completed At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($all_progress)): ?>
                    <?php foreach ($all_progress as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item['id']); ?></td>
                            <td><?php echo esc_html($item['user_id']); ?></td>
                            <td><?php echo esc_html($item['phase']); ?></td>
                            <td><?php echo esc_html($item['objective']); ?></td>
                            <td><?php echo esc_html($item['task_text']); ?></td>
                            <td><?php echo $item['completed'] ? 'Yes' : 'No'; ?></td>
                            <td><?php echo esc_html($item['completed_by']); ?></td>
                            <td><?php echo esc_html($item['completed_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">No progress data found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <style>
            .pj-data-actions {
                margin: 20px 0;
            }
            .pj-data-actions .button {
                margin-right: 10px;
            }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#pj-clear-all-data').on('click', function() {
                if (confirm('Are you sure you want to delete all progress data? This cannot be undone.')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'pj_clear_progress'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('All progress data has been cleared.');
                                location.reload();
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        }
                    });
                }
            });
        });
        </script>
    </div>
    <?php
}
