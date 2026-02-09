<?php
namespace Ilove_Img_Compress;

/**
 * Defines internationalization functionality.
 *
 * @since 2.2.7
 */
class Ilove_Img_Compress_I18n {
    /**
     *
     * Initializes the class.
     *
     * @since 2.2.7
     */
    public function init() {
        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'set_script_translations' ), 100 );
    }

    /**
     * Load the text domain for the plugin.
     *
     * Loads the text domain for the plugin, allowing for internationalization and localization.
     *
     * @since 2.2.7
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'iloveimg', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
    }

    /**
     * Set script translations for JavaScript files.
     *
     * Loads the JSON translation files for enqueued scripts.
     *
     * @since 2.2.7
     */
    public function set_script_translations() {
        if ( wp_script_is( 'iloveimg-compress-main', 'enqueued' ) ) {
            wp_set_script_translations( 'iloveimg-compress-main', 'iloveimg', plugin_dir_path( __DIR__ ) . 'languages' );
        }
    }
}
