jQuery(document).ready(function($){
	"use strict";
	var superio_upload;
	var superio_selector;

	function superio_add_file(event, selector) {

		var upload = $(".uploaded-file"), frame;
		var $el = $(this);
		superio_selector = selector;

		event.preventDefault();

		// If the media frame already exists, reopen it.
		if ( superio_upload ) {
			superio_upload.open();
			return;
		} else {
			// Create the media frame.
			superio_upload = wp.media.frames.superio_upload =  wp.media({
				// Set the title of the modal.
				title: "Select Image",

				// Customize the submit button.
				button: {
					// Set the text of the button.
					text: "Selected",
					// Tell the button not to close the modal, since we're
					// going to refresh the page when the image is selected.
					close: false
				}
			});

			// When an image is selected, run a callback.
			superio_upload.on( 'select', function() {
				// Grab the selected attachment.
				var attachment = superio_upload.state().get('selection').first();

				superio_upload.close();
				superio_selector.find('.upload_image').val(attachment.attributes.url).change();
				if ( attachment.attributes.type == 'image' ) {
					superio_selector.find('.superio_screenshot').empty().hide().prepend('<img src="' + attachment.attributes.url + '">').slideDown('fast');
				}
			});

		}
		// Finally, open the modal.
		superio_upload.open();
	}

	function superio_remove_file(selector) {
		selector.find('.superio_screenshot').slideUp('fast').next().val('').trigger('change');
	}
	
	$('body').on('click', '.superio_upload_image_action .remove-image', function(event) {
		superio_remove_file( $(this).parent().parent() );
	});

	$('body').on('click', '.superio_upload_image_action .add-image', function(event) {
		superio_add_file(event, $(this).parent().parent());
	});

});