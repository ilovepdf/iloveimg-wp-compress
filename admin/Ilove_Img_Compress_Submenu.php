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
			'Compress settings',
			'Compress settings',
			'manage_options',
			'iloveimg-compress-admin-page',
			array(
				$this->submenu_page,
				'render_compress',
			)
		);

		add_submenu_page(
			'iloveimg-admin-page',
			'Watermark settings',
			'Watermark settings',
			'manage_options',
			'iloveimg-watermark-admin-page',
			array(
				$this->submenu_page,
				'render_watermark',
			)
		);

		add_media_page(
			'iLoveIMG Media',
			'Bulk Optimization',
			'manage_options',
			'iloveimg-media-page',
			array(
				$this->submenu_page,
				'render_media_optimization',
			)
		);
		remove_submenu_page( 'iloveimg-admin-page', 'iloveimg-admin-page' );
	}

	/**
	 * Placeholder for the settings page content.
	 */
	public function settings_page() {
		echo 'This is the page content';
	}
}
