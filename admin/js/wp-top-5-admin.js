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
                var listHTML = '';

                // Loop through each point, add it to the list, and populate the corresponding input field
                response.data.forEach(function(point, index) {
                    listHTML += '<a href="#" class="list-group-item list-group-item-action">' + point + '</a>';

                    // Populate the input fields. Assumes your input fields have names like wp_top_5_points[1], wp_top_5_points[2], etc.
                    $('input[name="wp_top_5_points[' + (index + 1) + ']"]').val(point);
                });

                $('#top-5-points-list').html(listHTML);

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
