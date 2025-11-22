<?php
if (!defined('ABSPATH')) exit;

// Get current user progress
$user_id = get_current_user_id();
$project_id = isset($atts['project_id']) ? intval($atts['project_id']) : 1;
$editable = isset($atts['editable']) && $atts['editable'] === 'false' ? false : true;
$progress = pj_get_user_progress($user_id, $project_id);

// Get settings from options
$logo_url = get_option('pj_logo_url', '');
$client_name = get_option('pj_client_name', 'Kim Benedict - Sojourn Coaching');
$consultant_name = get_option('pj_consultant_name', 'PrometheanLink (Walter)');

// Get project metadata (roadmap title, subtitle, purpose points, etc.)
$roadmap_title = pj_get_project_meta($project_id, 'roadmap_title', '30/60/90 Project Roadmap');
$roadmap_subtitle = pj_get_project_meta($project_id, 'roadmap_subtitle', 'Moving Sojourn Coaching from idea and uncertainty into a confident, operational, and client-ready coaching practice');
$purpose_intro = pj_get_project_meta($project_id, 'purpose_intro', 'By the end of 90 days, you will have:');
$purpose_points = pj_get_project_meta($project_id, 'purpose_points', array(
    'A clear client journey and offers',
    'A professional website and booking system',
    'An intake and onboarding flow',
    'Simple funnels and email automations',
    'A growing library of content and video assets',
    'A sustainable communication rhythm between Client and Consultant'
));
$timeline_intro = pj_get_project_meta($project_id, 'timeline_intro', 'The project unfolds in strategic phases');
$show_timeline = pj_get_project_meta($project_id, 'show_timeline_visual', 'yes');

// Get all phases from database for dynamic timeline
$all_phases = pj_get_all_phases($project_id);

// Ensure purpose_points is always an array
if (!is_array($purpose_points)) {
    $purpose_points = array($purpose_points);
}

// Ensure all_phases is always an array (graceful fallback for empty database)
if (!is_array($all_phases)) {
    $all_phases = array();
}
?>

<div class="wormhole-roadmap" data-project-id="<?php echo esc_attr($project_id); ?>">
    <header class="wormhole-logo-header">
        <div class="container">
            <?php if (!empty($logo_url)): ?>
                <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($client_name); ?>" class="site-logo">
            <?php endif; ?>
        </div>
    </header>

    <div class="hero">
        <div class="container">
            <h1><?php echo esc_html($roadmap_title); ?></h1>
            <p class="subtitle"><?php echo esc_html($roadmap_subtitle); ?></p>
        </div>
    </div>

    <section>
        <div class="container content-width">
            <h2>Project Purpose</h2>
            <?php if (!empty($purpose_intro)): ?>
                <p><?php echo esc_html($purpose_intro); ?></p>
            <?php endif; ?>
            <ul class="purpose-list">
                <?php foreach ($purpose_points as $point): ?>
                    <li><?php echo esc_html($point); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="alt-bg">
        <div class="container content-width">
            <h2>Project Roles</h2>
            <div class="roles-container">
                <div class="role-card">
                    <h4>Client</h4>
                    <p><?php echo esc_html($client_name); ?></p>
                </div>
                <div class="role-card">
                    <h4>Consultant</h4>
                    <p><?php echo esc_html($consultant_name); ?></p>
                </div>
            </div>
        </div>
    </section>

    <?php if ($show_timeline === 'yes' && !empty($all_phases)): ?>
    <section>
        <div class="container content-width">
            <h2>Project Timeline</h2>
            <?php if (!empty($timeline_intro)): ?>
                <p class="timeline-intro"><?php echo esc_html($timeline_intro); ?></p>
            <?php endif; ?>
            <div class="phase-timeline">
                <?php foreach ($all_phases as $phase): ?>
                    <div class="phase-marker phase-<?php echo esc_attr($phase['phase_number']); ?>">
                        <div class="phase-circle"><?php echo esc_html($phase['phase_number'] * 30); ?></div>
                        <div class="phase-label"><?php echo esc_html($phase['phase_title']); ?></div>
                        <?php if (!empty($phase['phase_subtitle'])): ?>
                            <p class="phase-subtitle"><?php echo nl2br(esc_html($phase['phase_subtitle'])); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <?php
    // Load phases, objectives, and tasks from database
    foreach ($all_phases as $phase):
        $phase_id = $phase['id'];
        $phase_number = $phase['phase_number'];
        $phase_bg_class = 'phase-' . $phase_number . '-bg';

        // Get objectives for this phase
        $objectives = pj_get_objectives_by_phase($phase_id);
        ?>

        <!-- PHASE <?php echo esc_html($phase_number); ?> (Database-Driven) -->
        <section class="<?php echo esc_attr($phase_bg_class); ?>">
            <div class="container content-width">
                <h2><?php echo esc_html($phase['phase_title']); ?></h2>
                <p class="section-intro"><?php echo esc_html($phase['phase_subtitle']); ?></p>

                <?php if (!empty($phase['phase_description'])):
                    // Split description into bullet points if it contains line breaks
                    $goals = array_filter(explode("\n", $phase['phase_description']));
                    if (!empty($goals)):
                ?>
                <div class="goals-grid">
                    <?php foreach ($goals as $goal): ?>
                        <div class="goal-item">
                            <p><?php echo esc_html(trim($goal)); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php
                    endif;
                endif;

                // Render each objective with its tasks
                foreach ($objectives as $objective):
                    $objective_id = $objective['id'];
                    $objective_key = $objective['objective_key'];

                    // Get tasks for this objective
                    $db_tasks = pj_get_tasks_by_objective($objective_id);

                    // Convert database tasks to format expected by pj_render_objective()
                    $tasks_array = array();
                    foreach ($db_tasks as $task) {
                        $tasks_array[] = array(
                            'text' => $task['task_text'],
                            'owner' => !empty($task['owner']) ? $task['owner'] : 'both',
                            'details' => !empty($task['task_details']) ? $task['task_details'] : null
                        );
                    }

                    // Render the objective
                    echo pj_render_objective(
                        'phase' . $phase_number,
                        $objective_key,
                        $objective['objective_title'],
                        $objective['objective_subtitle'],
                        $tasks_array,
                        $progress,
                        $editable
                    );
                endforeach;
                ?>
            </div>
        </section>
    <?php endforeach; ?>

    <?php /* OLD HARDCODED VERSION REMOVED - Now using database-driven display above */
    /*
    <!-- PHASE 1 -->
    <section class="phase-1-bg">
        <div class="container content-width">
            <h2>Phase 1: Days 1–30</h2>
            <p class="section-intro">Discovery, Foundations & Communication</p>

            <div class="goals-grid">
                <div class="goal-item">
                    <p>Align on brand, client journey, offers, and communication rhythm</p>
                </div>
                <div class="goal-item">
                    <p>Build core intake assets and project roadmap</p>
                </div>
                <div class="goal-item">
                    <p>Begin foundational website and system setup</p>
                </div>
            </div>

    */ ?>

    <!-- Summary Section -->
    <section>
        <div class="container content-width">
            <div class="summary-box">
                <h3>90-Day Buildout Summary</h3>
                <p>This roadmap guides Sojourn Coaching from initial foundations to a fully operational, client-ready coaching practice. Over 90 days we establish communication rhythms, design the client journey and 8-week coaching structure, create a branded intake and onboarding flow, build and launch a professional website with integrated booking, set up a lead magnet and welcome email sequence, introduce AI tools to support content and communication, begin a sustainable content and social presence rhythm, and solidify systems for ongoing momentum.</p>
            </div>
        </div>
    </section>
</div>

<?php
/**
 * Helper function to render an objective card
 */
function pj_render_objective($phase, $letter, $title, $subtitle, $tasks, $progress, $editable = true) {
    ob_start();
    ?>
    <div class="objective-card">
        <div class="objective-header">
            <div class="objective-letter"><?php echo esc_html($letter); ?></div>
            <div class="objective-title">
                <h4><?php echo esc_html($title); ?></h4>
                <p class="objective-subtitle"><?php echo esc_html($subtitle); ?></p>
            </div>
        </div>
        <ul class="checklist">
            <?php foreach ($tasks as $index => $task):
                $task_id = $phase . '-' . $letter . '-' . ($index + 1);
                $is_completed = isset($task['completed']) && $task['completed'] === true;

                // Check database for completion status
                if (isset($progress[$task_id])) {
                    $is_completed = (bool) $progress[$task_id]['completed'];
                    $completed_by = $progress[$task_id]['completed_by'];
                    $completed_at = $progress[$task_id]['completed_at'];
                } else {
                    $completed_by = null;
                    $completed_at = null;
                }

                $owner_class = 'owner-' . (isset($task['owner']) ? $task['owner'] : 'both');
                $owner_label = isset($task['owner']) ? ucfirst($task['owner']) : 'Both';
            ?>
            <li class="checklist-item <?php echo $is_completed ? 'completed' : ''; ?>"
                data-task-id="<?php echo esc_attr($task_id); ?>"
                data-phase="<?php echo esc_attr($phase); ?>"
                data-objective="<?php echo esc_attr($letter); ?>">

                <div class="checkbox <?php echo $editable ? 'clickable' : ''; ?>"></div>
                <div class="checklist-content">
                    <div class="checklist-text"><?php echo esc_html($task['text']); ?></div>

                    <?php if (isset($task['details'])): ?>
                        <span class="owner-badge <?php echo esc_attr($owner_class); ?>">
                            <?php echo esc_html($task['details']); ?>
                        </span>
                    <?php else: ?>
                        <span class="owner-badge <?php echo esc_attr($owner_class); ?>">
                            Owner: <?php echo esc_html($owner_label); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($is_completed && $completed_by): ?>
                        <div class="completion-info">
                            <small>Completed by <?php echo esc_html($completed_by); ?>
                            <?php if ($completed_at): ?>
                                on <?php echo date('M j, Y', strtotime($completed_at)); ?>
                            <?php endif; ?>
                            </small>
                        </div>
                    <?php endif; ?>

                    <?php if ($editable): ?>
                        <button class="accordion-toggle" type="button" aria-expanded="false">
                            <span class="toggle-icon">+</span>
                            <span class="toggle-text">Add Journal Note</span>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($editable): ?>
                <div class="accordion-content" style="display: none;">
                    <div class="task-journal-panel">
                        <button type="button" class="accordion-close" aria-label="Close journal panel">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                        <!-- Note Editor -->
                        <div class="journal-editor">
                            <label for="note-<?php echo esc_attr($task_id); ?>" class="journal-label">
                                Task Notes & Details
                            </label>
                            <textarea
                                id="note-<?php echo esc_attr($task_id); ?>"
                                class="journal-textarea"
                                rows="6"
                                placeholder="Add detailed notes about this task, including what was done, decisions made, or next steps..."
                            ></textarea>

                            <label for="change-reason-<?php echo esc_attr($task_id); ?>" class="journal-label">
                                Change Reason (Optional)
                            </label>
                            <input
                                type="text"
                                id="change-reason-<?php echo esc_attr($task_id); ?>"
                                class="change-reason-input"
                                placeholder="Why are you updating this note? (e.g., 'Client requested revision', 'Approach changed')"
                            />

                            <div class="journal-actions">
                                <button type="button" class="button-save-note">Save Note</button>
                                <button type="button" class="button-attach-file">
                                    <span class="dashicons dashicons-paperclip"></span> Attach File
                                </button>
                                <input type="file" class="file-upload-input" style="display: none;" accept="image/*,.pdf,.doc,.docx,.odt,.rtf,.txt,.xls,.xlsx,.ods,.csv,.ppt,.pptx,.odp,.zip,.rar,.7z,.tar,.gz,.mp3,.wav,.ogg,.m4a,.mp4,.mov,.avi,.wmv,.flv,.webm,.json,.xml,.css,.html,.js">
                            </div>
                        </div>

                        <!-- File Attachments -->
                        <div class="journal-attachments">
                            <h5>Attachments</h5>
                            <div class="attachments-list"></div>
                        </div>

                        <!-- Revision History -->
                        <div class="journal-history">
                            <h5>Revision History</h5>
                            <div class="history-list"></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}
?>
