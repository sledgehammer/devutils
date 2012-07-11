/**
 *
 */

(function ($) {
	$(document).ready(function () {

		/**
		 * Update UnitTest indicator after the tests have run.
		 */
		var $indicator = $('[data-unittest=indicator]');
		if ($indicator.length > 0) {
			if ($('.unittest-summary').length == 0) {
				$indicator.text('Crashed').addClass('label-important');
			} else {
				if ($('.unittest-summary').hasClass('alert-success')) {
					$indicator.text('Passed').addClass('label-success');
				} else {
					$indicator.text('Failed').addClass('label-important');
					var $firstFailure = $('.unittest-assertion:has([data-unittest=fail])').first();
					$firstFailure.addClass('alert alert-error').css({
						marginTop: '15px',
						fontSize: '13px'
					});
					window.scrollTo(0, $firstFailure.offset().top - 30);
				}
			}
		}
		if ($('#phpdoc').length) {
			$('.contents').css('margin', 0).css({width: '+=30'});
			$(window).resize(function () {
				$('#phpdoc').height($('.pageLayout').height() - $('#phpdoc').offset().top - 30);
			});
			$(window).resize();
		}

	});
})(jQuery);