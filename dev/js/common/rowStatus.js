/**
 * Shared row-status seam for compress fragment detection and in-place DOM swap.
 *
 * Both the single-item compress path (compress/index.js) and the bulk queue
 * runner (bulk/queue.js) use these helpers so the done-detection contract and
 * the fragment-swap logic have exactly one implementation (DRY).
 *
 * CONTRACT: `iloveimg__badge__percent` is the shared completion marker emitted
 * by render_compress_details() on success.  Never rename on one side only.
 */

/**
 * Detect whether an AJAX HTML fragment indicates a completed compression.
 * render_compress_details() always emits iloveimg__badge__percent on success.
 *
 * @param {string} data  Raw HTML string returned by the AJAX endpoint.
 * @returns {boolean}
 */
export function isDoneFragment( data ) {
	// Guard typeof before indexOf to avoid TypeError on non-string values.
	return typeof data === 'string' && data.indexOf( 'iloveimg__badge__percent' ) !== -1;
}

/**
 * Swap a row status <td> with the returned HTML fragment in place.
 * Callers are responsible for any poller re-binding after the swap — this
 * function intentionally does only the DOM replacement so it stays usable
 * from both the single-item path (which manages its own setInterval) and
 * the bulk queue path (which has its own poll loop).
 *
 * @param {jQuery} $td   The table-cell jQuery object whose content will be replaced.
 * @param {string} data  Raw HTML fragment to inject.
 */
export function applyFragment( $td, data ) {
	$td.html( data );
}

/**
 * Render an error badge into a row's status-column cell on failure.
 *
 * Clears the cell content (removing the disabled button and spinner),
 * then injects a plain-text error badge.  The original button is NOT
 * re-enabled — the row is permanently marked failed for this page-load.
 *
 * The message is written via textContent (never innerHTML) to prevent
 * XSS from untrusted strings (constitution constraint).
 *
 * @param {jQuery} $cell    The status-column <td> jQuery object.
 * @param {string} message  Plain-text error message (already i18n-translated by the caller).
 */
export function markRowFailed( $cell, message ) {
	var badge = document.createElement( 'p' );
	badge.className = 'iloveimg-status-failed';
	badge.textContent = message;
	// empty() removes the disabled button and spinner; append() injects the badge.
	$cell.empty().append( badge );
}

