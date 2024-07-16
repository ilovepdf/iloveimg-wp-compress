<?php
use Ilove_Img_Compress\Ilove_Img_Compress_Resources;
?>
<div class="wrap iloveimg_settings">
    <h1><img src="<?php echo esc_url( ILOVE_IMG_COMPRESS_PLUGIN_URL . 'assets/images/logo.svg' ); ?>" class="logo" /></h1>
    
    <div class="iloveimg_settings__overview">
        <?php require_once 'account.php'; ?>
    </div>

    <?php if ( ! $ilove_img_is_logged ) : // @phpstan-ignore-line ?>    
        <div class="iloveimg_settings__info">
            <h3>The power of iLoveIMG in your WordPress!</h3>
            <p>Optimize your website images and improve your page load speed. Reduce the file size of your photos and gain maximum compression while keeping sharp images. Compress your WordPress images to improve the positioning of your site, boost visitor’s engagement and ultimately increase sales.</p>
            <p>Register now to get 2500 free credits and start working with iLoveIMG plugin now!</p>
        </div>
    <?php endif ?>
    
    <div class="iloveimg_settings__options">
        <div class="iloveimg_settings__options-nav">
            <ul>
                <li class="iloveimg_settings__options-nav__selected">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ); ?>">
                        <svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g id="Plugin-WP-iLoveIMG" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Home-Register" transform="translate(-48.000000, -627.000000)"> <g id="compress_20x20" transform="translate(48.000000, 627.000000)"> <path d="M3.2048565,0 L16.7951435,0 C17.9095419,0 18.3136497,0.116032014 18.7210571,0.33391588 C19.1284645,0.551799746 19.4482003,0.871535463 19.6660841,1.27894287 C19.883968,1.68635028 20,2.09045808 20,3.2048565 L20,16.7951435 C20,17.9095419 19.883968,18.3136497 19.6660841,18.7210571 C19.4482003,19.1284645 19.1284645,19.4482003 18.7210571,19.6660841 C18.3136497,19.883968 17.9095419,20 16.7951435,20 L3.2048565,20 C2.09045808,20 1.68635028,19.883968 1.27894287,19.6660841 C0.871535463,19.4482003 0.551799746,19.1284645 0.33391588,18.7210571 C0.116032014,18.3136497 0,17.9095419 0,16.7951435 L0,3.2048565 C0,2.09045808 0.116032014,1.68635028 0.33391588,1.27894287 C0.551799746,0.871535463 0.871535463,0.551799746 1.27894287,0.33391588 C1.68635028,0.116032014 2.09045808,0 3.2048565,0 Z" id="Rectangle-6-Copy-55" fill="#8FBC5D"></path> <circle id="Oval-3" fill="#FFFFFF" cx="10" cy="10" r="1"></circle> <path d="M12.7838471,13.5523527 L12.7737813,15.5554583 C12.772245,15.8611776 12.5231652,16.1102575 12.2174459,16.1117937 C11.9117266,16.11333 11.6651375,15.866741 11.6666738,15.5610217 L11.6833639,12.2396993 C11.6849001,11.93398 11.93398,11.6849001 12.2396993,11.6833639 L15.5610217,11.6666738 C15.866741,11.6651375 16.11333,11.9117266 16.1117937,12.2174459 C16.1102575,12.5231652 15.8611776,12.772245 15.5554583,12.7737813 L13.4321444,12.7844512 L16.5243982,15.8767049 C16.7177374,16.0700442 16.7159563,16.38529 16.5204201,16.5808263 C16.3248838,16.7763625 16.009638,16.7781435 15.8162988,16.5848043 L12.7838471,13.5523527 Z" id="Combined-Shape-Copy-2" fill="#FFFFFF" fill-rule="nonzero"></path> <path d="M7.21773163,6.50963224 L7.22779749,4.50652658 C7.22933376,4.2008073 7.47841361,3.95172745 7.7841329,3.95019117 C8.08985218,3.94865489 8.33644123,4.19524394 8.33490496,4.50096323 L8.31821489,7.82228564 C8.31667862,8.12800492 8.06759877,8.37708477 7.76187948,8.37862105 L4.44055707,8.39531111 C4.13483779,8.39684739 3.88824874,8.15025834 3.88978501,7.84453905 C3.89132129,7.53881977 4.14040114,7.28973992 4.44612043,7.28820364 L6.56943434,7.27753372 L3.47718059,4.18527997 C3.28384137,3.99194076 3.28562242,3.67669491 3.48115867,3.48115867 C3.67669491,3.28562242 3.99194076,3.28384137 4.18527997,3.47718059 L7.21773163,6.50963224 Z" id="Combined-Shape-Copy" fill="#FFFFFF" fill-rule="nonzero"></path> <path d="M4.4505138,13.5523527 L4.44044794,15.5554583 C4.43891166,15.8611776 4.18983182,16.1102575 3.88411253,16.1117937 C3.57839324,16.11333 3.3318042,15.866741 3.33334047,15.5610217 L3.35003054,12.2396993 C3.35156681,11.93398 3.60064666,11.6849001 3.90636595,11.6833639 L7.22768836,11.6666738 C7.53340764,11.6651375 7.77999669,11.9117266 7.77846041,12.2174459 C7.77692414,12.5231652 7.52784429,12.772245 7.222125,12.7737813 L5.09881109,12.7844512 L8.19106484,15.8767049 C8.38440406,16.0700442 8.38262301,16.38529 8.18708676,16.5808263 C7.99155052,16.7763625 7.67630467,16.7781435 7.48296546,16.5848043 L4.4505138,13.5523527 Z" id="Combined-Shape-Copy-8" fill="#FFFFFF" fill-rule="nonzero" transform="translate(5.834123, 14.197659) scale(-1, 1) translate(-5.834123, -14.197659) "></path> <path d="M15.551065,6.50963224 L15.5611308,4.50652658 C15.5626671,4.2008073 15.8117469,3.95172745 16.1174662,3.95019117 C16.4231855,3.94865489 16.6697746,4.19524394 16.6682383,4.50096323 L16.6515482,7.82228564 C16.6500119,8.12800492 16.4009321,8.37708477 16.0952128,8.37862105 L12.7738904,8.39531111 C12.4681711,8.39684739 12.2215821,8.15025834 12.2231183,7.84453905 C12.2246546,7.53881977 12.4737345,7.28973992 12.7794538,7.28820364 L14.9027677,7.27753372 L11.8105139,4.18527997 C11.6171747,3.99194076 11.6189558,3.67669491 11.814492,3.48115867 C12.0100282,3.28562242 12.3252741,3.28384137 12.5186133,3.47718059 L15.551065,6.50963224 Z" id="Combined-Shape-Copy-3" fill="#FFFFFF" fill-rule="nonzero" transform="translate(14.167456, 5.864326) scale(-1, 1) translate(-14.167456, -5.864326) "></path> </g> </g> </g></svg>
                        <span>Compress settings</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-watermark-admin-page' ) ); ?>">
                        <svg width="20px" height="20px" viewBox="0 0 20 20" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"> <g id="Plugin-WP-iLoveIMG" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Home-Register" transform="translate(-48.000000, -667.000000)"> <g id="watermark_20x20" transform="translate(48.000000, 667.000000)"> <path d="M3.2048565,0 L16.7951435,0 C17.9095419,0 18.3136497,0.116032014 18.7210571,0.33391588 C19.1284645,0.551799746 19.4482003,0.871535463 19.6660841,1.27894287 C19.883968,1.68635028 20,2.09045808 20,3.2048565 L20,16.7951435 C20,17.9095419 19.883968,18.3136497 19.6660841,18.7210571 C19.4482003,19.1284645 19.1284645,19.4482003 18.7210571,19.6660841 C18.3136497,19.883968 17.9095419,20 16.7951435,20 L3.2048565,20 C2.09045808,20 1.68635028,19.883968 1.27894287,19.6660841 C0.871535463,19.4482003 0.551799746,19.1284645 0.33391588,18.7210571 C0.116032014,18.3136497 0,17.9095419 0,16.7951435 L0,3.2048565 C0,2.09045808 0.116032014,1.68635028 0.33391588,1.27894287 C0.551799746,0.871535463 0.871535463,0.551799746 1.27894287,0.33391588 C1.68635028,0.116032014 2.09045808,0 3.2048565,0 Z" id="Rectangle-6-Copy-49" fill="#AB6993"></path> <path d="M9.08222222,9.00978182 C9.08222222,9.3536 8.64472222,10.2179364 8.23861111,10.8757 C8.20083333,10.9371364 8.18777778,11.0154818 8.20722222,11.0853727 C8.23805556,11.1961273 8.33777778,11.2730636 8.45138889,11.2730636 L11.5491667,11.2730636 C11.645,11.2730636 11.7327778,11.2181091 11.7755556,11.1310273 C11.8186111,11.0442273 11.81,10.9399545 11.7527778,10.8618909 C11.1752778,10.0736455 10.9177778,9.5024 10.9177778,9.0095 C10.9177778,8.51688182 11.1752778,7.94563636 11.755,7.15344545 C12.02,6.77890909 12.1602778,6.3373 12.1602778,5.87596364 C12.1602778,4.66780909 11.1913889,3.68454545 10,3.68454545 C8.80861111,3.68454545 7.83972222,4.66752727 7.83972222,5.87596364 C7.83972222,6.33701818 7.97972222,6.77890909 8.2475,7.15710909 C8.82472222,7.94563636 9.08222222,8.51688182 9.08222222,9.00978182 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path> <path d="M14.6888889,11.6321 L5.31111111,11.6321 C5.17111111,11.6321 5.0575,11.7473636 5.0575,11.8894 L5.0575,14.4756455 C5.0575,14.6176818 5.17083333,14.7329455 5.31111111,14.7329455 L14.6888889,14.7329455 C14.8288889,14.7329455 14.9425,14.6176818 14.9425,14.4756455 L14.9425,11.8894 C14.9425,11.7473636 14.8288889,11.6321 14.6888889,11.6321 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path> <path d="M13.2544444,15.1094545 L6.74611111,15.1094545 C6.60611111,15.1094545 6.4925,15.2247182 6.4925,15.3667545 L6.4925,15.6584364 C6.4925,15.8004727 6.60583333,15.9157364 6.74611111,15.9157364 L13.2544444,15.9157364 C13.3944444,15.9157364 13.5080556,15.8004727 13.5080556,15.6584364 L13.5080556,15.3667545 C13.5077778,15.2247182 13.3944444,15.1094545 13.2544444,15.1094545 Z" id="Shape" fill="#FFFFFF" fill-rule="nonzero"></path> </g> </g> </g></svg>
                        <span>Watermark settings</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="iloveimg_settings__options-container">
            <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="action" value="update_compress" />
                <p class="submit">
                    <button <?php echo ( ! $ilove_img_is_logged ) ? 'disabled' : ''; ?> type="submit" name="submit" id="submit" class="button button-secondary tooltip">
                        Save Changes
                        <span class="tooltiptext">Register and login with us to save settings changes</span>
                    </button>
                </p>
                <h3>Configure your Compress Images settings</h3>
                <input type="hidden" name="iloveimg_action" value="iloveimg_action_options_compress" />
                <div class="iloveimg_settings__options__field">
                    <div class="switch">
                        <input type="checkbox" name="iloveimg_field_compress_activated" <?php echo isset( $options_value['iloveimg_field_compress_activated'] ) ? 'checked' : ''; ?> />
                        <span class="slider"></span>
                    </div>
                    <label>Compress Activated</label>
                    <p>Activate this plugin in your WordPress dashboard. Activation will work only once you have registered and login as an iLovePDF developer.</p>
                </div>

                <div class="iloveimg_settings__options__field">
                    
                    <div class="switch">
                        <input type="checkbox" name="iloveimg_field_autocompress" <?php echo isset( $options_value['iloveimg_field_autocompress'] ) ? 'checked' : ''; ?> />
                        <span class="slider"></span>
                    </div>
                    <label>Enable Autocompress Images</label>
                    <p>With autocompress enabled any image uploaded to your Media folder will be automatically compressed. Anyway you still will be able to compress uncompressed images from Media.</p>
                </div>
                
                <?php if ( extension_loaded( 'gd' ) ) : ?>
                    <div class="iloveimg_settings__options__field">
                        <label>Images Sizes:</label>
                        <div class="iloveimg_settings__options__field__imagessizes">
                            <p>All uploaded images to media create alternative size versions. Select here which image versions you want to compress.</p>
                            <ul>
                            <?php foreach ( Ilove_Img_Compress_Resources::get_type_images() as $ilove_img_sizes_value ) : ?>
								<?php
								$ilove_img_field_sizes = isset( $options_value['iloveimg_field_sizes'] ) ? $options_value['iloveimg_field_sizes'] : array();
								?>
                                <li>
                                    <input type="checkbox" name="iloveimg_field_sizes[]" value="<?php echo esc_html( $ilove_img_sizes_value['field_id'] ); ?>" <?php echo ( in_array( $ilove_img_sizes_value['field_id'], $ilove_img_field_sizes, true ) ) ? 'checked' : ''; ?> />
                                    <span><?php echo esc_html( $ilove_img_sizes_value['label'] ); ?></span>
                                </li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="iloveimg_settings__options__field" style="margin-bottom: 30px">
                    <div class="switch">
                        <input type="checkbox" name="iloveimg_field_resize_full" <?php echo isset( $options_value['iloveimg_field_resize_full'] ) ? 'checked' : ''; ?> />
                        <span class="slider"></span>
                    </div>
                    <label>Resize full size image:</label>
                    <p>Any uploaded image bigger than the maximum size fixed here, will be downsized to fit into these width and height boundaries. All images will be  resized according to the aspect ratio of the original image.</p>
                    <div class="iloveimg_settings__options__field__resize">
                        <div>
                            <label>Max width</label>
                            <input type="number" name="iloveimg_field_size_full_width" value="<?php echo (int) $options_value['iloveimg_field_size_full_width']; // @phpstan-ignore-line ?>"  min="1"/>
                            <p>Original image width won't exceed this value in pixels.</p>
                        </div>
                        <div>
                            <label>Max height</label>
                            <input type="number" name="iloveimg_field_size_full_height" value="<?php echo (int) $options_value['iloveimg_field_size_full_height']; // @phpstan-ignore-line ?>"  min="1"/>
                            <p>Original image height won't exceed this value in pixels.</p> 
                        </div>
                    </div>      
                </div>

                <div class="iloveimg_settings__options__field-backup">
                    <div class="iloveimg_settings__options__field" style="border-bottom: 0;">
                        
                        <div class="switch">
                            <input type="checkbox" name="iloveimg_field_backup" <?php echo isset( $options_value['iloveimg_field_backup'] ) ? 'checked' : ''; ?> />
                            <span class="slider"></span>
                        </div>
                        <label>Backup original Images</label>
                        <p>Enable this option to make a backup of your images before being compress or watermarked. These backups will allow you to restore your original images at cost of taking server memory space.</p>
                        <p>You can find the original files at: <code>wp-content/uploads/iloveimg-backup</code></p>
                        
                    </div>
                    
                    <div class="iloveimg_settings__options__field">
                        
                        <label>Restore Original Images</label>
                        <p>All backup images can be restored. This action will recover the original images as they were before being stamped with Compress or Watermark. <span style="color: red;">Warning: Any changes made AFTER Watermark/Compress would be also restored.</span></p>
                        <p>You can also clear all your backup images to free memory space. <span style="color: red;">Warning: Clear backups will prevent you to restore original images.</span></p>
                            <button type="button" class="button button-style-iloveimg" id="iloveimg_restore_all" <?php echo ( isset( $options_value['iloveimg_field_backup'] ) && Ilove_Img_Compress_Resources::is_there_backup() ) ? '' : 'disabled'; ?>>Restore All</button>
                        
                            <button type="button" class="button button-remove button-style-iloveimg" id="iloveimg_clear_backup" <?php echo ( isset( $options_value['iloveimg_field_backup'] ) && Ilove_Img_Compress_Resources::is_there_backup() ) ? '' : 'disabled'; ?>>Clear backup</button>
                            <span><?php echo (float) round( Ilove_Img_Compress_Resources::get_size_backup(), 2 ); ?> MB</span>
                    </div>
                </div>
                
                <?php
                wp_nonce_field();

                ?>
                <p class="submit">
                    <button <?php echo ( ! $ilove_img_is_logged ) ? 'disabled' : ''; ?> type="submit" name="submit" id="submit" class="button button-secondary tooltip">
                        Save Changes
                        <span class="tooltiptext">Register and login with us to save settings changes</span>
                    </button>
                </p>
            </form>
        </div>
    </div>
</div><!-- .wrap -->