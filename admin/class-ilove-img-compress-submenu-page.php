<?php
/**
 * Creates the submenu page for the plugin.
 *
 * @package iloveimgcompress
 */

/**
 * Creates the submenu page for the plugin.
 *
 * Provides the functionality necessary for rendering the page corresponding
 * to the submenu with which this page is associated.
 *
 * @package iloveimgcompress
 */
class Ilove_Img_Compress_Submenu_Page {

	/**
     * Render submenu parent.
     */
    public function render_parent() {
    }

	/**
	 * Render the Compress settings page for the iLoveIMG plugin.
	 *
	 * This method is responsible for rendering the Compress settings page for the iLoveIMG plugin. It retrieves the compression settings from the plugin options and loads the corresponding view for displaying and managing these settings.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_compress() {
        $options_value = json_decode( get_option( 'iloveimg_options_compress' ), true );
        require_once 'views/compress.php';
	}

	/**
	 * Render the Watermark settings page for the iLoveIMG plugin.
	 *
	 * This method is responsible for rendering the Watermark settings page for the iLoveIMG plugin. If the "iloveimg-watermark" plugin is not active, it loads the Watermark settings view for configuring watermark settings.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_watermark() {
		if ( ! is_plugin_active( 'iloveimg-watermark/iloveimgwatermark.php' ) ) {
        	require_once 'views/watermark.php';
    	}
	}

	/**
	 * Render the Media Optimization settings page for the iLoveIMG plugin.
	 *
	 * This method is responsible for rendering the Media Optimization settings page for the iLoveIMG plugin. It retrieves the plugin's compression settings from the options and loads the corresponding view for configuring media optimization settings.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function render_media_optimization() {
		$options_value = json_decode( get_option( 'iloveimg_options_compress' ), true );
		require_once 'views/media-bulk.php';
	}
}
