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
 * @since             1.0.1
 * @package           iloveimgcompress
 *
 * @wordpress-plugin
 * Plugin Name:       Image Compressor & Optimizer - iLoveIMG
 * Plugin URI:        https://developer.iloveimg.com/
 * Description:       Get your images delivered quickly. Now you can get a powerful, easy to use, and reliable image compression plugin for your image optimization needs. With full automation and powerful features, iLoveIMG makes it easy to speed up your website by lightening past and new images with just a click. Compress JPG, PNG and GIF images in your Wordpress to improve the positioning of your site, boost visitor’s engagement and ultimately increase sales.
 * Version:           1.0.1
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
 

include_once "admin/class-iloveimg-plugin.php";
include_once "admin/class-iloveimg-process.php";
include_once "admin/class-resources.php";
include_once "admin/class-serializer.php";
include_once "admin/class-submenu-page.php";
include_once "admin/class-submenu.php";
include_once "admin/class-table-media-bulk-optimized.php";
 
add_action( 'plugins_loaded', 'iLoveIMG_Compress_custom_admin_settings' );



function iLoveIMG_Compress_custom_admin_settings() {
    
    $serializer = new iLoveIMG_Compress_Serializer();
    $serializer->init();
    
    $plugin = new iLoveIMG_Compress_Submenu( new iLoveIMG_Compress_Submenu_Page() );
    $plugin->init();
 
}

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'iLoveIMG_Compress_add_plugin_page_settings_link');
function iLoveIMG_Compress_add_plugin_page_settings_link( $links ) {
	$links[] = '<a href="' .
		admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) .
		'">' . __('Settings') . '</a>';
    $links[] = '<a href="' .
        admin_url( 'upload.php?page=iloveimg-media-page' ) .
        '">' . __('Bulk Optimization') . '</a>';
	return $links;
}

function iLoveIMG_Compress_activate(){
    add_option( 'iLoveIMG_Compress_db_version', iLoveIMG_Compress_COMPRESS_DB_VERSION );
    if(!get_option('iloveimg_options_compress')){
        $iloveimg_thumbnails = ['full', 'thumbnail', 'medium', 'medium_large', 'large'];
        if(!extension_loaded('gd')){
            $iloveimg_thumbnails = ['full'];
        }
        update_option('iloveimg_options_compress', 
            serialize(
                    [
                        //'iloveimg_field_compress_activated' => 0,
                        //'iloveimg_field_autocompress' => 1,
                        'iloveimg_field_sizes' => $iloveimg_thumbnails,
                        'iloveimg_field_resize_full' => 0,
                        'iloveimg_field_size_full_width' => 2048,
                        'iloveimg_field_size_full_height' => 2048,

                    ]
                )
            );
    }
}

register_activation_hook( __FILE__, 'iLoveIMG_Compress_activate' );

new iLoveIMG_Compress_Plugin();


define('iLoveIMG_Compress_REGISTER_URL', 'https://api.iloveimg.com/v1/user');
define('iLoveIMG_Compress_LOGIN_URL', 'https://api.iloveimg.com/v1/user/login');
define('iLoveIMG_Compress_USER_URL', 'https://api.iloveimg.com/v1/user');
define('iLoveIMG_Compress_NUM_MAX_FILES', 2);
define('iLoveIMG_Compress_COMPRESS_DB_VERSION', '1.0');
define('iLoveIMG_Compress_Plugin_URL', plugin_dir_url(__FILE__));

set_time_limit(300);