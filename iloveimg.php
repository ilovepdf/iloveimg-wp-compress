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


include( dirname( __FILE__ ) . '/framework/admin-page-framework.php' );
if ( ! class_exists( 'AdminPageFramework' ) ) {
    return;
}
include( dirname( __FILE__ ) . '/lib/class-resources.php' );
include( dirname( __FILE__ ) . '/admin/menu_create_group.php' );
include( dirname( __FILE__ ) . '/admin/settings_account.php' );
include( dirname( __FILE__ ) . '/admin/settings_compress.php' );
include( dirname( __FILE__ ) . '/admin/settings_watermark.php' );
new iLoveIMG_CreatePageGroup;

include( dirname( __FILE__ ) . '/lib/class-iloveimg-plugin.php' );
include( dirname( __FILE__ ) . '/lib/class-iloveimg-process.php' );
new iLoveIMG_Plugin();
