<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://iloveimg.com/
 * @since             1.0.4
 * @package           iloveimgcompress
 *
 * @wordpress-plugin
 * Plugin Name:       Image Compressor & Optimizer - iLoveIMG
 * Plugin URI:        https://developer.iloveimg.com/
 * Description:       Get your images delivered quickly. Now you can get a powerful, easy to use, and reliable image compression plugin for your image optimization needs. With full automation and powerful features, iLoveIMG makes it easy to speed up your website by lightening past and new images with just a click. Compress JPG, PNG and GIF images in your WordPress to improve the positioning of your site, boost visitor’s engagement and ultimately increase sales.
 * Version:           1.0.5
 * Author:            iLoveIMG
 * Author URI:        https://iloveimg.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       iloveimg
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once 'admin/class-ilove-img-compress-plugin.php';
require_once 'admin/class-ilove-img-compress-process.php';
require_once 'admin/class-ilove-img-compress-resources.php';
require_once 'admin/class-ilove-img-compress-serializer.php';
require_once 'admin/class-ilove-img-compress-submenu-page.php';
require_once 'admin/class-ilove-img-compress-submenu.php';
require_once 'admin/class-ilove-img-compress-media-list-table.php';

add_action( 'plugins_loaded', 'ilove_img_compress_custom_admin_settings' );

/**
 * Initialize custom admin settings for the iLoveIMG Compress plugin.
 *
 * This function initializes custom admin settings for the iLoveIMG Compress plugin, including
 * the serializer and submenu items.
 *
 * @since 1.0.0
 */
function ilove_img_compress_custom_admin_settings() {

    $serializer = new Ilove_Img_Compress_Serializer();
    $serializer->init();

    $plugin = new Ilove_Img_Compress_Submenu( new Ilove_Img_Compress_Submenu_Page() );
    $plugin->init();
}

/**
 * Add settings and bulk optimization links to the plugin in the WordPress admin menu.
 *
 * This function adds links to the plugin's settings and bulk optimization pages in the WordPress
 * admin menu.
 *
 * @since 1.0.0
 *
 * @param array $links An array of existing plugin links.
 *
 * @return array An updated array of plugin links with added settings and bulk optimization links.
 */
function ilove_img_compress_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) .
		'">' . __( 'Settings' ) . '</a>';
    $links[] = '<a href="' .
        admin_url( 'upload.php?page=iloveimg-media-page' ) .
        '">' . __( 'Bulk Optimization' ) . '</a>';
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ilove_img_compress_add_plugin_page_settings_link' );

/**
 * Activation function for the iLoveIMG Compress plugin.
 *
 * This function is called when the plugin is activated. It sets the plugin's database version,
 * initializes default options if they don't exist, and updates settings related to image sizes.
 *
 * @since 1.0.0
 */
function ilove_img_compress_activate() {
    add_option( 'ilove_img_compress_db_version', ILOVE_IMG_COMPRESS_DB_VERSION );
    if ( ! get_option( 'iloveimg_options_compress' ) ) {
        $iloveimg_thumbnails = array( 'full', 'thumbnail', 'medium', 'medium_large', 'large' );
        if ( ! extension_loaded( 'gd' ) ) {
            $iloveimg_thumbnails = array( 'full' );
        }
        update_option(
            'iloveimg_options_compress',
            serialize(
                array(
					// 'iloveimg_field_compress_activated' => 0,
					// 'iloveimg_field_autocompress' => 1,
					'iloveimg_field_sizes'            => $iloveimg_thumbnails,
					'iloveimg_field_resize_full'      => 0,
					'iloveimg_field_size_full_width'  => 2048,
					'iloveimg_field_size_full_height' => 2048,

				)
            )
        );
    }
}

register_activation_hook( __FILE__, 'ilove_img_compress_activate' );

new Ilove_Img_Compress_Plugin();

define( 'ILOVE_IMG_COMPRESS_REGISTER_URL', 'https://api.iloveimg.com/v1/user' );
define( 'ILOVE_IMG_COMPRESS_LOGIN_URL', 'https://api.iloveimg.com/v1/user/login' );
define( 'ILOVE_IMG_COMPRESS_USER_URL', 'https://api.iloveimg.com/v1/user' );
define( 'ILOVE_IMG_COMPRESS_NUM_MAX_FILES', 2 );
define( 'ILOVE_IMG_COMPRESS_DB_VERSION', '1.0' );
define( 'ILOVE_IMG_COMPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

set_time_limit( 300 );
