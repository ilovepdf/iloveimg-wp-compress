<?php

class iLoveIMG_Compress_Submenu {
 
	/**
	 * A reference the class responsible for rendering the submenu page.
	 *
	 * @var    Submenu_Page
	 * @access private
	 */
	private $submenu_page;

	/**
	 * Initializes all of the partial classes.
	 *
	 * @param Submenu_Page $submenu_page A reference to the class that renders the
	 *                                                                   page for the plugin.
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
			array( $this->submenu_page, 'renderParent' ),
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
				'renderCompress'
			)
		);

		add_submenu_page(
			'iloveimg-admin-page',
			'Watermark Options',
			'Watermark settings',
			'Watermark settings',
			'manage_options',
			'iloveimg-watermark-admin-page',
			array(
				$this->submenu_page,
				'renderWatermark'
			)
		);

		add_media_page(
			'iLoveIMG Media', 
			'Bulk Optimization', 
			'manage_options', 
			'iloveimg-media-page', 
			array(
				$this->submenu_page,
				'renderMediaOptimization'
			)
		);
		remove_submenu_page('iloveimg-admin-page', 'iloveimg-admin-page');

	}

	public function settings_page() {
		echo 'This is the page content';
	}
}