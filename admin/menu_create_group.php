<?php

// Extend the class
class iLoveIMG_CreatePageGroup extends AdminPageFramework {
    // Define the setUp() method to set how many pages, page titles and icons etc.
    public function setUp() {
        // Create the root menu
        $this->setRootMenuPage(
            'iLoveIMG',    // specify the name of the page group
            'https://www.iloveimg.com/img/favicons-img/favicon-16x16.png'    // use 16 by 16 image for the menu icon.
        );
        // Add the sub menus and the pages.
        // The third parameter accepts screen icon url that appears at the top of the page.
        $this->addSubMenuItems(
            array(
		    	'title' => 'Account',        // page title
                'page_slug' => 'iloveimg_account',    // page slug
                'style'         => array(
                    plugins_url( '/assets/css/main.css', dirname(__FILE__) ), 
                )
		    ),
		    array(
		    	'title' => 'Compress Options',        // page title
                'page_slug' => 'iloveimg_compress_options',    // page slug
                'style'         => array(
                    plugins_url( '/assets/css/main.css', dirname(__FILE__) ), 
                )
		    ),
		    array(
		    	'title' => 'Watermark Options',        // page title
                'page_slug' => 'iloveimg_watermark_options',    // page slug
                'style'         => array(
                    plugins_url( '/assets/css/main.css', dirname(__FILE__) ), 
                ),
                'script'         => array(
                   //plugins_url( '/assets/js/main.js', dirname(__FILE__) ), 
                )
		    ),
		    array(
		    	'title'	=>	__( 'Official Web', 'admin-page-framework-demo' ),
		    	'href'	=>	'https://developer.ilovepdf.com/',
		    	'show_page_heading_tab'	=>	false,
		    )
        );
        // You can add more pages as many as you want!
        
    }
    // Action hook methods: 'do_' + page slug.
    public function do_iloveimg_account() {
        ?>
        <?php
    }
    public function do_iloveimg_compress_options() {
        ?>
        <?php
    }
    public function do_iloveimg_watermark_options() {
        ?>
        <?php
    }

    public function do_before_iLoveIMG_CreatePageGroup(){
        ?>
        <div class="wpheader">
            <img src="https://www.iloveimg.com/img/iloveimg.svg" width="auto" height="29">
        </div>
        <?php
    }
    
    // There are more available filters and actions! Please refer to Demo 06 - Hooks.
}