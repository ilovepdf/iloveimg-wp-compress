/**
 * Bulk queue runner — bounded concurrent worker pool for per-file compression.
 *
 * Up to K files are processed concurrently (K = iloveimgCompress.bulkConcurrency,
 * default 3). As each file settles (success or terminal error), the next queued
 * id fills the freed slot immediately — no waiting for a full "chunk" to drain.
 *
 * The trigger and poll paths both treat non-OK HTTP status, an empty body, and
 * HTTP 202 as "still working" and loop until a real terminal state is reached.
 * Status-3 "queued/blocked" fragments trigger a bounded re-queue within the same
 * run rather than an immediate failure, so a transiently-saturated server gate
 * does not silently strand files.
 *
 * jQuery and ajaxurl are WordPress admin globals referenced directly, matching
 * the convention already used in compress/index.js.
 */

import { isDoneFragment } from '../common/rowStatus';

/**
 * Milliseconds between consecutive poll attempts.
 * @type {number}
 */
var POLL_INTERVAL = 1000;

/**
 * Maximum poll attempts per file before treating it as failed (hard backstop only).
 * At 1 s/poll this is ~5 minutes — the server's reset_if_stale (300 s) produces a
 * terminal status-0 fragment well before this limit is reached in practice.
 * @type {number}
 */
var MAX_POLLS = 300;

/**
 * Maximum times a single file is re-queued due to a status-3 (queued/blocked)
 * response before it is counted as failed.
 * @type {number}
 */
var MAX_RETRIES = 3;

/**
 * Resolve after `ms` milliseconds.
 * @param {number} ms
 * @returns {Promise<void>}
 */
function wait( ms ) {
	return new Promise( function ( resolve ) {
		setTimeout( resolve, ms );
	} );
}

/**
 * Return true when the HTML fragment contains the in-flight spinner element,
 * indicating the server job is still running (status 1 or 3).
 *
 * @param {string} html  Raw HTML string from the AJAX response.
 * @returns {boolean}
 */
function isQueuedFragment( html ) {
	return typeof html === 'string' && html.indexOf( 'iloveimg_compressing' ) !== -1;
}

/**
 * Process a list of attachment IDs with a bounded concurrent worker pool.
 *
 * @param {Object}   options
 * @param {Array}    options.ids          Attachment IDs to process.
 * @param {string}   options.action       WordPress AJAX action for the compress step.
 * @param {string}   options.pollAction   WordPress AJAX action for the poll step.
 * @param {string}   options.nonce        Action-scoped nonce.
 * @param {Function} [options.onFileStart] Called with (id) when a file enters a pool slot
 *                                        (first attempt only; not on re-queue retries).
 * @param {Function} options.onFileDone   Called with (id, ok, html) when a file finally
 *                                        settles (after all retries).
 * @param {Function} options.onProgress   Called with ({done, total}) after every settle.
 * @returns {Promise<{ succeeded: number, failed: number }>}
 */
export async function runQueue( { ids, action, pollAction, nonce, onFileStart, onFileDone, onProgress } ) {
	// Read pool size from localized config; fall back to a safe constant.
	var K = ( window.iloveimgCompress && iloveimgCompress.bulkConcurrency ) || 3;

	var total    = ids.length;
	var pending  = ids.slice(); // ids not yet dispatched (mutable queue)
	var retries  = {};          // retry count per id: retries[id] = n
	var inFlight = 0;
	var done     = 0;
	var succeeded = 0;
	var failed    = 0;

	return new Promise( function ( resolve ) {
		if ( total === 0 ) {
			resolve( { succeeded: 0, failed: 0 } );
			return;
		}

		/** Drain the pending queue into available pool slots. */
		function fillSlots() {
			while ( inFlight < K && pending.length > 0 ) {
				var id = pending.shift();
				inFlight++;
				processFile( id );
			}
		}

		/**
		 * Record a file as finally settled (success or fail), update counters,
		 * fire callbacks, free the slot, and fill it with the next pending id.
		 *
		 * @param {string|number} id
		 * @param {boolean}       ok
		 * @param {string}        html
		 */
		function settle( id, ok, html ) {
			inFlight--;
			done++;
			if ( ok ) {
				succeeded++;
			} else {
				failed++;
			}
			onFileDone( id, ok, html );
			onProgress( { done: done, total: total } );

			if ( done >= total ) {
				resolve( { succeeded: succeeded, failed: failed } );
			} else {
				fillSlots();
			}
		}

		/**
		 * Process a single file through the trigger → poll lifecycle.
		 * onFileStart is called only on the first attempt (retries[id] is falsy).
		 *
		 * @param {string|number} id
		 */
		function processFile( id ) {
			if ( onFileStart && ! retries[ id ] ) {
				onFileStart( id );
			}

			doProcess( id ).then( function ( result ) {
				if ( result.requeue ) {
					// Server reported status-3 (queued/blocked): bounded re-queue.
					var r = ( retries[ id ] || 0 ) + 1;
					retries[ id ] = r;
					if ( r <= MAX_RETRIES ) {
						inFlight--;
						pending.push( id );
						fillSlots();
					} else {
						// Retries exhausted — count as failed.
						settle( id, false, result.html );
					}
					return;
				}
				settle( id, result.ok, result.html );
			} ).catch( function () {
				settle( id, false, '' );
			} );
		}

		/**
		 * Async core: POST trigger → poll until terminal.
		 *
		 * Returns one of:
		 *   { ok: true,  html }  — terminal success
		 *   { ok: false, html }  — terminal failure
		 *   { requeue: true, html } — status-3 detected; caller decides retry vs fail
		 *
		 * @param {string|number} id
		 * @returns {Promise<{ok?: boolean, requeue?: boolean, html: string}>}
		 */
		async function doProcess( id ) {
			var lastHtml = '';

			// --- Trigger POST ---
			// A non-2xx response (e.g. proxy 500 at 40 s) or a network-level throw both
			// fall through to the poll loop — the server job may already be completing.
			var triggerBody = '';
			var triggerResolved = false;

			try {
				var resp = await jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: action, id: id, imgnonce: nonce },
				} );
				triggerResolved = true;
				triggerBody = typeof resp === 'string' ? resp : '';
			} catch ( e ) {
				// Non-2xx (proxy kill, 403 from auth failure) or network error:
				// do not conclude failure; fall through to the poll loop below.
			}

			lastHtml = triggerBody;

			// If the trigger responded with a non-empty, non-queued body, check for
			// terminal states immediately (no poll needed).
			if ( triggerResolved && triggerBody ) {
				if ( ! isQueuedFragment( triggerBody ) ) {
					if ( isDoneFragment( triggerBody ) ) {
						// Server completed synchronously and returned the success fragment.
						return { ok: true, html: lastHtml };
					}
					// Non-empty, non-queued, non-success → terminal error from trigger.
					return { ok: false, html: lastHtml };
				}
				// Queued/in-flight fragment from trigger → fall through to poll loop.
			}

			// --- Poll loop ---
			// Reached when: trigger threw (proxy kill), trigger returned an empty body
			// (edge case), or trigger returned a queued/in-flight fragment.
			// HTTP 202 from the poll endpoint means "still working" — WordPress returns it
			// as a 202 success with an empty body, so jQuery.ajax fires the success
			// callback with '' — the `! pollBody` guard below keeps the loop running.
			var polls = 0;
			while ( polls < MAX_POLLS ) {
				await wait( POLL_INTERVAL );
				polls++;

				var pollBody = '';
				try {
					var pollResp = await jQuery.ajax( {
						url: ajaxurl,
						type: 'POST',
						data: { action: pollAction, id: id, imgnonce: nonce },
					} );
					pollBody = typeof pollResp === 'string' ? pollResp : '';
				} catch ( pollErr ) {
					// Non-2xx status or network failure → still uncertain; keep polling.
					continue;
				}

				// Empty body (HTTP 202 "still working") → keep polling.
				if ( ! pollBody ) {
					continue;
				}

				if ( isQueuedFragment( pollBody ) ) {
					// Server says queued/blocked (status 3): signal caller to decide retry.
					return { requeue: true, html: pollBody };
				}

				lastHtml = pollBody;

				if ( isDoneFragment( pollBody ) ) {
					return { ok: true, html: lastHtml };
				}

				// Any other non-empty body → terminal error (e.g. "Try again or buy more credits").
				return { ok: false, html: lastHtml };
			}

			// MAX_POLLS exhausted without a terminal state: treat as failed.
			return { ok: false, html: lastHtml };
		}

		// Kick off the pool — fill up to K slots from the pending queue.
		fillSlots();
	} );
}
