<?php

namespace Ilove_Img_Compress;

/**
 * Class for managing the iLoveIMG plugin's submenu and pages.
 *
 * This class is responsible for adding a submenu to the 'Tools' menu in the WordPress admin area and rendering the plugin's settings and content pages. It initializes the submenu and adds individual pages for compress settings, watermark settings, and media optimization.
 *
 * @since 1.0.0
 */
class Ilove_Img_Compress_Submenu {


	/**
	 * A reference the class responsible for rendering the submenu page.
	 *
	 * @var    Ilove_Img_Compress_Submenu_Page
	 * @access private
	 */
	private $submenu_page;

	/**
	 * Initializes all of the partial classes.
	 *
	 * @param Ilove_Img_Compress_Submenu_Page $submenu_page A reference to the class that renders the page for the plugin.
	 */
	public function __construct( $submenu_page ) {
		$this->submenu_page = $submenu_page;
		add_action( 'load-media_page_iloveimg-media-page', array( $this, 'handle_bulk_compress_action' ) );
	}

	/**
	 * Adds a submenu for this plugin to the 'Tools' menu.
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_options_page' ), 9 );
	}

	/**
	 * Creates the submenu item and calls on the Submenu Page object to render
	 * the actual contents of the page.
	 */
	public function add_options_page() {

		add_menu_page(
			'iLoveIMG',
			'iLoveIMG',
			'manage_options',
			'iloveimg-admin-page',
			array( $this->submenu_page, 'render_parent' ),
			'https://www.iloveimg.com/img/favicons-img/favicon-16x16.png'
		);

		add_submenu_page(
			'iloveimg-admin-page',
			_x( 'Compress settings', 'submenu', 'iloveimg' ),
			_x( 'Compress settings', 'submenu', 'iloveimg' ),
			'manage_options',
			'iloveimg-compress-admin-page',
			array(
				$this->submenu_page,
				'render_compress',
			)
		);

		add_submenu_page(
			'iloveimg-admin-page',
			_x( 'Watermark settings', 'submenu', 'iloveimg' ),
			_x( 'Watermark settings', 'submenu', 'iloveimg' ),
			'manage_options',
			'iloveimg-watermark-admin-page',
			array(
				$this->submenu_page,
				'render_watermark',
			)
		);

		add_media_page(
			'iLoveIMG Media',
			_x( 'Bulk Compress', 'submenu', 'iloveimg' ),
			'upload_files',
			'iloveimg-media-page',
			array(
				$this->submenu_page,
				'render_media_optimization',
			)
		);
		remove_submenu_page( 'iloveimg-admin-page', 'iloveimg-admin-page' );
	}

	/**
	 * Handle bulk compress action on the custom media page (no-JS safety net).
	 *
	 * When JavaScript is enabled this hook is never reached because
	 * `initOwnPageBulkQueue()` intercepts the form submit in the browser and
	 * runs compression client-side.  When JavaScript is disabled (the only way
	 * this hook fires), redirect with the `iloveimg-bulk-compression=nojs` query
	 * var so `show_notices()` can render the "Bulk compression requires
	 * JavaScript" info notice — mirroring the native `handle_bulk_compression_action`
	 * safety net on `upload.php`.
	 *
	 * No synchronous foreach compression is performed here.
	 *
	 * @since 1.0.0
	 */
	public function handle_bulk_compress_action() {
		// Verify nonce for security.
		$nonce = isset( $_REQUEST['iloveimg_bulk_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['iloveimg_bulk_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'iloveimg_bulk_compress_action' ) ) {
			return;
		}

		// Capability check: only users who can manage media may trigger bulk actions.
		if ( ! current_user_can( 'upload_files' ) ) {
			return;
		}

		// Determine which bulk-action dropdown fired (top or bottom).
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		if ( '-1' === $action || '' === $action ) {
			$action = isset( $_REQUEST['action2'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		}

		// Only handle our own action; leave everything else to WP.
		if ( 'iloveimg_compress' !== $action ) {
			return;
		}

		// Verify we are on the correct page.
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		if ( 'iloveimg-media-page' !== $page ) {
			return;
		}

		// JS-disabled fallback: redirect with the nojs query var so show_notices()
		// renders the "Bulk compression requires JavaScript" info notice.
		wp_safe_redirect(
			add_query_arg(
				'iloveimg-bulk-compression',
				'nojs',
				admin_url( 'upload.php?page=iloveimg-media-page' )
			)
		);
		exit();
	}
}
