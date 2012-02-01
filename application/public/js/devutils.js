/**
 *
 */

(function ($) {
	$(document).ready(function () {

		/**
		 * Update UnitTest header after tests have run.
		 */
		if ($('.unittest_heading').length > 0) {
			$('.unittest_heading .label').remove();
			if ($('.unittest_summary').length == 0) {
				$('.unittest_heading').append(' <span class="label label-important">Crashed</span>');
			} else {
				if ($('.unittest_summary').hasClass('alert-success')) {
					$('.unittest_heading').append(' <span class="label label-success">Passed</span>');
				} else {
					$('.unittest_heading').append(' <span class="label label-important">Failed</span>');
					$('.assertion:has(.fail)').first().addClass('alert').addClass('alert-error').attr('id', 'first_failed_assertion').css({
						marginTop: '15px',
						fontSize: '13px'
					});
					window.location.hash = '#first_failed_assertion';
				}
			}
		}
		if ($('#phpdoc').length) {
			$('.contents').css('margin', 0).css('width', '+=35');
			$(window).resize(function () {
				$('.pageLayout').css('min-height', $(window).height() - 45);
				$('#phpdoc').height($('.pageLayout').height() - $('#phpdoc').offset().top);
			});
			$(window).resize();
		}

	});
})(jQuery);