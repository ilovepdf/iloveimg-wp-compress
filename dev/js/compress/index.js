import { __, sprintf } from '@wordpress/i18n';
import { showAdminNotice } from '../components';
import { isDoneFragment, applyFragment, markRowFailed } from '../common/rowStatus';
import { runQueue } from '../bulk/queue';

// Module-level state
var _sharedTimesIntervals = null;

// Dedup success notices: track which attachment ids have already received a
// "compressed successfully" notice in this page-load so a row that resolves
// "done" via multiple poll cycles only toasts once (REQ-01).
var notifiedIds = new Set();

/**
 * Return true when the HTML fragment contains the in-flight spinner element,
 * indicating the server job is still running (status 1 or 3).
 *
 * @param {string} html
 * @returns {boolean}
 */
function isQueuedFragment( html ) {
	return typeof html === 'string' && html.indexOf( 'iloveimg_compressing' ) !== -1;
}

/**
 * Store a reference to the shared timesIntervals object so poller re-binding
 * can work after an in-place container swap (C5).
 * @param {Object} timesIntervals
 */
export function initTimesIntervals( timesIntervals ) {
	_sharedTimesIntervals = timesIntervals;
}

/**
 * Attach a setInterval status-poller to a .iloveimg_compressing element.
 * Extracted as a named helper so both the page-load .each in main.js and the
 * post-swap re-bind in statusCompressing/compressImage share the same logic.
 * @param {HTMLElement} element
 * @param {string|number} index  key used for timesIntervals['ref_' + index]
 * @param {Object}        timesIntervals
 */
export function attachCompressingPoller( element, index, timesIntervals ) {
	timesIntervals[ 'ref_' + index ] = setInterval( function () {
		statusCompressing( element, index, timesIntervals );
	}, 1000 );
}

/**
 * Refresh the overview statistics panel via AJAX (FR-C10b).
 * No-op when the overview panel is absent from the current page.
 * Exported so bulk/index.js can call it after each successful file.
 */
export function refreshOverviewPanel() {
	var $overview = jQuery( '.iloveimg_settings__overview__statistics' );
	if ( ! $overview.length || ! window.iloveimgCompress || ! iloveimgCompress.nonce ) {
		return;
	}

	jQuery.ajax( {
		url: ajaxurl,
		type: 'POST',
		data: {
			action: 'ilove_img_compress_get_overview',
			imgnonce: iloveimgCompress.nonce,
		},
		success: function ( response ) {
			if ( ! response || ! response.success || ! response.data ) {
				return;
			}
			var d = response.data;
			var $left  = $overview.find( '.iloveimg_settings__overview__statistics__column_left' );
			var $right = $overview.find( '.iloveimg_settings__overview__statistics__column_right' );

			// Space-savings column
			$left.find( '.iloveimg_percent-total' ).css( 'width', d.percent_saved + '%' );
			$left.find( '.iloveimg_saving__number' ).text( d.percent_saved + '%' );
			$left.find( 'strong' ).eq( 0 ).text( d.original_mb + ' MB' );
			$left.find( 'strong' ).eq( 1 ).text( d.compressed_mb + ' MB' );

			// Images-compressed column
			$right.find( '.iloveimg_percent-total' ).css( 'width', d.percent_compressed + '%' );
			$right.find( '.iloveimg_saving__number' ).text( d.percent_compressed + '%' );
			$right.find( 'strong' ).eq( 0 ).text( d.total_images );
			$right.find( 'strong' ).eq( 1 ).text( d.files_compressed );
		},
	} );
}

/**
 * Compress image via AJAX (single-file click handler).
 * On a successful trigger response, applies the fragment immediately.
 * On a trigger error (e.g. proxy 40s timeout), falls back to polling so the
 * row always resolves to the TRUE server outcome rather than an instant failure.
 *
 * @param {Event} event
 */
export function compressImage( event ) {
	var element   = jQuery( event.target );
	var container = element.closest( 'td' );
	var id        = element.data( 'id' ) || element.attr( 'data-id' );
	var imgnonce  = element.data( 'imgnonce' ) || element.attr( 'data-imgnonce' );

	element.attr( 'disabled', 'disabled' );
	element.next( '.iloveimg-spinner' ).show();

	jQuery.ajax( {
		url: ajaxurl,
		type: 'POST',
		data: {
			action:   'ilove_img_compress_library',
			id:       id,
			imgnonce: imgnonce,
		},
		success: function ( data ) {
			// FR-C3: empty-response guard — never blank the <td> on empty/whitespace body.
			if ( typeof data === 'string' && data.trim() !== '' ) {
				applyFragment( container, data );

				// FR-C1: check for in-flight indicator FIRST (queued/status=3 fix).
				// If present, re-bind the poller and do NOT show a notice.
				var $newCompressing = container.find( '.iloveimg_compressing' );
				if ( $newCompressing.length && _sharedTimesIntervals ) {
					var newKey = 'swap_' + Date.now();
					attachCompressingPoller( $newCompressing[ 0 ], newKey, _sharedTimesIntervals );
				} else if ( ! $newCompressing.length ) {
					// Single-file: show notice; refresh overview on success.
					if ( isDoneFragment( data ) ) {
						if ( ! notifiedIds.has( id ) ) {
							notifiedIds.add( id );
							showAdminNotice( __( 'Image compressed successfully.', 'iloveimg' ), 'success' );
						}
						refreshOverviewPanel();
					} else {
						showAdminNotice( __( 'Could not compress image.', 'iloveimg' ), 'error' );
					}
				}
			} else {
				// Empty body (2xx with no content) — treat the same as a trigger error:
				// poll through to the real server outcome instead of showing an instant
				// false failure and re-enabling the button (FC2: "always reach a terminal state").
				pollSingleFileToTerminal( id, imgnonce, container, element );
			}
		},
		error: function () {
			// The trigger POST failed (e.g. proxy 40s timeout; server job may still be running).
			// Poll the companion endpoint until a terminal state is reached so the row shows
			// the TRUE outcome instead of an instant false failure (design.md §Single-file click).
			pollSingleFileToTerminal( id, imgnonce, container, element );
		},
	} );
}

/**
 * Poll `ilove_img_compress_library_is_compressed` until the server reaches a
 * terminal state, then apply the fragment or show an error notice.
 *
 * Used as the single-file click fallback when the trigger AJAX call fails
 * (proxy timeout, etc.) — the server job may still be completing.
 *
 * @param {string|number} id         Attachment ID.
 * @param {string}        imgnonce   Nonce for the compression endpoint.
 * @param {jQuery}        container  The row's status <td>.
 * @param {jQuery}        element    The compress button element.
 */
function pollSingleFileToTerminal( id, imgnonce, container, element ) {
	var SINGLE_MAX_POLLS = 300;
	var polls = 0;

	var pollInterval = setInterval( function () {
		polls++;

		jQuery.ajax( {
			url: ajaxurl,
			type: 'POST',
			data: {
				action:   'ilove_img_compress_library_is_compressed',
				id:       id,
				imgnonce: imgnonce,
			},
			success: function ( pollData ) {
				var body = typeof pollData === 'string' ? pollData : '';

				// Empty body (HTTP 202 "still working") → keep polling.
				if ( ! body ) {
					if ( polls >= SINGLE_MAX_POLLS ) {
						clearInterval( pollInterval );
						markRowFailed( container, __( 'Could not compress image.', 'iloveimg' ) );
					}
					return;
				}

				clearInterval( pollInterval );

				if ( isQueuedFragment( body ) ) {
					// Still queued — apply fragment to show spinner; the page-load poller
					// will pick up the new .iloveimg_compressing element if present.
					applyFragment( container, body );
					var $newCompressing = container.find( '.iloveimg_compressing' );
					if ( $newCompressing.length && _sharedTimesIntervals ) {
						var newKey = 'swap_' + Date.now();
						attachCompressingPoller( $newCompressing[ 0 ], newKey, _sharedTimesIntervals );
					}
					return;
				}

				applyFragment( container, body );
				if ( isDoneFragment( body ) ) {
					if ( ! notifiedIds.has( id ) ) {
						notifiedIds.add( id );
						showAdminNotice( __( 'Image compressed successfully.', 'iloveimg' ), 'success' );
					}
					refreshOverviewPanel();
				} else {
					showAdminNotice( __( 'Could not compress image.', 'iloveimg' ), 'error' );
				}
			},
			error: function () {
				// Non-OK status → keep polling (the server may still be working).
				if ( polls >= SINGLE_MAX_POLLS ) {
					clearInterval( pollInterval );
					markRowFailed( container, __( 'Could not compress image.', 'iloveimg' ) );
				}
			},
		} );
	}, 1000 );
}

/**
 * Check compression status (poll) for a page-load `.iloveimg_compressing` element.
 * Updates the row in place and re-binds a poller if the new fragment still
 * shows an in-progress state (C5).
 *
 * @param {HTMLElement} element
 * @param {number}      index
 * @param {Object}      timesIntervals
 */
export function statusCompressing( element, index, timesIntervals ) {
	var $element  = jQuery( element );
	var container = $element.closest( 'td' );
	/* translators: Shown in admin notice when compression polling fails repeatedly. */
	var failedMsg = __( 'Could not compress image.', 'iloveimg' );

	// Maximum consecutive non-2xx transport failures before giving up polling for this row.
	var POLL_FAIL_THRESHOLD = 3;

	jQuery.ajax( {
		url: ajaxurl,
		type: 'POST',
		data: {
			action:   'ilove_img_compress_library_is_compressed',
			id:       $element.data( 'id' ) || $element.attr( 'data-id' ),
			imgnonce: $element.data( 'imgnonce' ) || $element.attr( 'data-imgnonce' ),
		},
		success: function ( data ) {
			// FR-C3: guard — never blank the <td> on an empty or whitespace-only response.
			if ( typeof data === 'string' && data.trim() !== '' ) {
				clearInterval( timesIntervals[ 'ref_' + index ] );

				var $parsed      = jQuery( '<div>' ).html( data );
				var hasCompressing = $parsed.find( '.iloveimg_compressing' ).length > 0;

				if ( hasCompressing ) {
					// Response contains a new polling element: apply and re-bind (C5).
					applyFragment( container, data );
					var $rebound = container.find( '.iloveimg_compressing' );
					if ( $rebound.length && _sharedTimesIntervals ) {
						var newKey = 'swap_' + Date.now();
						attachCompressingPoller( $rebound[ 0 ], newKey, _sharedTimesIntervals );
					}
				} else {
					// Poll resolved to a terminal state (done badge or failure fragment).
					applyFragment( container, data );
					var pollId = $element.data( 'id' ) || $element.attr( 'data-id' );
					if ( isDoneFragment( data ) ) {
						if ( ! notifiedIds.has( pollId ) ) {
							notifiedIds.add( pollId );
							showAdminNotice( __( 'Image compressed successfully.', 'iloveimg' ), 'success' );
						}
						refreshOverviewPanel();
					} else {
						showAdminNotice( __( 'Could not compress image.', 'iloveimg' ), 'error' );
					}
				}
			}
			// If empty (HTTP 202): leave the existing cell untouched and keep polling.
		},
		error: function ( jqXHR ) {
			// HTTP 500 is kept as a backward-compat guard (server now returns 202 for in-flight,
			// but older states or edge cases may still produce 500 on this install).
			if ( jqXHR.status === 500 ) {
				return;
			}

			var failCount = ( $element.data( 'iloveimg-fail-count' ) || 0 ) + 1;
			$element.data( 'iloveimg-fail-count', failCount );

			if ( failCount >= POLL_FAIL_THRESHOLD ) {
				clearInterval( timesIntervals[ 'ref_' + index ] );
				$element.data( 'iloveimg-fail-count', 0 );
				container.find( 'button.iloveimg-compress' ).removeAttr( 'disabled' );
				container.find( '.iloveimg-spinner' ).hide();
				showAdminNotice( failedMsg, 'error' );
			}
		},
	} );
}

/**
 * Handle "Compress All" button — runs all compressable rows through the same
 * bounded concurrent pool used by the native bulk-action path (design.md §Unify).
 *
 * @param {Event} event
 */
export function compressAll( event ) {
	var $buttons = jQuery( 'button.iloveimg-compress' );

	// FR-C5: guard against zero — prevents a spurious "0 images" summary notice.
	if ( $buttons.length === 0 ) {
		return;
	}

	// Collect ids from all compressable row buttons.
	var ids = [];
	$buttons.each( function () {
		var id = jQuery( this ).data( 'id' ) || jQuery( this ).attr( 'data-id' );
		if ( id ) {
			ids.push( String( id ) );
		}
	} );

	if ( ids.length === 0 ) {
		return;
	}

	var nonce = window.iloveimgCompress ? iloveimgCompress.nonce : '';

	jQuery( 'button#iloveimg_allcompress' ).attr( 'disabled', 'disabled' );
	notifiedIds.clear();

	// --- Progress banner ---
	var banner = document.createElement( 'div' );
	banner.id        = 'iloveimg-bulk-progress-banner';
	banner.className = 'ipdf-notice ilovepdf-base__layout-flex notice notice-info ipdf-bulk-progress';

	var bannerFigure = document.createElement( 'figure' );
	bannerFigure.className = 'ipdf-logo ilovepdf-base__layout-flex ilovepdf-base__layout-items--center';
	var bannerLogo = document.createElement( 'img' );
	bannerLogo.src = ( window.iloveimgCompress && iloveimgCompress.logoUrl ) || '';
	bannerLogo.alt = 'logo iloveimg';
	bannerFigure.appendChild( bannerLogo );

	var bannerP = document.createElement( 'p' );
	banner.appendChild( bannerFigure );
	banner.appendChild( bannerP );

	var wpHeaderEnd = document.querySelector( '.wp-header-end' );
	if ( wpHeaderEnd ) {
		wpHeaderEnd.insertAdjacentElement( 'afterend', banner );
	} else {
		var wpBodyContent = document.getElementById( 'wpbody-content' );
		if ( wpBodyContent ) {
			wpBodyContent.insertBefore( banner, wpBodyContent.firstChild );
		}
	}

	/* translators: %1$d: current file number, %2$d: total file count */
	bannerP.textContent = sprintf( __( 'Compressing file %1$d of %2$d', 'iloveimg' ), 0, ids.length );
	window.scrollTo( { top: 0, behavior: 'smooth' } );

	// --- Callbacks (identical contract to attachBulkInterceptor in bulk/index.js) ---
	function onProgress( progress ) {
		/* translators: %1$d: current file number, %2$d: total file count */
		bannerP.textContent = sprintf( __( 'Compressing file %1$d of %2$d', 'iloveimg' ), progress.done, progress.total );
	}

	function onFileStart( id ) {
		jQuery( 'button.iloveimg-compress[data-id="' + id + '"]' )
			.attr( 'disabled', 'disabled' )
			.next( '.iloveimg-spinner' )
			.show();
	}

	function onFileDone( id, ok, html ) {
		var $btn = jQuery( 'button.iloveimg-compress[data-id="' + id + '"]' );
		if ( ! $btn.length ) {
			return;
		}
		var $cell = $btn.closest( 'td' );
		if ( ok ) {
			applyFragment( $cell, html );
			refreshOverviewPanel();
		} else {
			markRowFailed( $cell, __( 'Could not compress image.', 'iloveimg' ) );
		}
	}

	runQueue( {
		ids:         ids,
		action:      'ilove_img_compress_library',
		pollAction:  'ilove_img_compress_library_is_compressed',
		nonce:       nonce,
		onProgress:  onProgress,
		onFileStart: onFileStart,
		onFileDone:  onFileDone,
	} ).then( function ( result ) {
		if ( banner.parentNode ) {
			banner.parentNode.removeChild( banner );
		}

		var msg, type;
		if ( result.failed === 0 ) {
			type = 'success';
			/* translators: %d: number of images successfully compressed */
			msg = sprintf( __( '%d image(s) compressed successfully.', 'iloveimg' ), result.succeeded );
		} else {
			type = 'warning';
			/* translators: 1: number successfully compressed, 2: number failed */
			msg = sprintf( __( '%1$d compressed, %2$d failed.', 'iloveimg' ), result.succeeded, result.failed );
		}
		showAdminNotice( msg, type );
		// FR-C10b: final overview refresh after the entire batch completes.
		refreshOverviewPanel();
	} ).catch( function ( err ) {
		console.error( '[iloveimg compress all]', err );
		if ( banner.parentNode ) {
			banner.parentNode.removeChild( banner );
		}
		showAdminNotice( __( 'An unexpected error occurred. Please reload and try again.', 'iloveimg' ), 'error' );
	} );
}
