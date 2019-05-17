<?php
/**
 * Creates the submenu page for the plugin.
 *
 * @package Custom_Admin_Settings
 */
 
/**
 * Creates the submenu page for the plugin.
 *
 * Provides the functionality necessary for rendering the page corresponding
 * to the submenu with which this page is associated.
 *
 * @package Custom_Admin_Settings
 */
class iLoveIMG_Compress_Submenu_Page {
 
        
    public function renderParent() {
            
    }
	
	public function renderCompress() {
                $options_value = unserialize(get_option('iloveimg_options_compress'));
                require_once('views/compress.php');
	}

	public function renderWatermark() {
                $options_value = unserialize(get_option('iloveimg_options_compress'));
                require_once('views/watermark.php');
	}

	public function renderMediaOptimization(){
		
	}
	
}