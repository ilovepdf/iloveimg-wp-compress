<?php 
//use iloveimg;
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
 * @package           iloveimgcompress
 *
 * @wordpress-plugin
 * Plugin Name:       iLoveIMG - Compress Images
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

if ( ! defined( 'WPINC' ) ) {
     die;
}
 
foreach ( glob( plugin_dir_path( __FILE__ ) . 'admin/*.php' ) as $file ) {
    include_once $file;
}
 
add_action( 'plugins_loaded', 'iloveimg_compress_custom_admin_settings' );


global $iloveimg_compress_db_version;
$iloveimg_compress_db_version = '1.0';

function iloveimg_compress_custom_admin_settings() {
    
    $serializer = new iLoveIMG_Compress_Serializer();
    $serializer->init();
    
    $plugin = new iLoveIMG_Compress_Submenu( new iLoveIMG_Compress_Submenu_Page() );
    $plugin->init();
 
}

function iloveimg_compress_activate(){
    add_option( 'iloveimg_compress_db_version', $iloveimg_compress_db_version );
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

register_activation_hook( __FILE__, 'iloveimg_compress_activate' );

new iLoveIMG_Compress_Plugin();

