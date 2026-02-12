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
	 * Handle bulk compress action on custom media page.
	 *
	 * Processes bulk compression requests before the page renders (at load hook).
	 * This approach is necessary because the custom media page doesn't use the
	 * standard WordPress handle_bulk_actions-upload filter.
	 *
	 * @since 1.0.0
	 */
	public function handle_bulk_compress_action() {
		// Verify nonce for security
		$nonce = isset( $_REQUEST['iloveimg_bulk_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['iloveimg_bulk_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'iloveimg_bulk_compress_action' ) ) {
			return;
		}

		// Check if this is a form submission with a bulk action
		$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		if ( empty( $action ) ) {
			$action = isset( $_REQUEST['action2'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action2'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		}

		// Only proceed if compress action is selected
		if ( 'compress' !== $action ) {
			return;
		}

		// Verify we're on the correct page
		$page = isset( $_REQUEST['page'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		if ( 'iloveimg-media-page' !== $page ) {
			return;
		}

		// Get selected image IDs
		$image_ids = isset( $_REQUEST['image'] ) ? array_map( 'intval', wp_unslash( $_REQUEST['image'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verified above
		if ( empty( $image_ids ) ) {
			return;
		}

		// Process compression for each selected image
		$iloveimg_process = new Ilove_Img_Compress_Process();
		$success_ids      = array();
		$error_items      = array();

		foreach ( $image_ids as $id ) {
			$image = $iloveimg_process->compress( $id );

			if ( ! empty( $image['error'] ) ) {
				$error_items[] = array(
					'id'      => $id,
					'message' => $image['error_msg'],
				);
			} else {
				$success_ids[] = $id;
			}
		}

		// Store results in transients for display in the page
		set_transient( 'iloveimg_bulk_success', $success_ids, 600 );
		set_transient( 'iloveimg_bulk_errors', $error_items, 600 );

		// Determine status based on results
		$status = 'success';
		if ( ! empty( $error_items ) && ! empty( $success_ids ) ) {
			$status = 'partial';
		} elseif ( ! empty( $error_items ) ) {
			$status = 'error';
		}

		// Redirect back to the page with status parameter
		wp_safe_redirect(
			add_query_arg(
				array(
					'iloveimg-bulk-compression' => $status,
					'updated_count'             => count( $success_ids ),
				),
				admin_url( 'upload.php?page=iloveimg-media-page' )
			)
		);
		exit();
	}
}
