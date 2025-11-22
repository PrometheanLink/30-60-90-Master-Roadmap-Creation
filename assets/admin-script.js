jQuery(document).ready(function($) {
    'use strict';

    // Clear all data confirmation
    $('#pj-clear-all-data').on('click', function(e) {
        e.preventDefault();

        if (!confirm('Are you sure you want to delete all progress data? This action cannot be undone.')) {
            return;
        }

        var $button = $(this);
        var originalText = $button.text();

        // Show loading state
        $button.text('Clearing...').prop('disabled', true);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pj_clear_progress'
            },
            success: function(response) {
                if (response.success) {
                    alert('All progress data has been cleared successfully.');
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || 'Unknown error occurred'));
                    $button.text(originalText).prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                alert('Error clearing data. Please try again.');
                console.error('AJAX error:', status, error);
                $button.text(originalText).prop('disabled', false);
            }
        });
    });

    // Preview report in new window
    $('.pj-preview-report').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        window.open(url, '_blank', 'width=900,height=700,scrollbars=yes');
    });

    // Export to CSV with loading state
    $('.pj-export-csv').on('click', function() {
        var $button = $(this);
        var originalText = $button.text();

        $button.text('Generating...').prop('disabled', true);

        // Re-enable button after 3 seconds
        setTimeout(function() {
            $button.text(originalText).prop('disabled', false);
        }, 3000);
    });

    // Add confirmation to dangerous actions
    $('.pj-dangerous-action').on('click', function(e) {
        if (!confirm('This action may have significant effects. Are you sure you want to continue?')) {
            e.preventDefault();
        }
    });

    // Auto-save settings notification
    var settingsChanged = false;

    $('.pj-settings-form input, .pj-settings-form textarea, .pj-settings-form select').on('change', function() {
        settingsChanged = true;
    });

    $(window).on('beforeunload', function() {
        if (settingsChanged) {
            return 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    $('.pj-settings-form').on('submit', function() {
        settingsChanged = false;
    });

    // Real-time statistics update (if on dashboard)
    function updateDashboardStats() {
        if ($('.pj-admin-dashboard').length === 0) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pj_get_stats'
            },
            success: function(response) {
                if (response.success && response.data) {
                    $('.pj-stat-total-tasks').text(response.data.total_tasks);
                    $('.pj-stat-completed-tasks').text(response.data.completed_tasks);
                    $('.pj-stat-active-users').text(response.data.active_users);
                    $('.pj-stat-progress-percentage').text(response.data.progress_percentage + '%');
                }
            }
        });
    }

    // Update stats every 30 seconds if on dashboard
    if ($('.pj-admin-dashboard').length > 0) {
        setInterval(updateDashboardStats, 30000);
    }

    // Table row hover effects
    $('.wp-list-table tbody tr').hover(
        function() {
            $(this).css('background-color', '#f9f9f9');
        },
        function() {
            $(this).css('background-color', '');
        }
    );

    // Copy shortcode to clipboard
    $('.pj-copy-shortcode').on('click', function(e) {
        e.preventDefault();

        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val($(this).data('shortcode')).select();
        document.execCommand('copy');
        $temp.remove();

        // Show success message
        var $button = $(this);
        var originalText = $button.text();
        $button.text('Copied!');

        setTimeout(function() {
            $button.text(originalText);
        }, 2000);
    });

    // Image upload for logo
    $('#pj-upload-logo').on('click', function(e) {
        e.preventDefault();

        var frame = wp.media({
            title: 'Select or Upload Logo',
            button: {
                text: 'Use this logo'
            },
            multiple: false
        });

        frame.on('select', function() {
            var attachment = frame.state().get('selection').first().toJSON();
            $('#pj_logo_url').val(attachment.url);
            $('.pj-logo-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; margin-top: 10px;">');
        });

        frame.open();
    });

    // Validate email fields
    $('input[type="email"]').on('blur', function() {
        var email = $(this).val();
        var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email && !regex.test(email)) {
            $(this).css('border-color', '#c16107');
            if (!$(this).next('.email-error').length) {
                $(this).after('<p class="email-error" style="color: #c16107; margin-top: 5px;">Please enter a valid email address.</p>');
            }
        } else {
            $(this).css('border-color', '');
            $(this).next('.email-error').remove();
        }
    });

    // Add tooltips
    $('.pj-tooltip').each(function() {
        var $this = $(this);
        var title = $this.attr('title');

        if (title) {
            $this.attr('data-tooltip', title).removeAttr('title');

            $this.hover(
                function() {
                    var tooltip = '<div class="pj-tooltip-popup">' + $(this).data('tooltip') + '</div>';
                    $(this).append(tooltip);
                    $('.pj-tooltip-popup').fadeIn(200);
                },
                function() {
                    $('.pj-tooltip-popup').remove();
                }
            );
        }
    });

    // Add tooltip styles
    if ($('.pj-tooltip').length > 0) {
        $('<style>')
            .text('.pj-tooltip { position: relative; cursor: help; } ' +
                  '.pj-tooltip-popup { position: absolute; bottom: 100%; left: 50%; transform: translateX(-50%); ' +
                  'background: #1e262d; color: white; padding: 8px 12px; border-radius: 4px; font-size: 12px; ' +
                  'white-space: nowrap; margin-bottom: 5px; z-index: 1000; } ' +
                  '.pj-tooltip-popup:after { content: ""; position: absolute; top: 100%; left: 50%; ' +
                  'margin-left: -5px; border-width: 5px; border-style: solid; ' +
                  'border-color: #1e262d transparent transparent transparent; }')
            .appendTo('head');
    }

    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    // Print friendly version
    $('.pj-print-report').on('click', function(e) {
        e.preventDefault();
        window.print();
    });

    // Add print styles
    if ($('.pj-reports-panel').length > 0) {
        $('<style media="print">')
            .text('@media print { ' +
                  '.pj-welcome-panel, .pj-data-actions, .button { display: none !important; } ' +
                  '.wp-list-table { border: 1px solid #000; } ' +
                  '.wp-list-table th { background: #f5f5f5 !important; } ' +
                  '}')
            .appendTo('head');
    }
});
