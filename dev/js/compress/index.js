import { __, sprintf } from '@wordpress/i18n';
import { showAdminNotice } from '../components';

// Module-level state for batch operations
var _sharedTimesIntervals = null;
var batchInProgress = false;
var batchSuccesses = 0;
var batchErrors = 0;

// Dedup success notices: track which attachment ids have already received a
// "compressed successfully" notice in this page-load so a row that resolves
// "done" via multiple poll cycles only toasts once (REQ-01).
var notifiedIds = new Set();

/**
 * Store a reference to the shared timesIntervals object so poller re-binding
 * can work after an in-place container swap (C5).
 * @param {Object} timesIntervals
 */
export function initTimesIntervals(timesIntervals) {
	_sharedTimesIntervals = timesIntervals;
}

/**
 * Detect whether an AJAX HTML fragment indicates a completed compression.
 * render_compress_details() always emits iloveimg__badge__percent on success.
 * CONTRACT: `iloveimg__badge__percent` is the shared completion class between this JS check
 * and the `class="iloveimg__badge__percent"` element in render_compress_details().
 * Never rename on one side only.
 *
 * @param {string} data
 * @returns {boolean}
 */
function isDoneFragment(data) {
	// FR-C4: guard typeof before indexOf to avoid TypeError on non-string values.
	return typeof data === 'string' && data.indexOf('iloveimg__badge__percent') !== -1;
}

/**
 * Attach a setInterval status-poller to a .iloveimg_compressing element.
 * Extracted as a named helper so both the page-load .each in main.js and the
 * post-swap re-bind in statusCompressing/compressImage share the same logic.
 * @param {HTMLElement} element
 * @param {string|number} index  key used for timesIntervals['ref_' + index]
 * @param {Object}        timesIntervals
 */
export function attachCompressingPoller(element, index, timesIntervals) {
	timesIntervals['ref_' + index] = setInterval(function () {
		statusCompressing(element, index, timesIntervals);
	}, 1000);
}

/**
 * Refresh the overview statistics panel via AJAX (FR-C10b).
 * No-op when the overview panel is absent from the current page.
 */
function refreshOverviewPanel() {
	var $overview = jQuery('.iloveimg_settings__overview__statistics');
	if (!$overview.length || !window.iloveimgCompress || !iloveimgCompress.nonce) {
		return;
	}

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'ilove_img_compress_get_overview',
			imgnonce: iloveimgCompress.nonce,
		},
		success: function (response) {
			if (!response || !response.success || !response.data) {
				return;
			}
			var d = response.data;
			var $left = $overview.find('.iloveimg_settings__overview__statistics__column_left');
			var $right = $overview.find('.iloveimg_settings__overview__statistics__column_right');

			// Space-savings column
			$left.find('.iloveimg_percent-total').css('width', d.percent_saved + '%');
			$left.find('.iloveimg_saving__number').text(d.percent_saved + '%');
			$left.find('strong').eq(0).text(d.original_mb + ' MB');
			$left.find('strong').eq(1).text(d.compressed_mb + ' MB');

			// Images-compressed column
			$right.find('.iloveimg_percent-total').css('width', d.percent_compressed + '%');
			$right.find('.iloveimg_saving__number').text(d.percent_compressed + '%');
			$right.find('strong').eq(0).text(d.total_images);
			$right.find('strong').eq(1).text(d.files_compressed);
		},
	});
}

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
			// FR-C3: empty-response guard — never blank the <td> on empty/whitespace body.
			if (typeof data === 'string' && data.trim() !== '') {
				container.html(data);

				// FR-C1: check for in-flight indicator FIRST (queued/status=3 fix).
				// If present, re-bind the poller and do NOT touch batch counters or show a notice.
				var $newCompressing = container.find('.iloveimg_compressing');
				if ($newCompressing.length && _sharedTimesIntervals) {
					var newKey = 'swap_' + Date.now();
					attachCompressingPoller($newCompressing[0], newKey, _sharedTimesIntervals);
				} else if ($newCompressing.length) {
					// .iloveimg_compressing present but _sharedTimesIntervals not yet ready — skip counting.
				} else if (batchInProgress) {
					// Counted barrier — increment for the batch summary.
					if (isDoneFragment(data)) {
						batchSuccesses++;
					} else {
						batchErrors++;
					}
				} else {
					// Single-file: show notice; refresh overview on success.
					if (isDoneFragment(data)) {
						var id = element.data('id') || element.attr('data-id');
						if (!notifiedIds.has(id)) {
							notifiedIds.add(id);
							showAdminNotice(__('Image compressed successfully.', 'iloveimg'), 'success');
						}
						refreshOverviewPanel();
					} else {
						showAdminNotice(__('Could not compress image.', 'iloveimg'), 'error');
					}
				}
			} else {
				// Empty body — re-enable button for retry.
				element.removeAttr('disabled');
				element.next('.spinner, .loading').hide();
				if (batchInProgress) {
					batchErrors++;
				} else {
					showAdminNotice(__('Could not compress image.', 'iloveimg'), 'error');
				}
			}
		},
		error: function () {
			element.removeAttr('disabled');
			if (batchInProgress) {
				// Transport errors must count toward the batch barrier (C6).
				batchErrors++;
			} else {
				showAdminNotice(__('Could not compress image.', 'iloveimg'), 'error');
			}
		},
	});
}

/**
 * Check compression status (poll).
 * Updates the row in place and re-binds a poller if the new fragment still
 * shows an in-progress state (C5).
 * @param {HTMLElement} element
 * @param {number}      index
 * @param {Object}      timesIntervals
 */
export function statusCompressing(element, index, timesIntervals) {
	var $element = jQuery(element);
	var container = $element.closest('td');
	/* translators: Shown in admin notice when compression polling fails repeatedly. */
	var failedMsg = __('Could not compress image.', 'iloveimg');

	// Maximum consecutive non-500 transport failures before we give up polling for this row.
	// Scoped per row via jQuery .data() so one row's network issues do not affect other rows.
	var POLL_FAIL_THRESHOLD = 3;

	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'ilove_img_compress_library_is_compressed',
			id: $element.data('id') || $element.attr('data-id'),
			imgnonce: $element.data('imgnonce') || $element.attr('data-imgnonce'),
		},
		success: function (data) {
			// FR-C3: guard — never blank the <td> on an empty or whitespace-only response.
			if (typeof data === 'string' && data.trim() !== '') {
				clearInterval(timesIntervals['ref_' + index]);
				container.html(data);

				// Re-bind poller if the swapped fragment still has a compressing element (C5).
				var $newCompressing = container.find('.iloveimg_compressing');
				if ($newCompressing.length) {
					var newKey = 'swap_' + Date.now();
					attachCompressingPoller($newCompressing[0], newKey, timesIntervals);
				} else {
					// Poll resolved to completion (done or error fragment).
					if (!batchInProgress) {
						if (isDoneFragment(data)) {
							var pollId = $element.data('id') || $element.attr('data-id');
							if (!notifiedIds.has(pollId)) {
								notifiedIds.add(pollId);
								showAdminNotice(__('Image compressed successfully.', 'iloveimg'), 'success');
							}
							refreshOverviewPanel();
						} else {
							showAdminNotice(__('Could not compress image.', 'iloveimg'), 'error');
						}
					} else {
						if (isDoneFragment(data)) {
							batchSuccesses++;
						} else {
							batchErrors++;
						}
					}
				}
			}
			// If empty: leave the existing cell untouched and keep polling.
		},
		error: function (jqXHR) {
			// HTTP 500 means the job is still genuinely in-flight (server contract): keep polling.
			// Any other status is a real transport failure (network down, 502/504, DNS).
			if (jqXHR.status === 500) {
				return;
			}

			// Count consecutive non-500 failures per row (stored on the element via jQuery .data()
			// so separate rows do not share a counter). After POLL_FAIL_THRESHOLD failures we stop
			// the interval and surface an error so the spinner never spins indefinitely.
			var failCount = ($element.data('iloveimg-fail-count') || 0) + 1;
			$element.data('iloveimg-fail-count', failCount);

			if (failCount >= POLL_FAIL_THRESHOLD) {
				clearInterval(timesIntervals['ref_' + index]);
				$element.data('iloveimg-fail-count', 0);

				if (batchInProgress) {
					// Batch: increment counter so the barrier in compressAll() can fire.
					batchErrors++;
				} else {
					// Single: re-enable button so the user can retry manually.
					container.find('button.iloveimg-compress').removeAttr('disabled');
					// Hide spinner (class mirrors img class set in get_status_of_column()).
					container.find('.iloveimg-spinner').hide();
					showAdminNotice(failedMsg, 'error');
				}
			}
		},
	});
}

/**
 * Handle "Compress All" button — counted barrier, no location.reload() (C6).
 * Fires exactly one summary notice (success / partial / error) when all rows
 * have reported back or the max-tick cap is reached.
 * @param {Event} event
 */
export function compressAll(event) {
	var totalImagesToCompress = jQuery('button.iloveimg-compress').length;

	// FR-C5: guard against zero — prevents a spurious "0 images" summary notice.
	if (totalImagesToCompress === 0) {
		return;
	}

	batchInProgress = true;
	batchSuccesses = 0;
	batchErrors = 0;

	jQuery('button#iloveimg_allcompress').attr('disabled', 'disabled');

	jQuery('button.iloveimg-compress').each(function (index, element) {
		jQuery(element).trigger('click');
	});

	var tickCount = 0;
	// 300 ms per tick; allow ~60 s per image as a generous backstop cap.
	var maxTicks = totalImagesToCompress * 200 + 100;

	var timeProgress = setInterval(function () {
		tickCount++;
		var completed = batchSuccesses + batchErrors;
		var _percent = (completed * 100) / totalImagesToCompress;
		jQuery('button#iloveimg_allcompress .iloveimg-compress-all__percent').width(_percent + '%');

		if (completed >= totalImagesToCompress || tickCount >= maxTicks) {
			clearInterval(timeProgress);
			batchInProgress = false;

			var noticeType, noticeMsg;
			if (batchErrors === 0) {
				noticeType = 'success';
				/* translators: %d: number of images successfully compressed */
				noticeMsg = sprintf(__('%d image(s) compressed successfully.', 'iloveimg'), batchSuccesses);
			} else if (batchSuccesses === 0) {
				noticeType = 'error';
				/* translators: %d: number of images that failed to compress */
				noticeMsg = sprintf(__('%d image(s) failed to compress.', 'iloveimg'), batchErrors);
			} else {
				noticeType = 'warning';
				/* translators: 1: number of images successfully compressed, 2: number of failures */
				noticeMsg = sprintf(__('%1$d image(s) compressed, %2$d failed.', 'iloveimg'), batchSuccesses, batchErrors);
			}
			showAdminNotice(noticeMsg, noticeType);
			// FR-C10b: refresh overview after batch completes.
			refreshOverviewPanel();
		}
	}, 300);
}
