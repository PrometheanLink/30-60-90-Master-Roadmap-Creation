/**
 * Admin Phases Management JavaScript
 * For 30/60/90 Project Journey Plugin
 */

(function($) {
    'use strict';

    // State management
    let editingElement = null;
    let originalContent = '';

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        initAccordion();
        initDragDrop();
        initInlineEditing();
        initButtons();
        initImport();
    });

    /**
     * Initialize accordion toggle
     */
    function initAccordion() {
        $(document).on('click', '.pj-toggle-phase', function(e) {
            e.stopPropagation();
            const phaseCard = $(this).closest('.pj-phase-card');
            phaseCard.toggleClass('collapsed');
        });

        // Also toggle on header click (but not when clicking buttons)
        $(document).on('click', '.pj-phase-header', function(e) {
            if (!$(e.target).closest('.pj-phase-actions, .pj-phase-title-area [contenteditable]').length) {
                const phaseCard = $(this).closest('.pj-phase-card');
                phaseCard.toggleClass('collapsed');
            }
        });
    }

    /**
     * Initialize drag and drop sorting
     */
    function initDragDrop() {
        // Sort phases
        $('.pj-phases-container').sortable({
            handle: '.pj-phase-card > .pj-phase-header > .pj-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            axis: 'y',
            update: function(event, ui) {
                const order = $(this).sortable('toArray', { attribute: 'data-phase-id' });
                reorderItems('phases', order);
            }
        });

        // Sort objectives within each phase
        $('.pj-objectives-container').sortable({
            handle: '.pj-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            connectWith: '.pj-objectives-container',
            update: function(event, ui) {
                const order = $(this).sortable('toArray', { attribute: 'data-objective-id' });
                if (order.length > 0) {
                    reorderItems('objectives', order);
                }
            }
        });

        // Sort tasks within each objective
        $('.pj-tasks-container').sortable({
            handle: '.pj-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            connectWith: '.pj-tasks-container',
            update: function(event, ui) {
                const order = $(this).sortable('toArray', { attribute: 'data-task-id' });
                if (order.length > 0) {
                    reorderItems('tasks', order);
                }
            }
        });
    }

    /**
     * Initialize inline editing
     */
    function initInlineEditing() {
        // Edit buttons
        $(document).on('click', '.pj-edit-phase', function(e) {
            e.stopPropagation();
            const phaseCard = $(this).closest('.pj-phase-card');
            // Only edit direct children, not nested objectives/tasks
            const $editableFields = phaseCard.children('.pj-phase-header').find('[contenteditable]');
            enableEditing($editableFields);
        });

        $(document).on('click', '.pj-edit-objective', function(e) {
            e.stopPropagation();
            const objectiveCard = $(this).closest('.pj-objective-card');
            // Only edit objective fields, not nested tasks
            const $editableFields = objectiveCard.children('.pj-objective-header').find('[contenteditable]');
            enableEditing($editableFields);
        });

        $(document).on('click', '.pj-edit-task', function(e) {
            e.stopPropagation();
            const taskItem = $(this).closest('.pj-task-item');
            // Only edit this specific task
            const $editableFields = taskItem.find('[contenteditable]').not(function() {
                return $(this).closest('.pj-task-item').get(0) !== taskItem.get(0);
            });
            enableEditing($editableFields);
        });

        // Handle Enter key to save
        $(document).on('keydown', '[contenteditable="true"]', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                $(this).blur();
            }
            if (e.key === 'Escape') {
                cancelEditing(editingElement);
            }
        });

        // Save on blur
        $(document).on('blur', '[contenteditable="true"]', function() {
            // Only save if this element is part of the currently editing set
            if (editingElement && editingElement.filter(this).length > 0) {
                saveInlineEdit($(this));
            }
        });
    }

    /**
     * Enable editing mode
     */
    function enableEditing($elements) {
        if (editingElement) {
            cancelEditing(editingElement);
        }

        $elements.each(function() {
            const $el = $(this);
            $el.attr('contenteditable', 'true');
            $el.addClass('pj-editing');
        });

        $elements.first().focus();
        editingElement = $elements;
        originalContent = $elements.map(function() {
            return $(this).text();
        }).get();
    }

    /**
     * Cancel editing
     */
    function cancelEditing($elements) {
        $elements.each(function(index) {
            const $el = $(this);
            $el.attr('contenteditable', 'false');
            $el.removeClass('pj-editing');
            if (originalContent[index]) {
                $el.text(originalContent[index]);
            }
        });
        editingElement = null;
        originalContent = '';
    }

    /**
     * Save inline edit
     */
    function saveInlineEdit($element) {
        const $container = $element.closest('[data-phase-id], [data-objective-id], [data-task-id]');
        const itemType = $container.data('phase-id') ? 'phase' :
                         $container.data('objective-id') ? 'objective' : 'task';
        const itemId = $container.data('phase-id') || $container.data('objective-id') || $container.data('task-id');

        // Build data object with correct ID field name for backend
        const data = {};
        if (itemType === 'phase') {
            data.phase_id = itemId;
        } else if (itemType === 'objective') {
            data.objective_id = itemId;
            // Objectives also need phase_id and objective_key
            const $phaseCard = $container.closest('.pj-phase-card');
            data.phase_id = $phaseCard.data('phase-id');
            data.objective_key = $container.find('.pj-objective-letter').text().trim();
        } else if (itemType === 'task') {
            data.task_db_id = itemId;
            // Tasks also need phase_id, objective_id, task_id, and owner
            const $phaseCard = $container.closest('.pj-phase-card');
            const $objectiveCard = $container.closest('.pj-objective-card');
            data.phase_id = $phaseCard.data('phase-id');
            data.objective_id = $objectiveCard.data('objective-id');
            data.task_id = $container.find('.pj-task-id').text().trim();
            data.owner = $container.find('.pj-task-owner').text().trim() || 'both';
        }

        // Collect all editable fields in the container
        $container.find('[contenteditable="true"]').each(function() {
            const field = $(this).data('field');
            data[field] = $(this).text().trim();
        });

        // Disable editing
        $container.find('[contenteditable="true"]').attr('contenteditable', 'false').removeClass('pj-editing');
        editingElement = null;

        // Save via AJAX
        const action = 'pj_save_' + itemType;
        showLoading();

        $.ajax({
            url: pjPhasesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: action,
                nonce: pjPhasesAjax.nonce,
                ...data
            },
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showNotification('Saved successfully!', 'success');
                } else {
                    showNotification('Error: ' + response.data.message, 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotification('Connection error. Please try again.', 'error');
            }
        });
    }

    /**
     * Initialize buttons
     */
    function initButtons() {
        // Add Phase
        $(document).on('click', '#pj-add-phase', function() {
            // Count existing phases to determine next phase number
            const existingPhases = $('.pj-phase-card').length;
            const nextPhaseNumber = existingPhases + 1;
            const nextPhaseKey = 'phase' + nextPhaseNumber;

            const phaseTitle = prompt('Enter phase title:', 'Phase ' + nextPhaseNumber + ': Days ' + ((nextPhaseNumber - 1) * 30 + 1) + '-' + (nextPhaseNumber * 30));
            if (!phaseTitle) return;

            const phaseSubtitle = prompt('Enter phase subtitle:', 'Extended Implementation');
            const phaseDescription = prompt('Enter phase description (optional):', '');

            showLoading();
            $.ajax({
                url: pjPhasesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_save_phase',
                    nonce: pjPhasesAjax.nonce,
                    phase_key: nextPhaseKey,
                    phase_number: nextPhaseNumber,
                    phase_title: phaseTitle,
                    phase_subtitle: phaseSubtitle,
                    phase_description: phaseDescription,
                    project_id: 1
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        location.reload();
                    } else {
                        showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Connection error. Please try again.', 'error');
                }
            });
        });

        // Add Objective
        $(document).on('click', '.pj-add-objective', function() {
            const phaseId = $(this).data('phase-id');
            const $phaseCard = $(this).closest('.pj-phase-card');

            // Count existing objectives in this phase to determine next letter
            const existingObjectives = $phaseCard.find('.pj-objective-card').length;
            const nextLetter = String.fromCharCode(65 + existingObjectives); // 65 = 'A'

            const objectiveTitle = prompt('Enter objective title:', 'Objective ' + nextLetter + ': New Goal');
            if (!objectiveTitle) return;

            const objectiveSubtitle = prompt('Enter objective subtitle (optional):', '');

            showLoading();
            $.ajax({
                url: pjPhasesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_save_objective',
                    nonce: pjPhasesAjax.nonce,
                    phase_id: phaseId,
                    objective_key: nextLetter,
                    objective_title: objectiveTitle,
                    objective_subtitle: objectiveSubtitle,
                    project_id: 1
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        location.reload();
                    } else {
                        showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Connection error. Please try again.', 'error');
                }
            });
        });

        // Add Task
        $(document).on('click', '.pj-add-task', function() {
            const $objectiveCard = $(this).closest('.pj-objective-card');
            const $phaseCard = $(this).closest('.pj-phase-card');

            const objectiveId = $objectiveCard.data('objective-id');
            const phaseId = $phaseCard.data('phase-id');

            const taskText = prompt('Enter task text:', 'New task description');
            if (!taskText) return;

            const ownerInput = prompt('Enter task owner (Client, Consultant, or Both):', 'Client');
            // Normalize owner to lowercase to match CSS classes (owner-client, owner-consultant, owner-both)
            const owner = ownerInput ? ownerInput.toLowerCase() : 'both';

            // Generate unique task_id (timestamp-based to ensure uniqueness)
            const taskId = 'task-' + Date.now();

            showLoading();
            $.ajax({
                url: pjPhasesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_save_task',
                    nonce: pjPhasesAjax.nonce,
                    phase_id: phaseId,
                    objective_id: objectiveId,
                    task_id: taskId,
                    task_text: taskText,
                    owner: owner,
                    project_id: 1
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        location.reload();
                    } else {
                        showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Connection error. Please try again.', 'error');
                }
            });
        });

        // Delete Phase
        $(document).on('click', '.pj-delete-phase', function(e) {
            e.stopPropagation();
            if (!confirm('Are you sure you want to delete this phase? This will also delete all objectives and tasks within it.')) {
                return;
            }

            const phaseId = $(this).closest('.pj-phase-card').data('phase-id');
            deleteItem('phase', phaseId);
        });

        // Delete Objective
        $(document).on('click', '.pj-delete-objective', function(e) {
            e.stopPropagation();
            if (!confirm('Are you sure you want to delete this objective? This will also delete all tasks within it.')) {
                return;
            }

            const objectiveId = $(this).closest('.pj-objective-card').data('objective-id');
            deleteItem('objective', objectiveId);
        });

        // Delete Task
        $(document).on('click', '.pj-delete-task', function(e) {
            e.stopPropagation();
            if (!confirm('Are you sure you want to delete this task?')) {
                return;
            }

            const taskId = $(this).closest('.pj-task-item').data('task-id');
            deleteItem('task', taskId);
        });

        // Export JSON
        $(document).on('click', '#pj-export-json', function() {
            showLoading();
            $.ajax({
                url: pjPhasesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_get_roadmap',
                    nonce: pjPhasesAjax.nonce,
                    project_id: 1
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        const dataStr = JSON.stringify(response.data, null, 2);
                        const dataBlob = new Blob([dataStr], { type: 'application/json' });
                        const url = URL.createObjectURL(dataBlob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = 'roadmap-export.json';
                        link.click();
                        showNotification('Roadmap exported successfully!', 'success');
                    } else {
                        showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /**
     * Initialize import functionality
     */
    function initImport() {
        $(document).on('click', '#pj-import-roadmap', function() {
            if (!confirm('This will import the hardcoded roadmap into the database. Continue?')) {
                return;
            }

            showLoading();
            $.ajax({
                url: pjPhasesAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'pj_import_roadmap',
                    nonce: pjPhasesAjax.nonce,
                    project_id: 1
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        const stats = response.data.stats;
                        alert('Import successful!\n\nPhases: ' + stats.phases + '\nObjectives: ' + stats.objectives + '\nTasks: ' + stats.tasks);
                        location.reload();
                    } else {
                        showNotification('Error: ' + response.data.message, 'error');
                    }
                },
                error: function() {
                    hideLoading();
                    showNotification('Connection error. Please try again.', 'error');
                }
            });
        });
    }

    /**
     * Delete item via AJAX
     */
    function deleteItem(itemType, itemId) {
        showLoading();

        // Map item type to correct parameter name expected by PHP handler
        const paramName = {
            'phase': 'phase_id',
            'objective': 'objective_id',
            'task': 'task_db_id'
        }[itemType];

        const ajaxData = {
            action: 'pj_delete_' + itemType,
            nonce: pjPhasesAjax.nonce
        };
        ajaxData[paramName] = itemId;

        $.ajax({
            url: pjPhasesAjax.ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showNotification('Deleted successfully!', 'success');
                    location.reload();
                } else {
                    showNotification('Error: ' + response.data.message, 'error');
                }
            },
            error: function() {
                hideLoading();
                showNotification('Connection error. Please try again.', 'error');
            }
        });
    }

    /**
     * Reorder items via AJAX
     */
    function reorderItems(itemType, order) {
        $.ajax({
            url: pjPhasesAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'pj_reorder_items',
                nonce: pjPhasesAjax.nonce,
                item_type: itemType,
                order: order
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Order updated!', 'success');
                }
            }
        });
    }

    /**
     * Show loading overlay
     */
    function showLoading() {
        $('#pj-loading-overlay').fadeIn(200);
    }

    /**
     * Hide loading overlay
     */
    function hideLoading() {
        $('#pj-loading-overlay').fadeOut(200);
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        type = type || 'success';
        const $notification = $('<div class="pj-notification ' + type + '">' + message + '</div>');
        $('body').append($notification);

        setTimeout(function() {
            $notification.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

})(jQuery);
