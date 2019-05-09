<div class="wrap">
 
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
     
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">

        <div>
            <label>Compress Activated:</label>
            <input type="checkbox" name="iloveimg_field_compress_activated" <?php echo isset($options_value['iloveimg_field_compress_activated']) ? "checked" : ""  ?> />
        </div>

        <div>
            <label>Enable Autocompress Images:</label>
            <input type="checkbox" name="iloveimg_field_autocompress" <?php echo isset($options_value['iloveimg_field_autocompress']) ? "checked" : ""  ?> />
        </div>
        
        
        <div>
            <label>Images Sizes:</label>
            <ul>
            <?php foreach(iLoveIMG_Compress_Resources::getTypeImages() as $value): ?>
                <li>
                    <input type="checkbox" name="iloveimg_field_sizes[]" value="<?php echo $value['field_id'] ?>" <?php echo @(in_array($value['field_id'], $options_value['iloveimg_field_sizes'])) ? "checked" : "" ?> />
                    <span><?php echo $value['label'] ?></span>
                </li>
            <?php endforeach; ?>
            </ul>
            
        </div>

        <div>
            <label>Resize full size image:</label>
            <input type="checkbox" name="iloveimg_field_resize_full" <?php echo isset($options_value['iloveimg_field_resize_full']) ? "checked" : ""  ?> />

            Width:
            <input type="text" name="iloveimg_field_size_full_width" value="<?php echo $options_value['iloveimg_field_size_full_width'] ?>" />
            Height:
            <input type="text" name="iloveimg_field_size_full_height" value="<?php echo $options_value['iloveimg_field_size_full_height'] ?>" />        
        </div>
        


        <?php
        wp_nonce_field( 'iloveimg_settings_save', 'iloveimg_nonce_settings' );
        submit_button();
        ?>
    </form>
 
</div><!-- .wrap -->