<?php
/**
 * Image Compressor & Optimizer - iLoveIMG
 *
 * @link              https://iloveimg.com/
 * @since             1.0.4
 * @package           iloveimgcompress
 *
 * @wordpress-plugin
 * Plugin Name:       Image Compressor & Optimizer - iLoveIMG
 * Plugin URI:        https://iloveapi.com/
 * Description:       Get your images delivered quickly. Now you can get a powerful, easy to use, and reliable image compression plugin for your image optimization needs. With full automation and powerful features, iLoveIMG makes it easy to speed up your website by lightening past and new images with just a click. Compress JPG, PNG and GIF images in your WordPress to improve the positioning of your site, boost visitor’s engagement and ultimately increase sales.
 * Version:           2.2.6
 * Requires at least: 5.3
 * Requires PHP:      7.4
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

require_once plugin_dir_path( __FILE__ ) . '/vendor/autoload.php';

if ( ini_get( 'max_execution_time' ) < 300 ) {
    set_time_limit( 300 );
}

$ilove_img_compress_upload_path = wp_upload_dir();

define( 'ILOVE_IMG_COMPRESS_REGISTER_URL', 'https://api.ilovepdf.com/v1/user' );
define( 'ILOVE_IMG_COMPRESS_LOGIN_URL', 'https://api.ilovepdf.com/v1/user/login' );
define( 'ILOVE_IMG_COMPRESS_USER_URL', 'https://api.ilovepdf.com/v1/user' );
define( 'ILOVE_IMG_COMPRESS_NUM_MAX_FILES', 2 );
define( 'ILOVE_IMG_COMPRESS_DB_VERSION', '1.1' );
define( 'ILOVE_IMG_COMPRESS_UPLOAD_FOLDER', $ilove_img_compress_upload_path['basedir'] );
define( 'ILOVE_IMG_COMPRESS_BACKUP_FOLDER', $ilove_img_compress_upload_path['basedir'] . '/iloveimg-backup/' );
define( 'ILOVE_IMG_COMPRESS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

use Ilove_Img_Compress\Ilove_Img_Compress_Plugin;
use Ilove_Img_Compress\Ilove_Img_Compress_Resources;
use Ilove_Img_Compress\Ilove_Img_Compress_Serializer;
use Ilove_Img_Compress\Ilove_Img_Compress_Submenu;
use Ilove_Img_Compress\Ilove_Img_Compress_Submenu_Page;

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
		'">' . __( 'Settings', 'iloveimg' ) . '</a>';
    $links[] = '<a href="' .
        admin_url( 'upload.php?page=iloveimg-media-page' ) .
        '">' . __( 'Bulk Optimization', 'iloveimg' ) . '</a>';
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
    Ilove_Img_Compress_Resources::update_option( 'ilove_img_compress_db_version', ILOVE_IMG_COMPRESS_DB_VERSION, true );

    if ( ! file_exists( ILOVE_IMG_COMPRESS_BACKUP_FOLDER ) ) {
        wp_mkdir_p( ILOVE_IMG_COMPRESS_BACKUP_FOLDER );
    }

    if ( ! get_option( 'iloveimg_options_compress' ) ) {
        $iloveimg_thumbnails = array( 'full', 'thumbnail', 'medium', 'medium_large', 'large' );
        if ( ! extension_loaded( 'gd' ) ) {
            $iloveimg_thumbnails = array( 'full' );
        }

        Ilove_Img_Compress_Resources::update_option(
            'iloveimg_options_compress',
            wp_json_encode(
                array(
					'iloveimg_field_sizes'            => $iloveimg_thumbnails,
					'iloveimg_field_resize_full'      => 0,
					'iloveimg_field_size_full_width'  => 2048,
					'iloveimg_field_size_full_height' => 2048,
					'iloveimg_field_backup'           => 'on',
                )
            ),
            true
        );
    } else {
        $old_data = get_option( 'iloveimg_options_compress' );

        if ( is_serialized( $old_data ) ) {
            $old_data_serialize = unserialize( get_option( 'iloveimg_options_compress' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
            Ilove_Img_Compress_Resources::update_option( 'iloveimg_options_compress', wp_json_encode( $old_data_serialize ), true );
        }
    }
}

register_activation_hook( __FILE__, 'ilove_img_compress_activate' );

new Ilove_Img_Compress_Plugin();
