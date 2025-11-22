jQuery(document).ready(function($) {
    'use strict';

    // Handle checkbox clicks
    $('.wormhole-roadmap .checkbox.clickable').on('click', function() {
        var $checkbox = $(this);
        var $item = $checkbox.closest('.checklist-item');
        var taskId = $item.data('task-id');
        var taskText = $item.find('.checklist-text').text();
        var phase = $item.data('phase');
        var objective = $item.data('objective');
        var projectId = $('.wormhole-roadmap').data('project-id') || 1;
        var isCompleted = $item.hasClass('completed');

        // Toggle completed state
        var newState = isCompleted ? 0 : 1;

        // Show loading state
        $checkbox.css('opacity', '0.5');

        // Send AJAX request
        $.ajax({
            url: pjAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pj_save_progress',
                nonce: pjAjax.nonce,
                user_id: pjAjax.userId,
                project_id: projectId,
                task_id: taskId,
                task_text: taskText,
                phase: phase,
                objective: objective,
                completed: newState
            },
            success: function(response) {
                $checkbox.css('opacity', '1');

                if (response.success) {
                    // Toggle the completed class
                    if (newState === 1) {
                        $item.addClass('completed');

                        // Add completion info if provided
                        if (response.data.completed_by) {
                            var completedDate = new Date(response.data.completed_at);
                            var dateStr = completedDate.toLocaleDateString('en-US', {
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            });

                            var $completionInfo = $item.find('.completion-info');
                            if ($completionInfo.length === 0) {
                                $completionInfo = $('<div class="completion-info"></div>');
                                $item.find('.checklist-content').append($completionInfo);
                            }

                            $completionInfo.html(
                                '<small>Completed by ' + escapeHtml(response.data.completed_by) +
                                ' on ' + dateStr + '</small>'
                            );
                        }

                        // Show success animation
                        $checkbox.addClass('animate-success');
                        setTimeout(function() {
                            $checkbox.removeClass('animate-success');
                        }, 600);

                    } else {
                        $item.removeClass('completed');
                        $item.find('.completion-info').remove();
                    }

                    // Update progress statistics if they exist
                    updateProgressStats();

                } else {
                    alert('Error saving progress: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                $checkbox.css('opacity', '1');
                alert('Error saving progress. Please try again.');
                console.error('AJAX error:', status, error);
            }
        });
    });

    // Update progress statistics
    function updateProgressStats() {
        var totalTasks = $('.wormhole-roadmap .checklist-item').length;
        var completedTasks = $('.wormhole-roadmap .checklist-item.completed').length;
        var percentage = totalTasks > 0 ? Math.round((completedTasks / totalTasks) * 100) : 0;

        // Update any progress indicators on the page
        $('.pj-progress-total').text(totalTasks);
        $('.pj-progress-completed').text(completedTasks);
        $('.pj-progress-percentage').text(percentage + '%');
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Add smooth scroll to anchors
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top - 100
            }, 1000);
        }
    });

    // Add animation class for checkboxes
    var style = document.createElement('style');
    style.textContent = `
        .wormhole-roadmap .checkbox.animate-success {
            animation: checkboxSuccess 0.6s ease;
        }
        @keyframes checkboxSuccess {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
    `;
    document.head.appendChild(style);

    // Calculate initial progress
    updateProgressStats();

    // Add phase completion indicators
    function updatePhaseProgress() {
        var phases = ['phase1', 'phase2', 'phase3'];

        phases.forEach(function(phase) {
            var $phaseItems = $('.wormhole-roadmap .checklist-item[data-phase="' + phase + '"]');
            var total = $phaseItems.length;
            var completed = $phaseItems.filter('.completed').length;
            var percentage = total > 0 ? Math.round((completed / total) * 100) : 0;

            // Find the phase marker and add progress indicator
            var phaseNumber = phase.replace('phase', '');
            var $phaseMarker = $('.wormhole-roadmap .phase-marker.phase-' + phaseNumber);

            var $progressIndicator = $phaseMarker.find('.phase-progress-indicator');
            if ($progressIndicator.length === 0) {
                $progressIndicator = $('<div class="phase-progress-indicator"></div>');
                $phaseMarker.append($progressIndicator);
            }

            $progressIndicator.text(percentage + '% Complete');
        });
    }

    // Add phase progress styling
    var phaseStyle = document.createElement('style');
    phaseStyle.textContent = `
        .wormhole-roadmap .phase-progress-indicator {
            font-size: 14px !important;
            color: var(--color-grey-700) !important;
            margin-top: 8px !important;
            font-weight: 600 !important;
        }
    `;
    document.head.appendChild(phaseStyle);

    // Initialize phase progress
    updatePhaseProgress();

    // Re-calculate phase progress when checkboxes change
    $('.wormhole-roadmap .checkbox.clickable').on('click', function() {
        setTimeout(updatePhaseProgress, 500);
    });

    // Add keyboard accessibility
    $('.wormhole-roadmap .checkbox.clickable').attr('tabindex', '0').attr('role', 'checkbox');

    $('.wormhole-roadmap .checkbox.clickable').on('keypress', function(e) {
        if (e.which === 13 || e.which === 32) { // Enter or Space
            e.preventDefault();
            $(this).click();
        }
    });

    // Update ARIA attributes
    $('.wormhole-roadmap .checklist-item').each(function() {
        var $item = $(this);
        var $checkbox = $item.find('.checkbox');
        var isCompleted = $item.hasClass('completed');

        $checkbox.attr('aria-checked', isCompleted ? 'true' : 'false');
    });

    // Update ARIA attributes when checkbox state changes
    $('.wormhole-roadmap .checkbox.clickable').on('click', function() {
        setTimeout(function() {
            $('.wormhole-roadmap .checklist-item').each(function() {
                var $item = $(this);
                var $checkbox = $item.find('.checkbox');
                var isCompleted = $item.hasClass('completed');

                $checkbox.attr('aria-checked', isCompleted ? 'true' : 'false');
            });
        }, 100);
    });

    // ===== ACCORDION & JOURNAL FUNCTIONALITY =====

    // Accordion Toggle
    $('.wormhole-roadmap').on('click', '.accordion-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent triggering checkbox click

        var $toggle = $(this);
        var $item = $toggle.closest('.checklist-item');
        var $content = $item.find('.accordion-content');
        var $icon = $toggle.find('.toggle-icon');
        var $text = $toggle.find('.toggle-text');
        var isExpanded = $toggle.attr('aria-expanded') === 'true';

        if (isExpanded) {
            // Collapse
            $content.slideUp(300);
            $toggle.attr('aria-expanded', 'false');
            $icon.text('+');
            $text.text('Add Journal Note');
        } else {
            // Expand
            $content.slideDown(300);
            $toggle.attr('aria-expanded', 'true');
            $icon.text('−');
            $text.text('Hide Journal');

            // Load existing notes and attachments
            loadTaskJournal($item);
        }
    });

    // Accordion Close Button
    $('.wormhole-roadmap').on('click', '.accordion-close', function(e) {
        e.preventDefault();
        e.stopPropagation();

        var $closeBtn = $(this);
        var $item = $closeBtn.closest('.checklist-item');
        var $content = $item.find('.accordion-content');
        var $toggle = $item.find('.accordion-toggle');
        var $icon = $toggle.find('.toggle-icon');
        var $text = $toggle.find('.toggle-text');

        // Collapse
        $content.slideUp(300);
        $toggle.attr('aria-expanded', 'false');
        $icon.text('+');
        $text.text('Add Journal Note');
    });

    // Load task journal data (notes + attachments)
    function loadTaskJournal($item) {
        var taskId = $item.data('task-id');
        var projectId = $('.wormhole-roadmap').data('project-id') || 1;
        var $historyList = $item.find('.history-list');
        var $attachmentsList = $item.find('.attachments-list');

        // Load notes (revision history)
        $.ajax({
            url: pjAjax.ajaxurl,
            type: 'GET',
            data: {
                action: 'pj_get_task_notes',
                project_id: projectId,
                task_id: taskId
            },
            success: function(response) {
                if (response.success && response.data.notes) {
                    renderRevisionHistory($historyList, response.data.notes, $item);
                } else {
                    $historyList.html('<p class="no-history">No notes yet. Add your first note above.</p>');
                }
            }
        });

        // Load attachments
        $.ajax({
            url: pjAjax.ajaxurl,
            type: 'GET',
            data: {
                action: 'pj_get_task_attachments',
                project_id: projectId,
                task_id: taskId
            },
            success: function(response) {
                if (response.success && response.data.attachments) {
                    renderAttachments($attachmentsList, response.data.attachments);
                } else {
                    $attachmentsList.html('<p class="no-attachments">No attachments yet.</p>');
                }
            }
        });
    }

    // Render revision history
    function renderRevisionHistory($container, notes, $item) {
        if (!notes || notes.length === 0) {
            $container.html('<p class="no-history">No notes yet.</p>');
            return;
        }

        // Load the latest note into the editor
        var latestNote = notes[0];
        $item.find('.journal-textarea').val(latestNote.note_text);
        $item.find('.change-reason-input').val('');

        // Render history
        var html = '';
        notes.forEach(function(note, index) {
            var date = new Date(note.created_at);
            var dateStr = date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            html += '<div class="history-item' + (index === 0 ? ' current-version' : '') + '">';
            html += '<div class="history-header">';
            html += '<strong>Revision #' + escapeHtml(note.revision_number) + '</strong>';
            html += '<span class="history-meta">' + escapeHtml(note.created_by_name) + ' • ' + dateStr + '</span>';
            html += '</div>';
            if (note.change_reason) {
                html += '<div class="history-reason"><em>Reason: ' + escapeHtml(note.change_reason) + '</em></div>';
            }
            html += '<div class="history-content">' + escapeHtml(note.note_text) + '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // Render attachments
    function renderAttachments($container, attachments) {
        if (!attachments || attachments.length === 0) {
            $container.html('<p class="no-attachments">No attachments yet.</p>');
            return;
        }

        var html = '';
        attachments.forEach(function(att) {
            var date = new Date(att.uploaded_at);
            var dateStr = date.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });

            var fileIcon = getFileIcon(att.file_type);

            html += '<div class="attachment-item" data-attachment-id="' + att.id + '">';
            html += '<span class="file-icon">' + fileIcon + '</span>';
            html += '<div class="attachment-info">';
            html += '<a href="' + escapeHtml(att.file_path) + '" target="_blank" class="attachment-name">';
            html += escapeHtml(att.file_name) + '</a>';
            html += '<div class="attachment-meta">';
            html += escapeHtml(att.uploaded_by_name) + ' • ' + dateStr + ' • ' + formatFileSize(att.file_size);
            html += '</div>';
            html += '</div>';
            html += '</div>';
        });

        $container.html(html);
    }

    // Save note
    $('.wormhole-roadmap').on('click', '.button-save-note', function() {
        var $button = $(this);
        var $item = $button.closest('.checklist-item');
        var taskId = $item.data('task-id');
        var projectId = $('.wormhole-roadmap').data('project-id') || 1;
        var $textarea = $item.find('.journal-textarea');
        var $changeReason = $item.find('.change-reason-input');
        var noteText = $textarea.val().trim();
        var changeReason = $changeReason.val().trim();

        if (!noteText) {
            alert('Please enter some notes before saving.');
            $textarea.focus();
            return;
        }

        $button.prop('disabled', true).text('Saving...');

        $.ajax({
            url: pjAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pj_save_task_note',
                nonce: pjAjax.nonce,
                project_id: projectId,
                task_id: taskId,
                note_text: noteText,
                change_reason: changeReason
            },
            success: function(response) {
                $button.prop('disabled', false).text('Save Note');

                if (response.success) {
                    $changeReason.val(''); // Clear change reason
                    loadTaskJournal($item); // Reload journal to show new revision

                    // Show success message
                    var $success = $('<div class="journal-success">✓ Note saved as Revision #' + response.data.revision_number + '</div>');
                    $button.after($success);
                    setTimeout(function() {
                        $success.fadeOut(function() {
                            $(this).remove();
                        });
                    }, 3000);
                } else {
                    alert('Error saving note: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                $button.prop('disabled', false).text('Save Note');
                alert('Error saving note. Please try again.');
            }
        });
    });

    // File upload
    $('.wormhole-roadmap').on('click', '.button-attach-file', function() {
        var $button = $(this);
        var $item = $button.closest('.checklist-item');
        var $fileInput = $item.find('.file-upload-input');

        $fileInput.click();
    });

    $('.wormhole-roadmap').on('change', '.file-upload-input', function() {
        var $input = $(this);
        var $item = $input.closest('.checklist-item');
        var taskId = $item.data('task-id');
        var projectId = $('.wormhole-roadmap').data('project-id') || 1;
        var file = this.files[0];

        if (!file) return;

        var formData = new FormData();
        formData.append('action', 'pj_upload_task_attachment');
        formData.append('nonce', pjAjax.nonce);
        formData.append('project_id', projectId);
        formData.append('task_id', taskId);
        formData.append('file', file);

        var $button = $item.find('.button-attach-file');
        $button.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Uploading...');

        $.ajax({
            url: pjAjax.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-paperclip"></span> Attach File');
                $input.val(''); // Clear input

                if (response.success) {
                    loadTaskJournal($item); // Reload to show new attachment
                } else {
                    alert('Error uploading file: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                $button.prop('disabled', false).html('<span class="dashicons dashicons-paperclip"></span> Attach File');
                $input.val('');
                alert('Error uploading file. Please try again.');
            }
        });
    });

    // Helper functions
    function getFileIcon(mimeType) {
        if (!mimeType) return '📄';
        if (mimeType.includes('image')) return '🖼️';
        if (mimeType.includes('pdf')) return '📕';
        if (mimeType.includes('word') || mimeType.includes('document')) return '📘';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return '📊';
        if (mimeType.includes('video')) return '🎥';
        if (mimeType.includes('zip') || mimeType.includes('compressed')) return '📦';
        return '📄';
    }

    function formatFileSize(bytes) {
        if (!bytes) return '0 B';
        var k = 1024;
        var sizes = ['B', 'KB', 'MB', 'GB'];
        var i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
    }

    // Add spinner animation for upload
    var spinStyle = document.createElement('style');
    spinStyle.textContent = `
        .dashicons.spin {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(spinStyle);
});
