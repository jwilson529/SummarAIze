(function($) {
    'use strict';

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

})(jQuery);
