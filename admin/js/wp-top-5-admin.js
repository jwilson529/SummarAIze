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
            var fieldValue = $field.val();
            var fieldName = $field.attr('name');
            console.log('Auto-saving field', fieldName, 'with value', fieldValue);

            $.ajax({
                    url: wp_top_5_admin_vars.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wp_top_5_auto_save',
                        nonce: wp_top_5_admin_vars.wp_top_5_ajax_nonce,
                        field_name: fieldName,
                        field_value: fieldValue
                    },
                    beforeSend: function() {
                        console.log('Sending AJAX request for field', fieldName, 'with value', fieldValue);
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        console.log("Auto-save success:", response.data);
                        showNotification('Field saved successfully.');
                        // showWpSettingsSavedNotification();
                    } else {
                        console.log("Auto-save failed:", response.data);
                        showNotification('Failed to save field.', 'error');
                    }
                })
                .fail(function(response) {
                    console.log("Auto-save error:", response);
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
         * Initialize auto-save for all fields in the settings form.
         */
        function initializeAutoSave() {
            console.log('Initializing auto-save for all fields');
            $('.wp-top-5-settings-form').find('input, select, textarea').on('input change', debounce(function() {
                console.log('Field changed:', $(this).attr('name'));
                autoSaveField($(this));
            }, 500));
        }


        /**
         * Event handler for the Generate Top 5 Points button click.
         * 
         * @param {Event} event The click event.
         */
        $(document).on('click', '#generate-top-5-button', function(event) {
            event.preventDefault();

            // Show loading icon and countdown
            $('#loading-icon').html('<div class="spinner"></div>Generating key points...').show();

            var countdown = 10;
            var countdownInterval = setInterval(function() {
                if (countdown > 0) {
                    $('#loading-icon').html('<div class="spinner"></div>Generating key points... ' + countdown + ' seconds remaining');
                    countdown--;
                } else {
                    clearInterval(countdownInterval);
                    $('#loading-icon').html('<div class="spinner"></div>Generating key points...');
                }
            }, 1000);

            var editorData = getEditorData();
            console.log("Editor Data:", editorData);

            // Get the selected model from the options.
            var selectedModel = wp_top_5_admin_vars.selected_model;
            console.log("Selected Model:", selectedModel);

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
                        model: selectedModel
                    },
                })
                .done(function(response) {
                    // Hide loading icon
                    clearInterval(countdownInterval);
                    $('#loading-icon').hide();
                    console.log("AJAX Response:", response);

                    if (response.success) {
                        console.log("Success:", response.data);
                        // Set the response data to input fields
                        if (response.data && response.data.points && Array.isArray(response.data.points)) {
                            response.data.points.forEach(function(point) {
                                var inputSelector = '#wp_top_5_points_' + point.index;
                                var inputField = $(inputSelector);
                                console.log("Setting point", point.index, "to", point.text, "using selector", inputSelector);

                                if (inputField.length) {
                                    inputField.val(point.text).change();
                                    console.log('Input field found and set for point', point.index);

                                    // Force re-render with a delay
                                    setTimeout(function() {
                                        inputField.val(point.text).trigger('change');
                                    }, 100);
                                } else {
                                    console.log('Input field not found for point', point.index);
                                }
                            });
                        }
                    } else {
                        console.log("Failed:", response.data);
                    }
                })
                .fail(function(response) {
                    // Hide loading icon
                    clearInterval(countdownInterval);
                    $('#loading-icon').hide();
                    console.log("Error:", response);
                })
                .always(function(response) {
                    console.log("Complete:", response);
                });
        });

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

    });




})(jQuery);