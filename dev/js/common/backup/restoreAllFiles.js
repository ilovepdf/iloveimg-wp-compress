import { _x } from '@wordpress/i18n';
import { createDialogComponent } from '../../components';

/**
 * Initialize restore all files functionality
 */
export const initRestoreAllFiles = () => {
	jQuery('.iloveimg_page_iloveimg-compress-admin-page #iloveimg_restore_all').on('click', function (event) {
		event.preventDefault();

		let element = jQuery(event.currentTarget);

		const dialogComponent = createDialogComponent(
			_x('All tool changes will be lost. Do you want to continue?', 'dialog content', 'iloveimg'),
			_x('Warning!', 'dialog title', 'iloveimg'),
			_x('Continue', 'button dialog box', 'iloveimg')
		);

		element.parent().append(dialogComponent);

		const dialogElem = document.getElementById('iloveimg-compress-restore-dialog');
		const btnConfirmDialog = document.getElementById('iloveimg-compress-dialog-aceptted');
		const btnCloseDialog = document.getElementById('iloveimg-compress-dialog-close');

		dialogElem.showModal();

		btnConfirmDialog.addEventListener('click', (e) => {
			e.preventDefault();
			element.attr('disabled', 'disabled');
			dialogElem.close();
			dialogElem.remove();

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'ilove_img_compress_restore_all',
				},
				success: function () {
					element.removeAttr('disabled');
					location.reload();
				},
				error: function () {
					element.removeAttr('disabled');
				},
			});
		});

		btnCloseDialog.addEventListener('click', (e) => {
			e.preventDefault();
			dialogElem.close();
			dialogElem.remove();
		});
	});
};