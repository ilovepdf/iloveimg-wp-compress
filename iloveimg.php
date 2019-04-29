<?php 
use ilovepdf;
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://iloveimg.com/
 * @since             1.0.0
 * @package           iloveimg
 *
 * @wordpress-plugin
 * Plugin Name:       iLoveIMG
 * Plugin URI:        https://developer.iloveimg.com/
 * Description:       Compress your images files and Stamp images or text into images files. This is the Official iLoveIMG plugin for Wordpress. You can optimize all your images and stamp them automatically as you do in iloveimg.com.
 * Version:           1.0.0
 * Author:            iLoveIMG
 * Author URI:        https://iloveimg.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       iloveimg
 * Domain Path:       /languages
 */
require_once('vendor/autoload.php');

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
     die;
}
 
// Include the dependencies needed to instantiate the plugin.
foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
    include_once $file;
}
 
add_action( 'plugins_loaded', 'iloveimg_custom_admin_settings' );
/**
 * Starts the plugin.
 *
 * @since 1.0.0
 */

global $iloveimg_db_version;
$iloveimg_db_version = '1.0';

function iloveimg_custom_admin_settings() {
    
    $serializer = new Serializer();
    $serializer->init();
    
    $plugin = new iLoveIMG_Submenu( new iLoveIMG_Submenu_Page() );
    $plugin->init();
 
}

function iloveimg_activate(){
    add_option( 'iloveimg_db_version', $iloveimg_db_version );
    if(!get_option('iloveimg_options_compress')){
        update_option('iloveimg_options_compress', 
            serialize(
                    [
                        'iloveimg_field_compress_activated' => 1,
                        'iloveimg_field_autocompress' => 1,
                        'iloveimg_field_sizes' => ['full', 'thumbnail', 'medium', 'medium_large', 'large'],
                        'iloveimg_field_resize_full' => 0,
                        'iloveimg_field_size_full_width' => 2048,
                        'iloveimg_field_size_full_height' => 2048,

                    ]
                )
            );
    }
}

register_activation_hook( __FILE__, 'iloveimg_activate' );

require_once( dirname( __FILE__ ) . '/lib/class-resources.php' );
require_once( dirname( __FILE__ ) . '/lib/class-iloveimg-plugin.php' );
require_once( dirname( __FILE__ ) . '/lib/class-iloveimg-process.php' );
new iLoveIMG_Plugin();

