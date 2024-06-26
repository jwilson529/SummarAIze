(function( $ ) {
	'use strict';


	jQuery(document).ready(function($) {
	    // Open the modal
	    $('.wp-top-5-popup-btn').on('click', function() {
	        $(this).next('.wp-top-5-popup-modal').fadeIn();
	    });

	    // Close the modal
	    $('.wp-top-5-popup-close').on('click', function() {
	        $(this).closest('.wp-top-5-popup-modal').fadeOut();
	    });

	    // Close the modal when clicking outside the content
	    $(window).on('click', function(event) {
	        if ($(event.target).hasClass('wp-top-5-popup-modal')) {
	            $('.wp-top-5-popup-modal').fadeOut();
	        }
	    });
	});



})( jQuery );
