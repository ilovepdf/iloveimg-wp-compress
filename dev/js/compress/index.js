/**
 * Compress image via AJAX
 * @param {Event} event
 */
export function compressImage(event) {
	var element = jQuery(event.target);
	var container = element.closest('td');

	element.attr('disabled', 'disabled');
	element.next('.spinner, .loading').show();

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'ilove_img_compress_library',
			id: element.data('id') || element.attr('data-id'),
			imgnonce: element.data('imgnonce') || element.attr('data-imgnonce'),
		},
		success: function (data) {
			element.removeAttr('disabled');
			container.html(data);
		},
		error: function () {
			element.removeAttr('disabled');
		},
	});
}

/**
 * Check compression status
 * @param {HTMLElement} element
 * @param {number} index
 * @param {Object} timesIntervals
 */
export function statusCompressing(element, index, timesIntervals) {
	var $element = jQuery(element);
	var container = $element.closest('td');

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'ilove_img_compress_library_is_compressed',
			id: $element.data('id') || $element.attr('data-id'),
			imgnonce: $element.data('imgnonce') || $element.attr('data-imgnonce'),
		},
		success: function (data) {
			clearInterval(timesIntervals['ref_' + index]);
			container.html(data);
		},
		error: function () { },
	});
}

/**
 * Handle compress all button
 * @param {Event} event
 */
export function compressAll(event) {
	var totalImagesToCompress = jQuery('button.iloveimg-compress').length;
	var timeReload;

	jQuery('button#iloveimg_allcompress').attr('disabled', 'disabled');
	jQuery('button.iloveimg-compress').each(function (index, element) {
		var buttonCompress = jQuery(element);
		buttonCompress.trigger('click');
		timeReload = setInterval(function () {
			var _percent = 100 - (jQuery('button.iloveimg-compress').length * 100) / totalImagesToCompress;
			jQuery('button#iloveimg_allcompress .iloveimg-compress-all__percent').width(_percent + '%');
			if (!jQuery('button.iloveimg-compress').length) {
				clearInterval(timeReload);
				location.reload();
			}
		}, 300);
	});
}

/**
 * Handle bulk action submit
 * @param {Event} event
 */
export function handleBulkAction(event) {
	// Check both action selectors (top and bottom dropdowns)
	var selectedAction = jQuery(document).find('select[name=action] option:checked').val() || jQuery(document).find('select[name=action2] option:checked').val();
	
	if (selectedAction === 'iloveimg_compress') {
		event.preventDefault();
		var totalToProcess = jQuery('table.wp-list-table.images tbody tr, table.wp-list-table.media tbody tr').find("th.check-column input[type='checkbox']:checked").length;
		var timeReload;
		
		jQuery('table.wp-list-table.images tbody tr, table.wp-list-table.media tbody tr').each(function (
			index,
			element
		) {
			if (jQuery(element).find("th.check-column input[type='checkbox']").is(':checked')) {
				jQuery(element).find('button.iloveimg-compress').trigger('click');
			}
		});
		
		if (totalToProcess > 0) {
			timeReload = setInterval(function () {
				var remaining = jQuery('table.wp-list-table.images tbody tr, table.wp-list-table.media tbody tr').find('button.iloveimg-compress').length;
				if (remaining === 0) {
					clearInterval(timeReload);
					location.reload();
				}
			}, 500);
		}
	}
}