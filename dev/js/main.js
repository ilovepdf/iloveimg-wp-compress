import { compressImage, statusCompressing, compressAll, handleBulkAction } from './compress';
import { initClearBackup, initRestoreAllFiles, initRestoreFile } from './common/backup';

(function ($) {
	var adminpage = '';
	if (typeof window.adminpage !== 'undefined') {
		adminpage = window.adminpage;
	}
	var timesIntervals = {};

	// Initialize based on admin page
	switch (adminpage) {
		case 'upload-php':
			$(document).on('click', 'button.iloveimg-compress', compressImage);

			// Check compressing status
			$('.iloveimg_compressing').each(function (index, element) {
				timesIntervals['ref_' + index] = setInterval(function () {
					statusCompressing(element, index, timesIntervals);
				}, 1000);
			});

			// Handle bulk action form submit
			$(document).on('submit', 'form#images-filter, form#posts-filter', handleBulkAction);
			break;

		case 'media_page_iloveimg-media-page':
		case 'post-php':
			$(document).on('click', 'button.iloveimg-compress', compressImage);
			$(document).on('click', 'button#iloveimg_allcompress', compressAll);

			// Check compressing status
			$('.iloveimg_compressing').each(function (index, element) {
				timesIntervals['ref_' + index] = setInterval(function () {
					statusCompressing(element, index, timesIntervals);
				}, 1000);
			});

			// Handle bulk action form submit
			$(document).on('submit', 'form#images-filter, form#posts-filter', handleBulkAction);
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