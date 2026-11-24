/**
 * Bulk-queue controller for the WordPress media library list table and the
 * plugin's own compress submenu page.
 *
 * The shared helper `attachBulkInterceptor` intercepts a form submit when the
 * selected action is `iloveimg_compress`, collects the checked attachment IDs,
 * and runs them through the bounded concurrent pool runner (bulk/queue.js).
 * When no items are checked the native WP flow proceeds unchanged.
 *
 * jQuery and ajaxurl are WordPress admin globals referenced directly, matching
 * the convention already used in compress/index.js.
 */

import { __, sprintf } from '@wordpress/i18n';
import { showAdminNotice } from '../components';
import { runQueue } from './queue';
import { applyFragment, markRowFailed } from '../common/rowStatus';
import { refreshOverviewPanel } from '../compress';

/**
 * Shared bulk-action interceptor used by both the native media-library list
 * table and the plugin's own submenu page.
 *
 * @param {HTMLElement} form               The form element to intercept.
 * @param {Object}      options
 * @param {string}      options.checkboxName    Name prefix for attachment-id checkboxes (without `[]`).
 */
function attachBulkInterceptor( form, options ) {
	var checkboxName = options.checkboxName;

	form.addEventListener( 'submit', function ( event ) {
		// Determine which bulk-action dropdown is active (top or bottom).
		var action1 = form.querySelector( 'select[name="action"]' );
		var action2 = form.querySelector( 'select[name="action2"]' );
		var bulkAction = '';

		if ( action1 && action1.value !== '-1' ) {
			bulkAction = action1.value;
		} else if ( action2 && action2.value !== '-1' ) {
			bulkAction = action2.value;
		}

		// Only intercept our own action; let WP handle everything else.
		if ( bulkAction !== 'iloveimg_compress' ) {
			return;
		}

		// Collect the checked attachment IDs.
		var ids = [].slice.call( form.querySelectorAll( 'input[name="' + checkboxName + '[]"]:checked' ) )
			.map( function ( cb ) { return cb.value; } );

		// No items selected — do NOT preventDefault(); let WP's native validation show its notice.
		if ( ids.length === 0 ) {
			return;
		}

		event.preventDefault();

		var nonce = window.iloveimgCompress ? iloveimgCompress.nonce : '';

		// --- Progress banner ---
		// Branded layout: logo figure + progress text paragraph.
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

		// Insert after .wp-header-end (the WP admin notice landmark).
		var wpHeaderEnd = document.querySelector( '.wp-header-end' );
		if ( wpHeaderEnd ) {
			wpHeaderEnd.insertAdjacentElement( 'afterend', banner );
		} else {
			var wpBodyContent = document.getElementById( 'wpbody-content' );
			if ( wpBodyContent ) {
				wpBodyContent.insertBefore( banner, wpBodyContent.firstChild );
			}
		}

		// Pre-seed progress text so the banner is non-empty before the first file fires.
		/* translators: %1$d: current file number, %2$d: total file count */
		bannerP.textContent = sprintf( __( 'Compressing file %1$d of %2$d', 'iloveimg' ), 0, ids.length );

		// Scroll to the banner so the user can see progress.
		window.scrollTo( { top: 0, behavior: 'smooth' } );

		// --- Callbacks ---
		function onProgress( progress ) {
			/* translators: %1$d: current file number, %2$d: total file count */
			bannerP.textContent = sprintf( __( 'Compressing file %1$d of %2$d', 'iloveimg' ), progress.done, progress.total );
		}

		function onFileStart( id ) {
			// Disable the row button and reveal its spinner immediately before the
			// request fires, giving the user instant visual feedback.
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
				// Terminal success: replace the cell with the server's compressed-details fragment
				// and refresh the overview statistics panel.
				applyFragment( $cell, html );
				refreshOverviewPanel();
			} else {
				// Terminal failure: show an error badge at normal text size (no font-size override).
				markRowFailed( $cell, __( 'Could not compress image.', 'iloveimg' ) );
			}
		}

		// --- Run the pool ---
		runQueue( {
			ids:         ids,
			action:      'ilove_img_compress_library',
			pollAction:  'ilove_img_compress_library_is_compressed',
			nonce:       nonce,
			onProgress:  onProgress,
			onFileStart: onFileStart,
			onFileDone:  onFileDone,
		} ).then( function ( result ) {
			// Remove the progress banner.
			if ( banner.parentNode ) {
				banner.parentNode.removeChild( banner );
			}

			// Summary notice — exactly one per queue run.
			var msg, type;
			if ( result.failed === 0 ) {
				type = 'success';
				/* translators: %d: number of images successfully compressed */
				msg = sprintf( __( '%d image(s) compressed successfully.', 'iloveimg' ), result.succeeded );
			} else {
				type = 'warning';
				/* translators: 1: number of images successfully compressed, 2: number that failed */
				msg = sprintf( __( '%1$d compressed, %2$d failed.', 'iloveimg' ), result.succeeded, result.failed );
			}

			showAdminNotice( msg, type );
		} ).catch( function ( err ) {
			// Unexpected rejection outside per-file error handling.
			console.error( '[iloveimg compress bulk]', err );

			if ( banner.parentNode ) {
				banner.parentNode.removeChild( banner );
			}

			showAdminNotice( __( 'An unexpected error occurred. Please reload and try again.', 'iloveimg' ), 'error' );
		} );
	} );
}

/**
 * Initialise the bulk-action intercept on the native media library list table
 * (`upload.php`).  Safe to call unconditionally — guards against pages that
 * have no list table.
 */
export function initBulkQueue() {
	// Guard: only wire up on pages that have the native media list table.
	var form = document.getElementById( 'posts-filter' );
	if ( ! form ) {
		return;
	}

	attachBulkInterceptor( form, {
		// NOTE: compress passes the base name WITHOUT brackets ('media'), NOT 'media[]'.
		// The selector in attachBulkInterceptor appends '[]' itself when querying checkboxes.
		// Do NOT align this to the watermark style (which may differ) — that would break ID collection.
		checkboxName: 'media',
	} );
}

/**
 * Initialise the bulk-action intercept on the plugin's own compress submenu
 * page (`upload.php?page=iloveimg-media-page`).  Safe to call unconditionally —
 * guards against pages that have no `#images-filter` form.
 */
export function initOwnPageBulkQueue() {
	// Guard: only wire up on the own compress page.
	if ( ! document.getElementById( 'images-filter' ) ) {
		return;
	}

	var form = document.getElementById( 'images-filter' );
	attachBulkInterceptor( form, {
		// NOTE: compress passes the base name WITHOUT brackets ('image'), NOT 'image[]'.
		checkboxName: 'image',
	} );
}
