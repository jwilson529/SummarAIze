(function( $ ) {
	'use strict';

	jQuery(document).ready(function($) {
	    $('.wp-top-5-header').on('click', function() {
	        var $list = $('.wp-top-5-list');

	        $list.slideToggle('fast').toggleClass('hidden');
	        $(this).html($list.hasClass('hidden') ? 'View Key Points &#9650;' : 'Hide Key Points &#9660;');

	        if(!$list.hasClass('hidden')) {
	            $('.wp-top-5-point').each(function(i) {
	                $(this).delay(100 * i).fadeIn(500);  // Staggered fade-in animation for each point
	            });
	        }
	    });
	});



})( jQuery );
