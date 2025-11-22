/**
 * Admin Project Settings JavaScript
 * For 30/60/90 Project Journey Plugin
 */

(function($) {
    'use strict';

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        initPurposePoints();
        initFormSubmit();
        initDefaultsButton();
    });

    /**
     * Initialize purpose points repeater
     */
    function initPurposePoints() {
        // Add new purpose point
        $(document).on('click', '#pj-add-purpose-point', function(e) {
            e.preventDefault();
            const template = $('#pj-purpose-point-template').html();
            $('#pj-purpose-points-container').append(template);
        });

        // Remove purpose point
        $(document).on('click', '.pj-remove-point', function(e) {
            e.preventDefault();
            const $row = $(this).closest('.pj-purpose-point-row');

            // Confirm if has value
            const value = $row.find('input').val();
            if (value && !confirm('Remove this purpose point?')) {
                return;
            }

            $row.fadeOut(200, function() {
                $(this).remove();
            });
        });

        // Make purpose points sortable
        $('#pj-purpose-points-container').sortable({
            handle: '.pj-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            axis: 'y',
            cursor: 'grabbing',
            tolerance: 'pointer'
        });
    }

    /**
     * Initialize form submit
     */
    function initFormSubmit() {
        $('#pj-project-settings-form').on('submit', function(e) {
            e.preventDefault();

            const $form = $(this);
            const $saveBtn = $('#pj-save-settings');
            const $status = $('.pj-save-status');

            // Collect form data
            const projectId = $form.find('input[name="project_id"]').val();
            const roadmapTitle = $form.find('input[name="roadmap_title"]').val();
            const roadmapSubtitle = $form.find('textarea[name="roadmap_subtitle"]').val();
            const purposeIntro = $form.find('input[name="purpose_intro"]').val();
            const timelineIntro = $form.find('input[name="timeline_intro"]').val();
            const showTimeline = $form.find('input[name="show_timeline_visual"]').is(':checked') ? 'yes' : 'no';

            // Collect purpose points
            const purposePoints = [];
            $form.find('input[name="purpose_points[]"]').each(function() {
                const value = $(this).val().trim();
                if (value) {
                    purposePoints.push(value);
                }
            });

            // Prepare metadata object
            const metadata = {
                roadmap_title: roadmapTitle,
                roadmap_subtitle: roadmapSubtitle,
                purpose_intro: purposeIntro,
                purpose_points: purposePoints,
                timeline_intro: timelineIntro,
                show_timeline_visual: showTimeline
            };

            // Show loading
            $saveBtn.prop('disabled', true).text('Saving...');
            $status.removeClass('success error').html('');

            // Save via AJAX
            $.ajax({
                url: pjProjectSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_save_project_metadata',
                    nonce: pjProjectSettings.nonce,
                    project_id: projectId,
                    metadata: metadata
                },
                success: function(response) {
                    $saveBtn.prop('disabled', false).text('Save Project Settings');

                    if (response.success) {
                        $status.addClass('success').html(
                            '<span class="dashicons dashicons-yes-alt"></span> Settings saved successfully!'
                        );

                        // Clear status after 3 seconds
                        setTimeout(function() {
                            $status.fadeOut(300, function() {
                                $(this).html('').show();
                            });
                        }, 3000);
                    } else {
                        $status.addClass('error').html(
                            '<span class="dashicons dashicons-warning"></span> Error: ' + response.data.message
                        );
                    }
                },
                error: function() {
                    $saveBtn.prop('disabled', false).text('Save Project Settings');
                    $status.addClass('error').html(
                        '<span class="dashicons dashicons-warning"></span> Connection error. Please try again.'
                    );
                }
            });
        });
    }

    /**
     * Initialize defaults button
     */
    function initDefaultsButton() {
        $('#pj-init-defaults').on('click', function() {
            if (!confirm('Initialize default project settings? This will set standard values for all fields.')) {
                return;
            }

            const $btn = $(this);
            $btn.prop('disabled', true).text('Initializing...');

            $.ajax({
                url: pjProjectSettings.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_init_default_metadata',
                    nonce: pjProjectSettings.nonce,
                    project_id: 1
                },
                success: function(response) {
                    if (response.success) {
                        alert('Default settings initialized successfully!\n\n' + response.data.count + ' settings were created.');
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                        $btn.prop('disabled', false).text('Initialize Default Settings');
                    }
                },
                error: function() {
                    alert('Connection error. Please try again.');
                    $btn.prop('disabled', false).text('Initialize Default Settings');
                }
            });
        });
    }

})(jQuery);
