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
        
        // Show loading icon
        $('#loading-icon').show();

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
            $('#loading-icon').hide();
            console.log("AJAX Response:", response);
            if (response.success && Array.isArray(response.data)) {
                response.data.forEach(function(point, index) {
                    // Remove Markdown formatting
                    point = point.replace(/\*\*(.*?)\*\*/g, '$1');
                    
                    var inputSelector = '#wp_top_5_points_' + (index + 1);
                    var inputField = $(inputSelector);
                    
                    console.log("Setting point", index + 1, "to", point, "using selector", inputSelector);
                    
                    if (inputField.length) {
                        inputField.val(point).change();
                        console.log('Input field found and set for point', index + 1);

                        // Force re-render with a delay
                        setTimeout(function() {
                            inputField.val(point).trigger('change');
                        }, 100);
                    } else {
                        console.log('Input field not found for point', index + 1);
                    }
                });
                console.log("Success:", response);
            } else {
                console.log("Failed to generate points:", response);
            }
        })
        .fail(function(response) {
            $('#loading-icon').hide();
            console.log("Error:", response);
        })
        .always(function(response) {
            console.log("Complete:", response);
        });
    });

})(jQuery);
