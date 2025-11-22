<?php
if (!defined('ABSPATH')) exit;

/**
 * Generate PDF report
 */
add_action('admin_post_pj_generate_pdf', 'pj_generate_pdf_report');

function pj_generate_pdf_report() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    // Suppress all warnings and notices during PDF generation
    $old_error_level = error_reporting(0);

    // Clean all output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }
    ob_start();

    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 1;

    // Load mPDF library
    require_once PJ_PLUGIN_DIR . 'vendor/autoload.php';

    try {
        // Use system temp directory (already writable)
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 30,
            'margin_bottom' => 25,
            'margin_header' => 10,
            'margin_footer' => 10,
            'tempDir' => sys_get_temp_dir()
        ]);

        // Get settings - ensure strings not null
        $logo_url = get_option('pj_logo_url', '') ?: '';
        $client_name = get_option('pj_client_name', '') ?: 'Client';
        $consultant_name = get_option('pj_consultant_name', '') ?: 'Consultant';
        $pdf_header = get_option('pj_pdf_header', '30/60/90 Project Journey Report') ?: '30/60/90 Project Journey Report';

        // Set header (only include logo if URL is valid)
        $header = '<div style="text-align: center; font-family: sans-serif; font-size: 10pt; color: #666;">';
        if (!empty($logo_url) && filter_var($logo_url, FILTER_VALIDATE_URL)) {
            $header .= '<img src="' . esc_url($logo_url) . '" style="max-height: 40px; margin-bottom: 10px;">';
        }
        $header .= '<div>' . esc_html($pdf_header) . '</div>';
        $header .= '</div>';
        $mpdf->SetHTMLHeader($header);

        // Set footer
        $footer = '<div style="text-align: center; font-family: sans-serif; font-size: 9pt; color: #666;">Page {PAGENO} of {nbpg}</div>';
        $mpdf->SetHTMLFooter($footer);

        // Get progress data
        $progress = pj_get_user_progress($user_id, $project_id);

        // Generate HTML content
        $html = pj_generate_pdf_html($progress, $client_name, $consultant_name, $logo_url);

        // Write HTML to PDF
        $mpdf->WriteHTML($html);

        // Clean output buffer before PDF output
        ob_end_clean();

        // Output PDF
        $filename = '30-60-90-project-journey-' . date('Y-m-d') . '.pdf';
        $mpdf->Output($filename, 'D'); // D = download

    } catch (\Mpdf\MpdfException $e) {
        ob_end_clean();
        wp_die('Error generating PDF: ' . $e->getMessage());
    }

    exit;
}

/**
 * Generate HTML content for PDF
 */
function pj_generate_pdf_html($progress, $client_name, $consultant_name, $logo_url) {
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: 'DejaVu Sans', sans-serif;
                font-size: 11pt;
                line-height: 1.6;
                color: #333;
            }
            h1 {
                font-size: 24pt;
                color: #c16107;
                margin-bottom: 10px;
                text-align: center;
            }
            h2 {
                font-size: 18pt;
                color: #1e262d;
                margin-top: 20px;
                margin-bottom: 10px;
                border-bottom: 2px solid #c16107;
                padding-bottom: 5px;
            }
            h3 {
                font-size: 14pt;
                color: #333;
                margin-top: 15px;
                margin-bottom: 8px;
            }
            .subtitle {
                text-align: center;
                font-size: 12pt;
                color: #666;
                margin-bottom: 30px;
            }
            .roles {
                display: table;
                width: 100%;
                margin: 20px 0;
            }
            .role {
                display: table-cell;
                width: 50%;
                padding: 15px;
                border: 2px solid #c16107;
                text-align: center;
                background: #f5f5f5;
            }
            .role h4 {
                color: #c16107;
                margin: 0 0 5px 0;
            }
            .objective {
                margin: 20px 0;
                page-break-inside: avoid;
            }
            .objective-header {
                background: #eeedeb;
                padding: 10px;
                margin-bottom: 10px;
                border-left: 5px solid #c16107;
            }
            .objective-title {
                font-weight: bold;
                font-size: 13pt;
                color: #1e262d;
            }
            .objective-subtitle {
                font-style: italic;
                color: #666;
                font-size: 10pt;
            }
            .task {
                padding: 8px 10px;
                margin: 5px 0;
                background: #f9f9f9;
                border-left: 3px solid #ccc;
            }
            .task.completed {
                background: #e5f5d9;
                border-left-color: #95c93d;
            }
            .task.completed:before {
                content: "✓ ";
                color: #95c93d;
                font-weight: bold;
                margin-right: 5px;
            }
            .completion-info {
                font-size: 9pt;
                color: #666;
                margin-top: 3px;
            }
            .summary-box {
                background: #fff9f5;
                border: 2px solid #c16107;
                padding: 20px;
                margin: 30px 0;
                text-align: center;
            }
            .signature-section {
                margin-top: 40px;
                page-break-inside: avoid;
            }
            .signature-box {
                border: 1px solid #ccc;
                padding: 15px;
                margin: 10px 0;
                min-height: 80px;
            }
            .signature-label {
                font-weight: bold;
                margin-bottom: 10px;
            }
            .phase-badge {
                background: #c16107;
                color: white;
                padding: 5px 15px;
                border-radius: 20px;
                display: inline-block;
                margin: 20px 0 10px 0;
                font-weight: bold;
            }
            .stats-box {
                background: #f5f5f5;
                padding: 15px;
                margin: 20px 0;
                border-radius: 5px;
            }
            .stat-item {
                display: inline-block;
                width: 30%;
                text-align: center;
                padding: 10px;
            }
            .stat-number {
                font-size: 24pt;
                font-weight: bold;
                color: #c16107;
            }
            .stat-label {
                font-size: 10pt;
                color: #666;
            }
        </style>
    </head>
    <body>
        <h1>30/60/90 Project Journey</h1>
        <div class="subtitle">Progress Report - <?php echo date('F j, Y'); ?></div>

        <div class="roles">
            <div class="role" style="border-right: 1px solid white;">
                <h4>Client</h4>
                <p><?php echo esc_html($client_name); ?></p>
            </div>
            <div class="role" style="border-left: 1px solid white;">
                <h4>Consultant</h4>
                <p><?php echo esc_html($consultant_name); ?></p>
            </div>
        </div>

        <?php
        // Calculate statistics
        $total_tasks = 0;
        $completed_tasks = 0;
        foreach ($progress as $task) {
            $total_tasks++;
            if ($task['completed']) {
                $completed_tasks++;
            }
        }
        $completion_percentage = $total_tasks > 0 ? round(($completed_tasks / $total_tasks) * 100) : 0;
        ?>

        <div class="stats-box">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_tasks; ?></div>
                <div class="stat-label">Total Tasks</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $completed_tasks; ?></div>
                <div class="stat-label">Completed</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $completion_percentage; ?>%</div>
                <div class="stat-label">Progress</div>
            </div>
        </div>

        <?php
        // Organize tasks by phase and objective
        $phases = array(
            'phase1' => array('title' => 'Phase 1: Days 1–30', 'subtitle' => 'Discovery, Foundations & Communication', 'objectives' => array()),
            'phase2' => array('title' => 'Phase 2: Days 31–60', 'subtitle' => 'Website Build, Booking, Funnel & AI Support', 'objectives' => array()),
            'phase3' => array('title' => 'Phase 3: Days 61–90', 'subtitle' => 'Refinement, Launch, and Momentum', 'objectives' => array())
        );

        foreach ($progress as $task) {
            $phase = $task['phase'];
            $objective = $task['objective'];
            if (!isset($phases[$phase]['objectives'][$objective])) {
                $phases[$phase]['objectives'][$objective] = array();
            }
            $phases[$phase]['objectives'][$objective][] = $task;
        }

        // Render each phase
        foreach ($phases as $phase_key => $phase_data) {
            if (empty($phase_data['objectives'])) {
                continue; // Skip phases with no tasks
            }
            ?>
            <div class="phase-badge"><?php echo esc_html($phase_data['title']); ?></div>
            <p style="font-style: italic; color: #666;"><?php echo esc_html($phase_data['subtitle']); ?></p>

            <?php
            foreach ($phase_data['objectives'] as $objective_letter => $tasks) {
                ?>
                <div class="objective">
                    <div class="objective-header">
                        <div class="objective-title">Objective <?php echo esc_html($objective_letter); ?></div>
                    </div>
                    <?php foreach ($tasks as $task): ?>
                        <div class="task <?php echo $task['completed'] ? 'completed' : ''; ?>">
                            <?php echo esc_html($task['task_text']); ?>
                            <?php if ($task['completed'] && $task['completed_by']): ?>
                                <div class="completion-info">
                                    Completed by <?php echo esc_html($task['completed_by']); ?>
                                    <?php if ($task['completed_at']): ?>
                                        on <?php echo date('M j, Y', strtotime($task['completed_at'])); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
            }
        }
        ?>

        <div class="summary-box">
            <h3>90-Day Buildout Summary</h3>
            <p>This roadmap guides Sojourn Coaching from initial foundations to a fully operational, client-ready coaching practice. Over 90 days we establish communication rhythms, design the client journey and 8-week coaching structure, create a branded intake and onboarding flow, build and launch a professional website with integrated booking, set up a lead magnet and welcome email sequence, introduce AI tools to support content and communication, begin a sustainable content and social presence rhythm, and solidify systems for ongoing momentum.</p>
        </div>

        <div class="signature-section">
            <h3>Signatures</h3>
            <div class="signature-box">
                <div class="signature-label">Client Signature:</div>
                <div style="margin-top: 40px;">___________________________________</div>
                <div style="margin-top: 5px; font-size: 9pt; color: #666;">Date: _______________</div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Consultant Signature:</div>
                <div style="margin-top: 40px;">___________________________________</div>
                <div style="margin-top: 5px; font-size: 9pt; color: #666;">Date: _______________</div>
            </div>
        </div>

    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Generate preview HTML (for viewing in browser before PDF generation)
 */
add_action('admin_post_pj_preview_report', 'pj_preview_report');
function pj_preview_report() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }

    $user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 1;

    $logo_url = get_option('pj_logo_url', '');
    $client_name = get_option('pj_client_name', '');
    $consultant_name = get_option('pj_consultant_name', '');

    $progress = pj_get_user_progress($user_id, $project_id);

    echo pj_generate_pdf_html($progress, $client_name, $consultant_name, $logo_url);
    exit;
}
