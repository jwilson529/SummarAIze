(function($) {
    'use strict';

    $(document).ready(function() {

        $(document).on('click', '#summariaze_create_assistant', function(event) {
            event.preventDefault();
            console.log('Button clicked'); // Debugging line
            $('#summaraize_assistant_id').val('');
            
            // Manually trigger auto-save for the Assistant ID field
            autoSaveField($('#summaraize_assistant_id'));

            // Refresh the page after a short delay to ensure auto-save completes
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        });

        /**
         * Get the editor data from either the Gutenberg or Classic editor.
         * 
         * @return {Object} The editor data including title, content, and tags.
         */
        function getEditorData() {
            var title, content, tags;

            if ($('#editor').length) {
                // Gutenberg editor
                title = wp.data.select('core/editor').getEditedPostAttribute('title');
                content = wp.data.select('core/editor').getEditedPostContent();
                var tagArray = wp.data.select('core/editor').getEditedPostAttribute('tags');
                tags = Array.isArray(tagArray) ? tagArray.join(', ') : '';
            } else {
                // Classic editor
                title = $('input#title').val();
                content = $('textarea#content').val();
                tags = $('input[name="tax_input[post_tag]"]').val();
            }

            return { title, content, tags };
        }

        /**
         * Initialize auto-save for all fields in the settings form.
         */
        function initializeAutoSave() {
            $('.summaraize-settings-form').find('input, select, textarea').on('input change', debounce(function() {
                autoSaveField($(this));
            }, 500));
        }

        initializeAutoSave();

        /**
         * Show a popup notification.
         * 
         * @param {String} message The message to display.
         * @param {String} type The type of notification (success, error).
         */
        function showNotification(message, type = 'success') {
            var $notification = $('<div class="summaraize-notification ' + type + '">' + message + '</div>');
            $('body').append($notification);
            $notification.fadeIn('fast');

            setTimeout(function() {
                $notification.fadeOut('slow', function() {
                    $notification.remove();
                });
            }, 2000);
        }

        /**
         * Show the WordPress settings saved notification.
         */
        function showWpSettingsSavedNotification() {
            var $notification = $(
                '<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible">' +
                '<p><strong>Settings saved.</strong></p>' +
                '<button type="button" class="notice-dismiss">' +
                '<span class="screen-reader-text">Dismiss this notice.</span>' +
                '</button></div>'
            );
            $('.wrap').prepend($notification);

            // Automatically dismiss the notice after a few seconds
            setTimeout(function() {
                $notification.fadeOut('slow', function() {
                    $notification.remove();
                });
            }, 4000);
        }

        /**
         * Auto-save function for input field changes.
         * 
         * @param {Object} $field The jQuery object for the field.
         */
        function autoSaveField($field) {
            var fieldValue;
            var fieldName = $field.attr('name');

            // Handle checkboxes
            if ($field.attr('type') === 'checkbox') {
                fieldValue = [];
                $('input[name="' + fieldName + '"]:checked').each(function() {
                    fieldValue.push($(this).val());
                });
            } else {
                fieldValue = $field.val();
            }

            $.ajax({
                    url: summaraize_admin_vars.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'summaraize_auto_save',
                        nonce: summaraize_admin_vars.summaraize_ajax_nonce,
                        field_name: fieldName.replace('[]', ''), // Remove [] for the option name
                        field_value: fieldValue
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        showNotification('Field saved successfully.');
                        if (response.data.refresh) {
                            location.reload();
                        }
                    } else {
                        showNotification('Failed to save field.', 'error');
                    }
                })
                .fail(function() {
                    showNotification('Error saving field.', 'error');
                });
        }

        /**
         * Debounce function to limit the rate at which a function can fire.
         * 
         * @param {Function} func The function to debounce.
         * @param {Number} wait The time to wait before executing the function.
         * @returns {Function} The debounced function.
         */
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }

        /**
         * Event handler for the Generate Top 5 Points button click.
         * 
         * @param {Event} event The click event.
         */
        $(document).on('click', '#generate-summaraize-button', function(event) {
            event.preventDefault();
            console.log('Button clicked');

            var $button = $(this);
            $button.prop('disabled', true);
            console.log('Button disabled');

            var $spinner = $button.find('.summaraize-spinner');
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
            console.log('Spinner displayed');

            var originalText = 'Generate Top 5 Points';
            $button.contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
            $button.append(' Generating...');
            console.log('Button text changed to "Generating..."');

            var editorData = getEditorData();
            console.log('Editor data:', editorData);

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
                    console.log('AJAX request successful', response);
                    $button.prop('disabled', false);
                    $spinner.hide();
                    $button.text(originalText);

                    if (response.success) {
                        if (response.data && response.data.points && Array.isArray(response.data.points)) {
                            response.data.points.forEach(function(point) {
                                var inputSelector = '#summaraize_points_' + point.index;
                                var inputField = $(inputSelector);

                                if (inputField.length) {
                                    inputField.val(point.text).change();
                                    setTimeout(function() {
                                        inputField.val(point.text).trigger('change');
                                    }, 100);
                                }
                            });
                        }
                    } else {
                        if (response.data && response.data.data === 'Assistant ID is not configured.') {
                            alert('Assistant ID is not configured. Please set it in the plugin settings.');
                        }
                    }
                })
                .fail(function(response) {
                    console.log('AJAX request failed', response);
                    $button.prop('disabled', false);
                    $spinner.hide();
                    $button.text(originalText);
                });
        });

        /**
         * Toggle visibility of settings fields based on display mode.
         */
        function toggleSettingsFields() {
            var displayMode = $('#summaraize_display_position').val();
            if (displayMode === 'popup') {
                $('#summaraize_button_style').closest('tr').show();
                $('#summaraize_button_color').closest('tr').show();
                $('#summaraize_display_mode').closest('tr').hide();
            } else {
                $('#summaraize_button_style').closest('tr').hide();
                $('#summaraize_button_color').closest('tr').hide();
                $('#summaraize_display_mode').closest('tr').show();
            }
        }

        // Initial toggle based on the current value
        toggleSettingsFields();

        // Toggle fields on change
        $('#summaraize_display_position').change(function() {
            toggleSettingsFields();
        });

        /**
         * Toggle visibility of override settings fields based on view mode.
         */
        function toggleOverrideFields() {
            var displayMode = $('#summaraize_view').val();
            if (displayMode === 'popup') {
                $('.button-style-wrapper').show();
                $('.button-style-description').show();
                $('.button-color-wrapper').show();
                $('.button-color-description').show();
            } else {
                $('.button-style-wrapper').hide();
                $('.button-style-description').hide();
                $('.button-color-wrapper').hide();
                $('.button-color-description').hide();
            }
        }

        // Initial toggle based on the current value
        toggleOverrideFields();

        // Toggle fields on change
        $('#summaraize_view').change(function() {
            toggleOverrideFields();
        });

        $('#summaraize_override_settings').change(function() {
            if ($(this).is(':checked')) {
                $('#summaraize_override_options').show();
            } else {
                $('#summaraize_override_options').hide();
            }
        });

        /**
         * Monitor the API key field for input and paste events.
         */
        const apiKeyField = $('input[name="summaraize_openai_api_key"]');
        apiKeyField.on('input paste', debounce(function() {
            const apiKey = $(this).val();
            if (apiKey.length === 51) { // OpenAI API key length is 51 characters
                autoSaveField($(this));
                setTimeout(function() {
                    location.reload();
                }, 1000); // Give some time for auto-save to complete before refreshing
            }
        }, 500));

        /**
         * Additional handler for checkbox changes.
         */
        $(document).on('change', '.summaraize-settings-field', function() {
            autoSaveField($(this));
        });

        /**
         * Additional handler for checkbox changes.
         */
        $(document).on('change', '.summaraize-settings-checkbox', function() {
            autoSaveField($(this));
        });
    });

})(jQuery);