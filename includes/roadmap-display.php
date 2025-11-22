<?php
if (!defined('ABSPATH')) exit;

// Get current user progress
$user_id = get_current_user_id();
$project_id = isset($atts['project_id']) ? intval($atts['project_id']) : 1;
$editable = isset($atts['editable']) && $atts['editable'] === 'false' ? false : true;
$progress = pj_get_user_progress($user_id, $project_id);

// Get settings
$logo_url = get_option('pj_logo_url', '');
$client_name = get_option('pj_client_name', 'Kim Benedict - Sojourn Coaching');
$consultant_name = get_option('pj_consultant_name', 'PrometheanLink (Walter)');
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
            <h1>30/60/90 Project Roadmap</h1>
            <p class="subtitle">Moving Sojourn Coaching from idea and uncertainty into a confident, operational, and client-ready coaching practice</p>
        </div>
    </div>

    <section>
        <div class="container content-width">
            <h2>Project Purpose</h2>
            <ul class="purpose-list">
                <li>A clear client journey and offers</li>
                <li>A professional website and booking system</li>
                <li>An intake and onboarding flow</li>
                <li>Simple funnels and email automations</li>
                <li>A growing library of content and video assets</li>
                <li>A sustainable communication rhythm between Client and Consultant</li>
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

    <section>
        <div class="container content-width">
            <h2>Project Timeline</h2>
            <div class="phase-timeline">
                <div class="phase-marker phase-1">
                    <div class="phase-circle">30</div>
                    <div class="phase-label">Phase 1</div>
                    <p class="phase-subtitle">Discovery, Foundations<br>& Communication</p>
                </div>
                <div class="phase-marker phase-2">
                    <div class="phase-circle">60</div>
                    <div class="phase-label">Phase 2</div>
                    <p class="phase-subtitle">Website Build, Booking,<br>Funnel & AI Support</p>
                </div>
                <div class="phase-marker phase-3">
                    <div class="phase-circle">90</div>
                    <div class="phase-label">Phase 3</div>
                    <p class="phase-subtitle">Refinement, Launch<br>& Momentum</p>
                </div>
            </div>
        </div>
    </section>

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

            <?php echo pj_render_objective('phase1', 'A', 'Communication & Working Agreement', 'Establish a predictable way of working together', array(
                array('text' => 'Decide preferred communication style (scheduled calls vs. flexible check-ins)', 'owner' => 'client'),
                array('text' => 'Confirm primary communication channels (e.g. Zoom, email, text, shared folder, etc.)', 'owner' => 'both'),
                array('text' => 'Agree on basic response time expectations (e.g. 24–48 hours on weekdays)', 'owner' => 'both'),
                array('text' => 'Create and approve a simple Working Relationship / Accountability Agreement', 'owner' => 'both', 'details' => 'Consultant drafts, Client reviews & approves')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase1', 'B', 'Client Journey & Coaching Structure', 'Define how Sojourn Coaching clients move from first contact to transformation', array(
                array('text' => 'Map the 8-week coaching arc (start → middle → completion)', 'owner' => 'both'),
                array('text' => 'Define core stages of the client journey (e.g. Discovery → Intake → First Sessions → Midpoint Check-in → Completion & Next Steps)', 'owner' => 'both'),
                array('text' => 'Outline first consultation structure (intake call flow, key questions)', 'owner' => 'both', 'details' => 'Client leads, Consultant supports'),
                array('text' => 'Identify 2–3 primary outcomes clients should achieve by the end of 8 weeks', 'owner' => 'both', 'details' => 'Client identifies, Consultant refines wording'),
                array('text' => 'Draft a simple visual coaching roadmap/diagram (even rough at first)', 'owner' => 'consultant', 'details' => 'Consultant drafts, Client reviews')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase1', 'C', 'Intake PDF & Client Onboarding', 'Create a branded intake experience to use once clients sign up', array(
                array('text' => 'Client drafts raw intake questions/content (checkboxes, comment fields, reflection prompts)', 'owner' => 'client'),
                array('text' => 'Consultant reviews and structures questions into sections (e.g. Background, Current Challenges, Goals, Boundaries)', 'owner' => 'consultant'),
                array('text' => 'Consultant designs branded intake PDF (logo, fonts, colors, layout)', 'owner' => 'consultant'),
                array('text' => 'Client reviews intake PDF and requests any revisions (up to agreed rounds)', 'owner' => 'client'),
                array('text' => 'Final intake PDF saved in shared folder and linked in onboarding flow (email/site)', 'owner' => 'consultant')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase1', 'D', 'Website & Tech Foundations – Initial Setup', 'Stand up the core environment for Sojourn Coaching\'s online presence', array(
                array('text' => 'Confirm final platform for main site (WordPress install location)', 'owner' => 'both', 'details' => 'Consultant, Client approval'),
                array('text' => 'Confirm current domain and where it should point (e.g. sojourn-coaching.com → new host)', 'owner' => 'both', 'details' => 'Consultant, Client approval'),
                array('text' => 'Point domain to new hosting and verify propagation', 'owner' => 'consultant'),
                array('text' => 'Install and configure base WordPress theme and structure (Home, About, Work with Kim, Contact/Book, etc.)', 'owner' => 'consultant'),
                array('text' => 'Create shared folder (e.g. Google Drive) for assets: photos, logo, headshots, brand elements', 'owner' => 'both', 'details' => 'Consultant creates, Client uploads content'),
                array('text' => 'Client provides initial copy: About bio, why Sojourn, a few testimonials if available', 'owner' => 'client')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase1', 'E', '30/60/90 Master Roadmap Creation', 'Turn this plan into a living tracker for both parties', array(
                array('text' => 'Consultant converts this document into a visual 30/60/90 roadmap (phases, milestones, checkboxes)', 'owner' => 'consultant', 'completed' => true),
                array('text' => 'Roadmap uploaded to a private online space / dashboard for Client access', 'owner' => 'consultant'),
                array('text' => 'Walkthrough of the roadmap on a call to confirm priorities and expectations', 'owner' => 'both')
            ), $progress, $editable); ?>
        </div>
    </section>

    <!-- PHASE 2 -->
    <section class="phase-2-bg">
        <div class="container content-width">
            <h2>Phase 2: Days 31–60</h2>
            <p class="section-intro">Website Build, Booking, Funnel & AI Support</p>

            <div class="goals-grid">
                <div class="goal-item">
                    <p>Launch a professional website with integrated booking</p>
                </div>
                <div class="goal-item">
                    <p>Set up a simple but effective lead funnel and welcome emails</p>
                </div>
                <div class="goal-item">
                    <p>Introduce AI tools into Kim's workflow in a supportive, non-overwhelming way</p>
                </div>
            </div>

            <?php echo pj_render_objective('phase2', 'A', 'Website Buildout (Core Pages Live)', 'Make Sojourn Coaching publicly presentable and aligned with the brand', array(
                array('text' => 'Finalize site navigation (e.g. Home, Work with Kim, About, Testimonials, Book a Clarity Call, Resources/Videos, etc.)', 'owner' => 'both'),
                array('text' => 'Build and style Home page with clear messaging and CTA (e.g. "Book a Clarity Call")', 'owner' => 'consultant'),
                array('text' => 'Build Work with Kim page outlining offers, 8-week journey, and who it\'s for', 'owner' => 'both', 'details' => 'Consultant drafts, Client approves/refines wording'),
                array('text' => 'Build About page with story, values, and credibility markers', 'owner' => 'both', 'details' => 'Consultant drafts, Client provides/refines bio and story'),
                array('text' => 'Create Book a Clarity Call page (or modal) linking to booking system', 'owner' => 'consultant'),
                array('text' => 'Connect intake PDF into the onboarding flow where appropriate (e.g. after booking or post-enrollment)', 'owner' => 'consultant')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase2', 'B', 'Booking & Scheduling System', 'Give prospects and clients a simple, clear way to book time with Kim', array(
                array('text' => 'Choose scheduler platform (e.g. Calendly, SimplyBook, or preferred system)', 'owner' => 'both'),
                array('text' => 'Set up availability blocks for Clarity Calls and Coaching Sessions', 'owner' => 'both', 'details' => 'Client, Consultant guidance'),
                array('text' => 'Enable automated reminders and timezone handling', 'owner' => 'consultant'),
                array('text' => 'Embed booking widget or link into "Book a Clarity Call" page and other strategic places on site', 'owner' => 'consultant'),
                array('text' => 'Test end-to-end booking flow (visitor → booking → confirmation emails)', 'owner' => 'both')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase2', 'C', 'Funnel & Email Automation', 'Capture interested leads and nurture them into conversations or clients', array(
                array('text' => 'Decide on lead magnet concept (e.g. mini-guide, checklist, reflection, Clarity & Confidence Index, short video)', 'owner' => 'both', 'details' => 'Client, Consultant supports format'),
                array('text' => 'Draft content for the lead magnet (or outline for Consultant to polish)', 'owner' => 'both', 'details' => 'Client, Consultant refines'),
                array('text' => 'Create Landing Page for lead magnet with clear promise and opt-in form', 'owner' => 'consultant'),
                array('text' => 'Choose and configure email platform (e.g. MailerLite, ConvertKit, or similar)', 'owner' => 'both', 'details' => 'Consultant, Client approvals'),
                array('text' => 'Build 3–5 email Welcome Sequence triggered on opt-in: 1) Welcome & story, 2) Value/insight/teaching, 3) Invitation to Clarity Call, (Optional) 4 & 5: Case study, FAQs, soft offer', 'owner' => 'both', 'details' => 'Consultant drafts, Client reviews & tweaks'),
                array('text' => 'Integrate opt-in form with email platform and test the full flow', 'owner' => 'consultant')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase2', 'D', 'AI Support – Jumpstart & Workflow Integration', 'Help Kim confidently use AI tools to support content and communication', array(
                array('text' => 'Identify core AI tools to use (e.g. ChatGPT for writing, image tools, avatar video intro if desired)', 'owner' => 'consultant'),
                array('text' => 'Provide a small Prompt Pack tailored to Sojourn Coaching for: Client messaging (bios, intros, DMs), Content ideas (blogs, social posts, email subject lines), Simple scripting for short videos (reels/shorts)', 'owner' => 'consultant'),
                array('text' => 'Conduct a short AI onboarding session (screen share) to walk through how Kim can safely and simply use these tools', 'owner' => 'both', 'details' => 'Consultant leads, Client participates'),
                array('text' => 'Save prompts and workflows in a reference doc Kim can revisit', 'owner' => 'consultant')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase2', 'E', 'Video & Content Planning', 'Lay groundwork for a library of content Kim can grow over time', array(
                array('text' => 'Decide whether to start with a private video area or go public (e.g. YouTube later)', 'owner' => 'both', 'details' => 'Client, Consultant input'),
                array('text' => 'Outline 3–5 starter video topics (stories, teachings, reflections)', 'owner' => 'both', 'details' => 'Client brainstorming, Consultant structures'),
                array('text' => 'Choose where early videos will live (site, unlisted links, private library, etc.)', 'owner' => 'both'),
                array('text' => 'Document a simple repeatable flow: idea → outline → record → upload → publish/link on site', 'owner' => 'consultant')
            ), $progress, $editable); ?>
        </div>
    </section>

    <!-- PHASE 3 -->
    <section class="phase-3-bg">
        <div class="container content-width">
            <h2>Phase 3: Days 61–90</h2>
            <p class="section-intro">Refinement, Launch, and Momentum</p>

            <div class="goals-grid">
                <div class="goal-item">
                    <p>Refine everything based on real feedback</p>
                </div>
                <div class="goal-item">
                    <p>Prepare for public launch or re-launch of Sojourn Coaching</p>
                </div>
                <div class="goal-item">
                    <p>Solidify content rhythm and client experience</p>
                </div>
            </div>

            <?php echo pj_render_objective('phase3', 'A', 'Website & Funnel Refinement', 'Polish the experience now that the basics are live', array(
                array('text' => 'Review analytics and feedback from early visitors (if available)', 'owner' => 'both', 'details' => 'Consultant gathers, Both review'),
                array('text' => 'Adjust copy and layout on key pages (Home, Work with Kim, Book a Call) for clarity and conversions', 'owner' => 'both', 'details' => 'Consultant, Client approvals'),
                array('text' => 'Refine any visual elements that feel off-brand or unclear', 'owner' => 'consultant'),
                array('text' => 'Confirm all links, forms, and booking flows function reliably', 'owner' => 'consultant')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase3', 'B', 'Coaching System & Client Journey Finalization', 'Lock in a repeatable and confident client experience', array(
                array('text' => 'Finalize 8-week coaching roadmap graphic/diagram and add to website and/or onboarding material', 'owner' => 'both', 'details' => 'Consultant creates, Client approves'),
                array('text' => 'Standardize First Session format (how sessions begin, what\'s reviewed, expectations set)', 'owner' => 'both', 'details' => 'Client, Consultant documents'),
                array('text' => 'Define simple check-in points (e.g. Week 4 midpoint review, final session recap and next steps)', 'owner' => 'both'),
                array('text' => 'Create a short "How to Get the Most Out of Coaching with Kim" guide (text or video) for new clients', 'owner' => 'both', 'details' => 'Consultant drafts, Client finalizes tone')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase3', 'C', 'Social Presence & Launch Support', 'Create a lightweight but aligned presence on at least one platform', array(
                array('text' => 'Decide primary platform (e.g. Instagram or LinkedIn) to focus on first', 'owner' => 'both', 'details' => 'Client, Consultant input'),
                array('text' => 'Set or refine profile (photo, bio, link to Clarity Call/lead magnet)', 'owner' => 'both', 'details' => 'Consultant drafts, Client approves'),
                array('text' => 'Provide 8–10 simple content prompts aligned to Sojourn Coaching themes', 'owner' => 'consultant'),
                array('text' => 'Map out a realistic posting rhythm (e.g. 1–2x per week) that feels sustainable', 'owner' => 'both'),
                array('text' => 'Announce or re-announce Sojourn Coaching publicly with a short, clear launch message', 'owner' => 'both', 'details' => 'Client, Consultant can help draft')
            ), $progress, $editable); ?>

            <?php echo pj_render_objective('phase3', 'D', 'Launch Review & Ongoing Rhythm', 'Close the 90-day buildout with clarity on what\'s next', array(
                array('text' => 'Conduct a 90-day Launch Review Session (what\'s working, what feels heavy, what feels exciting)', 'owner' => 'both'),
                array('text' => 'Decide what should become ongoing habits (weekly review, content creation block, system check)', 'owner' => 'both', 'details' => 'Client, Consultant suggests structure'),
                array('text' => 'Identify 2–3 next-phase improvements (e.g. refine packages, add group program, build more content, expand video presence)', 'owner' => 'both'),
                array('text' => 'Update the roadmap or create a follow-on 90-day plan based on what emerges', 'owner' => 'both', 'details' => 'Consultant drafts, Client approves')
            ), $progress, $editable); ?>
        </div>
    </section>

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
