import { _x } from '@wordpress/i18n';
import { createDialogComponent } from '../../components';

/**
 * Initialize restore single file functionality
 */
export const initRestoreFile = () => {
	jQuery('.iloveimg-compress.iloveimg_restore_button_wrapper .iloveimg_restore_button').on(
		'click',
		function (event) {
			event.preventDefault();

			let element = jQuery(event.currentTarget);
			let fieldNonce = jQuery(event.currentTarget).siblings('#_wpnonce');
			fieldNonce = fieldNonce.val();
			let action = element.data('action');
			let imageId = element.data('id');

			element.hide();
			element.nextAll('.loading').show();

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
						action: action,
						id: imageId,
						_wpnonce: fieldNonce,
					},
					dataType: 'json',
					success: function (data) {
						element.nextAll('.loading').hide();
						element.nextAll('.success').html(data.data).show();
						location.reload();
					},
					error: function (error) {
						element.nextAll('.loading').hide();
						element.nextAll('.error').html(error.responseJSON.data).show();
						element.show();
					},
				});
			});

			btnCloseDialog.addEventListener('click', (e) => {
				e.preventDefault();
				element.show();
				element.nextAll('.loading').hide();
				dialogElem.close();
				dialogElem.remove();
			});
		}
	);
};