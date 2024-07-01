(function($) {
    'use strict';

    jQuery(document).ready(function($) {

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
            $('.wp-top-5-settings-form').find('input, select, textarea').on('input change', debounce(function() {
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
            var $notification = $('<div class="wp-top-5-notification ' + type + '">' + message + '</div>');
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
                // Collect all selected checkboxes with the same name
                fieldValue = [];
                $('input[name="' + fieldName + '"]:checked').each(function() {
                    fieldValue.push($(this).val());
                });
            } else {
                fieldValue = $field.val();
            }

            $.ajax({
                url: wp_top_5_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wp_top_5_auto_save',
                    nonce: wp_top_5_admin_vars.wp_top_5_ajax_nonce,
                    field_name: fieldName,
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
            .fail(function(response) {
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
        $(document).on('click', '#generate-top-5-button', function(event) {
            event.preventDefault();

            // Change button text and show spinner
            var $button = $(this);
            $button.prop('disabled', true);
            
            // Apply inline styles to ensure visibility
            var $spinner = $button.find('.wp-top-5-spinner');
            $spinner.css({
                display: 'inline-block',
                width: '16px',
                height: '16px',
                border: '2px solid #f3f3f3',
                borderTop: '2px solid #0073aa',
                borderRadius: '50%',
                animation: 'wp-top-5-spin 1s linear infinite',
                marginRight: '8px'
            });

            var originalText = 'Generate Top 5 Points';
            $button.contents().filter(function() {
                return this.nodeType === 3;
            }).remove();
            $button.append(' Generating...');

            var editorData = getEditorData();

            $.ajax({
                url: wp_top_5_admin_vars.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wp_top_5_gather_content',
                    nonce: wp_top_5_admin_vars.wp_top_5_ajax_nonce,
                    title: editorData.title,
                    tags: editorData.tags || '',
                    content: editorData.content,
                }
            })
            .done(function(response) {
                // Restore button text and hide spinner
                $button.prop('disabled', false);
                $spinner.hide();
                $button.text(originalText);

                if (response.success) {
                    // Set the response data to input fields
                    if (response.data && response.data.points && Array.isArray(response.data.points)) {
                        response.data.points.forEach(function(point) {
                            var inputSelector = '#wp_top_5_points_' + point.index;
                            var inputField = $(inputSelector);

                            if (inputField.length) {
                                inputField.val(point.text).change();

                                // Force re-render with a delay
                                setTimeout(function() {
                                    inputField.val(point.text).trigger('change');
                                }, 100);
                            }
                        });
                    }
                }
            })
            .fail(function(response) {
                // Restore button text and hide spinner
                $button.prop('disabled', false);
                $spinner.hide();
                $button.text(originalText);
            });
        });
        /**
         * Toggle visibility of settings fields based on display mode.
         */
        function toggleSettingsFields() {
            var displayMode = $('#wp_top_5_display_position').val();
            if (displayMode === 'popup') {
                $('#wp_top_5_button_style').closest('tr').show();
                $('#wp_top_5_button_color').closest('tr').show();
                $('#wp_top_5_display_mode').closest('tr').hide();
            } else {
                $('#wp_top_5_button_style').closest('tr').hide();
                $('#wp_top_5_button_color').closest('tr').hide();
                $('#wp_top_5_display_mode').closest('tr').show();
            }
        }

        // Initial toggle based on the current value
        toggleSettingsFields();

        // Toggle fields on change
        $('#wp_top_5_display_position').change(function() {
            toggleSettingsFields();
        });

        // Monitor the API key field for input and paste events
        const apiKeyField = $('input[name="wp_top_5_openai_api_key"]');
        apiKeyField.on('input paste', debounce(function() {
            const apiKey = $(this).val();
            if (apiKey.length === 51) { // OpenAI API key length is 51 characters
                autoSaveField($(this));
                setTimeout(function() {
                    location.reload();
                }, 1000); // Give some time for auto-save to complete before refreshing
            }
        }, 500));

        // Additional handler for checkbox changes
        $(document).on('change', '.wp-top-5-settings-field', function() {
            autoSaveField($(this));
        });

        // Additional handler for checkbox changes
        $(document).on('change', '.wp-top-5-settings-checkbox', function() {
            autoSaveField($(this));
        });

    });

})(jQuery);
