jQuery(document).ready(function ($) {
    $('#post_type_selector').change(function () {
        let postType = $(this).val();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'fetch_categories_by_post_type',
                post_type: postType
            },
            success: function (response) {
                $('#category_selector').html(response);
            }
        });
    });
    
    $('.upload_arrow_button').click(function(e) {
        e.preventDefault();
        var mediaUploader = wp.media({
            title: 'Choose Arrow Image',
            button: { text: 'Select Image' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#slick_arrow').val(attachment.url);
            $('#slick_arrow_preview').attr('src', attachment.url).show();
        });

        mediaUploader.open();
    });

    $('.remove_arrow_button').click(function() {
        $('#slick_arrow').val(''); // Clear input field
        $('#slick_arrow_preview').hide(); // Hide preview

        // Reapply the default arrow image
        $('#slick_arrow_preview').attr('src', slickSliderOptions.defaultArrow);
    });


    // Show preview if image is already set
    if ($('#slick_arrow').val()) {
        $('#slick_arrow_preview').show();
    } else {
        $('#slick_arrow_preview').hide();
    }
});
