import { compressImage, statusCompressing, compressAll, attachCompressingPoller, initTimesIntervals } from './compress';
import { initClearBackup, initRestoreAllFiles, initRestoreFile } from './common/backup';

(function ($) {
	var adminpage = '';
	if (typeof window.adminpage !== 'undefined') {
		adminpage = window.adminpage;
	}
	var timesIntervals = {};

	// Share timesIntervals with the compress module so post-swap re-binding works (C5)
	initTimesIntervals(timesIntervals);

	// Initialize based on admin page
	switch (adminpage) {
		case 'upload-php':
			$(document).on('click', 'button.iloveimg-compress', compressImage);

			// Check compressing status — use shared helper for page-load .each (C5)
			$('.iloveimg_compressing').each(function (index, element) {
				attachCompressingPoller(element, index, timesIntervals);
			});

			// Native WP bulk dropdown goes server-side — handleBulkAction intercept removed (C7)
			break;

		case 'media_page_iloveimg-media-page':
		case 'post-php':
			$(document).on('click', 'button.iloveimg-compress', compressImage);
			$(document).on('click', 'button#iloveimg_allcompress', compressAll);

			// Check compressing status — use shared helper for page-load .each (C5)
			$('.iloveimg_compressing').each(function (index, element) {
				attachCompressingPoller(element, index, timesIntervals);
			});

			// Native WP bulk dropdown goes server-side — handleBulkAction intercept removed (C7)
			break;
	}

	// Settings page: highlight save button on change
	$('.iloveimg_settings__options-container form input').on('change', function () {
		if (!$('.iloveimg_settings__options-container form .submit button').hasClass('need_saving')) {
			setTimeout(function () {
				$('.iloveimg_settings__options-container form .submit button').addClass('need_saving');
				setTimeout(function () {
					$('.iloveimg_settings__options-container form .submit button').removeClass('need_saving');
				}, 5000);
			}, 1000);
		}
	});

	// Initialize backup functionality
	initRestoreAllFiles();
	initClearBackup();
	initRestoreFile();
})(jQuery);
