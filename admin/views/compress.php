<div class="wrap iloveimg_settings">
 
    <img src="<?php echo plugins_url("/iloveimg-compress/assets/images/logo.svg") ?>" class="logo" />
     
    <div class="iloveimg_settings__overview">
        <?php require_once "overview.php"; ?>
    </div>
    
    <div class="iloveimg_settings__options">
        <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
            <?php submit_button(); ?>
            <h3>Configure your Compress Images settings</h3>
            <input type="hidden" name="iloveimg_action" value="iloveimg_action_options_compress" />
            <div class="iloveimg_settings__options__field">
                <div class="switch">
                    <input type="checkbox" name="iloveimg_field_compress_activated" <?php echo isset($options_value['iloveimg_field_compress_activated']) ? "checked" : ""  ?> />
                    <span class="slider"></span>
                </div>
                <label>Compress Activated</label>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.Duis rhoncus velit nec est condimentum feugiat. Donec aliquam augue nec gravida lobortis. Nunc arcu mi, pretium quis dolor id, iaculis euismod ligula. Donec tincidunt gravida lacus eget lacinia.</p>
            </div>

            <div class="iloveimg_settings__options__field">
                
                <div class="switch">
                    <input type="checkbox" name="iloveimg_field_autocompress" <?php echo isset($options_value['iloveimg_field_autocompress']) ? "checked" : ""  ?> />
                    <span class="slider"></span>
                </div>
                <label>Enable Autocompress Images</label>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.Duis rhoncus velit nec est condimentum feugiat. Donec aliquam augue nec gravida lobortis. Nunc arcu mi, pretium quis dolor id, iaculis euismod ligula. Donec tincidunt gravida lacus eget lacinia.</p>
            </div>
            
            
            <div class="iloveimg_settings__options__field">
                <label>Images Sizes:</label>
                <div class="iloveimg_settings__options__field__imagessizes">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing.</p>
                    <ul>
                    <?php foreach(iLoveIMG_Compress_Resources::getTypeImages() as $value): ?>
                        <li>
                            <input type="checkbox" name="iloveimg_field_sizes[]" value="<?php echo $value['field_id'] ?>" <?php echo @(in_array($value['field_id'], $options_value['iloveimg_field_sizes'])) ? "checked" : "" ?> />
                            <span><?php echo $value['label'] ?></span>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <div class="iloveimg_settings__options__field" style="margin-bottom: 30px">
                <div class="switch">
                    <input type="checkbox" name="iloveimg_field_resize_full" <?php echo isset($options_value['iloveimg_field_resize_full']) ? "checked" : ""  ?> />
                    <span class="slider"></span>
                </div>
                <label>Resize full size image:</label>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.Duis rhoncus velit nec est condimentum feugiat. Donec aliquam augue nec gravida lobortis. Nunc arcu mi, pretium quis dolor id, iaculis euismod ligula. Donec tincidunt gravida lacus eget lacinia.</p>
                <div class="iloveimg_settings__options__field__resize">
                    <div>
                        <label>Max width</label>
                        <input type="text" name="iloveimg_field_size_full_width" value="<?php echo $options_value['iloveimg_field_size_full_width'] ?>" />
                    </div>
                    <div>
                        <label>Max height</label>
                        <input type="text" name="iloveimg_field_size_full_height" value="<?php echo $options_value['iloveimg_field_size_full_height'] ?>" />  
                    </div>
                </div>      
            </div>
            


            <?php
            wp_nonce_field( 'iloveimg_settings_save', 'iloveimg_nonce_settings' );
            submit_button();
            ?>
        </form>
    </div>
</div><!-- .wrap -->