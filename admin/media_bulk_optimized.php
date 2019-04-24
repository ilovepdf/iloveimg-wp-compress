<?php



// Extend the class
class Media_Bukl_Optimized extends AdminPageFramework {
    /**
     * The set-up method which is triggered automatically with the 'wp_loaded' hook.
     *
     * Here we define the setup() method to set how many pages, page titles and icons etc.
     */
    public function setUp() {
        // Create the root menu - specifies to which parent menu to add.
        // the available built-in root menu labels: Dashboard, Posts, Media, Links, Pages, Comments, Appearance, Plugins, Users, Tools, Settings, Network Admin
        $this->setRootMenuPage( 'Media' );
        // Add the sub menus and the pages
        $this->addSubMenuItems(
            array(
                'title'     => 'Optimize Images',        // the page and menu title
                'page_slug' => 'iloveimg_image_optimized',         // the page slug
            )
        );
    }
    /**
     * One of the pre-defined methods which is triggered when the registered page loads.
     *
     * Here we add form fields.
     * @callback        action      load_{page slug}
     */
    public function load_iloveimg_image_optimized( $oAdminPage ) {
        
    }
    /**
     * One of the pre-defined methods which is triggered when the page contents is going to be rendered.
     * @callback        action      do_{page slug}
     */
    public function do_iloveimg_image_optimized() {
        ?>
        <div class="wpheader">
            <img src="https://www.iloveimg.com/img/iloveimg.svg" width="auto" height="29">
        </div>
        <div class="iloveimg_optimize">
            <div class="iloveimg__optimize__details">
                <div class="iloveimg__optimize__details-cols">
                    <div class="iloveimg__optimize__details-overview">
                        <h3>Overview</h3>
                        <div class="iloveimg__cols">
                            <div class="iloveimg__col_1">
                                <p class="totalImages"><strong>120</strong> Total images you optimized with iLoveIMG</p>
                                <div class="iloveimg_bar iloveimg_bar-original">
                                    <p>Original size</p>
                                    <div class="iloveimg_bar-percent">
                                        <div class="iloveimg_bar-percent-bar"></div>
                                        <div class="iloveimg_bar-percent-mb">26.3 MB</div>
                                    </div>
                                </div>
                                <div class="iloveimg_bar">
                                    <p>Optimized size</p>
                                    <div class="iloveimg_bar-percent">
                                        <div style="width: 40%;" class="iloveimg_bar-percent-bar"></div>
                                        <div class="iloveimg_bar-percent-mb">12.8 MB</div>
                                    </div>
                                </div>
                                <div class="iloveimg_totalsaved">
                                    <div class="iloveimg_totalsaved-percent">70%</div>
                                    <p>that's the size you saved by using iLoveIMG</p>
                                </div>
                            </div>
                            <div class="iloveimg__col_2">
                                <?php echo "hol2a" ?>
                            </div>
                        </div>
                    </div>
                    <div class="iloveimg__optimize__details-account">
                    </div>
                </div>
                <div class="iloveimg__optimize__details-buttons">
                     <input type="submit" name="id-start" id="id-start" class="button button-primary button-hero" value="Compress Images">
                </div>
            </div>
            
        </div>
        <?php
    }
}
// Instantiate the class object.
new Media_Bukl_Optimized;