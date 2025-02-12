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
        $this->load_textdomain();
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
}
