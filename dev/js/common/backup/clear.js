import { _x } from '@wordpress/i18n';
import { createDialogComponent } from '../../components';

/**
 * Initialize clear backup functionality
 */
export const initClearBackup = () => {
	jQuery('.iloveimg_page_iloveimg-compress-admin-page #iloveimg_clear_backup').on('click', function (event) {
		event.preventDefault();

		let element = jQuery(event.currentTarget);

		const dialogComponent = createDialogComponent(
			_x('All files in the iloveimg-backup folder will be deleted. Continue?', 'dialog content', 'iloveimg'),
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
					action: 'ilove_img_compress_clear_backup',
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