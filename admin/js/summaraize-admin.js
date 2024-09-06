(function($) {
    'use strict';

    $(document).ready(function() {

        // Regenerate Assistant ID
        $(document).on('click', '#summariaze_create_assistant', function(event) {
            event.preventDefault();

            // Clear the Assistant ID field
            $('#summaraize_assistant_id').val('');

            // Trigger auto-save for the Assistant ID field
            autoSaveField($('#summaraize_assistant_id'));

            // Reload the page after a short delay to ensure the changes are reflected
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        });

        // Remove a point from the list when the trashcan icon is clicked
        $('.remove-point').click(function() {
            var pointInputId = $(this).data('point-id');
            $('#' + pointInputId).val(''); // Clear the input field

            // Update the sorted points to reflect the removal
            updateSortedPoints();
        });

        // Function to update the hidden field with the current order of points
        function updateSortedPoints() {
            var sortedPoints = [];
            $('#summaraize-points-list input[type="text"]').each(function() {
                sortedPoints.push($(this).val()); // Push even empty values
            });

            // Store the sorted order (including empty values) in a hidden field
            $('#summaraize_points_sorted').val(JSON.stringify(sortedPoints));
        }

        // Make points list sortable if it exists
        if ($('#summaraize-points-list').length) {
            $('#summaraize-points-list').sortable({
                handle: ".dashicons-menu", // Handle for dragging items
                animation: 150, // Animation speed for sorting
                stop: function() {
                    // This will update the input fields with the correct order after sorting.
                    var sortedPoints = [];
                    $('#summaraize-points-list input[type="text"]').each(function() {
                        sortedPoints.push($(this).val());
                    });

                    // Store the sorted order in a hidden field to be submitted with the form.
                    $('#summaraize_points_sorted').val(JSON.stringify(sortedPoints));
                }
            });
        }


        // Handle tab switching functionality
        $('.nav-tab-wrapper a').click(function(e) {
            e.preventDefault();
            $('.nav-tab-wrapper a').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');

            // Show the selected tab and hide others
            $('.tab-content').hide();
            $($(this).attr('href')).show();
        });

        // Set the initial tab visibility
        $('#main-settings').show();
        $('#advanced-settings').hide();

        // Show or hide the custom prompt field based on dropdown selection
        $('#summaraize_prompt_type').on('change', function() {
            var selectedValue = $(this).val();
            if (selectedValue === 'custom') {
                $('#summaraize_custom_prompt_row').show(); // Show custom prompt row
                $('#summaraize_custom_prompt_custom').css('display', 'block'); // Show custom textarea
            } else {
                $('#summaraize_custom_prompt_row').hide(); // Hide custom prompt row
                $('#summaraize_custom_prompt_custom').val(''); // Clear the value
                autoSaveField($('#summaraize_custom_prompt_custom')); // Auto-save the cleared value
            }
        });

        // Trigger change event on load to initialize the state
        $('#summaraize_prompt_type').trigger('change');

        // Function to get editor data from Gutenberg or Classic editor
        function getEditorData() {
            var title, content, tags;

            if ($('#editor').length) {
                // Get data from Gutenberg editor
                title = wp.data.select('core/editor').getEditedPostAttribute('title');
                content = wp.data.select('core/editor').getEditedPostContent();
                tags = wp.data.select('core/editor').getEditedPostAttribute('tags').join(', ');
            } else {
                // Get data from Classic editor
                title = $('input#title').val();
                content = $('textarea#content').val();
                tags = $('input[name="tax_input[post_tag]"]').val();
            }

            return { title, content, tags };
        }

        // Function to auto-save field values
        function autoSaveField($field, value = null) {
            var fieldName, fieldValue;

            if (typeof $field === 'string') {
                // If $field is a string, use it as the field name and value
                fieldName = $field;
                fieldValue = value;
            } else {
                // If $field is a jQuery object, handle normally
                $field = $($field);
                fieldName = $field.attr('name');

                // Handle checkboxes separately
                if ($field.attr('type') === 'checkbox') {
                    fieldValue = [];
                    $('input[name="' + fieldName + '"]:checked').each(function() {
                        fieldValue.push($(this).val());
                    });
                } else {
                    fieldValue = $field.val();
                }
            }

            // Perform AJAX to save the field value
            $.ajax({
                url: summaraize_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'summaraize_auto_save',
                    nonce: summaraize_admin_vars.summaraize_ajax_nonce,
                    post_id: summaraize_admin_vars.post_id, // Ensure post_id is available
                    field_name: fieldName,
                    field_value: fieldValue
                }
            })
            .done(function(response) {
                if (response.success) {
                    showNotification('Field saved successfully.');
                } else {
                    showNotification(response.data.message, 'error');
                }
            })
            .fail(function() {
                showNotification('Error saving field.', 'error');
            });
        }

        // Initialize auto-save functionality on form field changes
        function initializeAutoSave() {
            $('.summaraize-settings-form').find('input, select, textarea').on('input change', debounce(function() {
                var $field = $(this);
                var fieldName = $field.attr('name');
                var fieldValue;

                // Handle checkboxes separately
                if ($field.attr('type') === 'checkbox') {
                    fieldValue = [];
                    $('input[name="' + fieldName + '"]:checked').each(function() {
                        fieldValue.push($(this).val());
                    });
                } else {
                    fieldValue = $field.val();
                }

                autoSaveField(fieldName, fieldValue);
            }, 500));
        }

        initializeAutoSave(); // Call to initialize auto-save

        // Function to show notification popups
        function showNotification(message, type = 'success') {
            var $notification = $('<div class="summaraize-notification ' + type + '">' + message + '</div>');
            $('body').append($notification);
            $notification.fadeIn('fast');

            // Fade out the notification after 2 seconds
            setTimeout(function() {
                $notification.fadeOut('slow', function() {
                    $notification.remove();
                });
            }, 2000);
        }

        // Handle "Generate Top 5 Points" button click
        $(document).on('click', '#generate-summaraize-button', function(event) {
            event.preventDefault();

            var $button = $(this);
            $button.prop('disabled', true); // Disable the button while processing
            var $spinner = $button.find('.summaraize-spinner');

            // Show the loading spinner
            $spinner.css({
                display: 'inline-block',
                width: '16px',
                height: '16px',
                border: '2px solid #f3f3f3',
                borderTop: '2px solid #0073aa',
                borderRadius: '50%',
                animation: 'summaraize-spin 1s linear infinite',
                marginRight: '8px'
            });

            var editorData = getEditorData(); // Gather editor data

            // AJAX request to gather content and generate top 5 points
            $.ajax({
                url: summaraize_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'summaraize_gather_content',
                    nonce: summaraize_admin_vars.summaraize_ajax_nonce,
                    title: editorData.title,
                    tags: editorData.tags || '',
                    content: editorData.content,
                }
            })
            .done(function(response) {
                $button.prop('disabled', false); // Re-enable the button
                $spinner.hide(); // Hide the spinner
                $button.text('Generate Top 5 Points');

                // Populate the points if successful
                if (response.success && response.data.points) {
                    response.data.points.forEach(function(point) {
                        var inputField = $('#summaraize_points_' + point.index);
                        if (inputField.length) {
                            inputField.val(point.text).change();
                        }
                    });
                }
            })
            .fail(function() {
                $button.prop('disabled', false); // Re-enable the button
                $spinner.hide(); // Hide the spinner
                $button.text('Generate Top 5 Points');
            });
        });

        // Toggle settings fields based on display mode
        function toggleSettingsFields() {
            var displayMode = $('#summaraize_display_position').val();
            if (displayMode === 'popup') {
                $('#summaraize_button_style, #summaraize_button_color').closest('tr').show();
                $('#summaraize_display_mode').closest('tr').hide();
            } else {
                $('#summaraize_button_style, #summaraize_button_color').closest('tr').hide();
                $('#summaraize_display_mode').closest('tr').show();
            }
        }

        toggleSettingsFields(); // Initial toggle on page load
        $('#summaraize_display_position').change(toggleSettingsFields); // Toggle fields on dropdown change

        // Toggle override settings fields based on view mode
        function toggleOverrideFields() {
            var displayMode = $('#summaraize_view').val();
            if (displayMode === 'popup') {
                $('.button-style-wrapper, .button-style-description, .button-color-wrapper, .button-color-description').show();
            } else {
                $('.button-style-wrapper, .button-style-description, .button-color-wrapper, .button-color-description').hide();
            }
        }

        toggleOverrideFields(); // Initial toggle on page load
        $('#summaraize_view').change(toggleOverrideFields); // Toggle fields on dropdown change

        // Toggle visibility of override options based on checkbox
        $('#summaraize_override_settings').change(function() {
            $('#summaraize_override_options').toggle($(this).is(':checked'));
        });

        // Add a spinner with a message below the input field
        function addSpinnerWithMessage($field, message) {
            $field.siblings('.summaraize-spinner-container').remove(); // Remove any existing spinner

            // Create the spinner container and append the spinner and message
            const spinnerContainer = $('<div class="summaraize-spinner-container"></div>');
            const spinner = $('<div class="summaraize-spinner"></div>');
            const spinnerMessage = $('<span class="summaraize-spinner-message"></span>').text(message);

            spinnerContainer.append(spinner).append(spinnerMessage);
            $field.after(spinnerContainer); // Insert after the input field
            spinnerContainer.fadeIn('fast'); // Show the spinner container
        }

        // Remove the spinner and message from the input field
        function removeSpinnerWithMessage($field) {
            $field.siblings('.summaraize-spinner-container').fadeOut('slow', function() {
                $(this).remove();
            });
        }

        // Validate API key with debounce
        const apiKeyField = $('input[name="summaraize_openai_api_key"]');
        apiKeyField.on('input paste', debounce(function() {
            const apiKey = $(this).val();

            addSpinnerWithMessage(apiKeyField, 'Validating API key...');

            // Perform AJAX request to validate API key
            $.ajax({
                url: summaraize_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'summaraize_ajax_validate_openai_api_key',
                    nonce: summaraize_admin_vars.summaraize_ajax_nonce,
                    api_key: apiKey
                }
            })
            .done(function(validationResponse) {
                if (validationResponse.success) {
                    autoSaveField(apiKeyField); // Auto-save the key
                    setTimeout(function() {
                        location.reload(); // Refresh the page after saving
                    }, 1000);
                } else {
                    showNotification(validationResponse.data.message || 'Invalid API key.', 'error');
                }
            })
            .fail(function() {
                showNotification('Error validating API key.', 'error');
            })
            .always(function() {
                removeSpinnerWithMessage(apiKeyField); // Remove spinner after validation
            });
        }, 500));

        // Auto-save settings fields on change or input
        $(document).on('input change', '.summaraize-settings-field', function() {
            autoSaveField($(this));
        });

        // Debounce function to limit the rate of function execution
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

    });
})(jQuery);
