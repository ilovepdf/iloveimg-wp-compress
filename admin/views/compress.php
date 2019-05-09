<div class="wrap">
 
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
     
    <?php 
    $isLogged = false;
    if(get_option('iloveimg_account')){
        $account = json_decode(get_option('iloveimg_account'), true);
        if(array_key_exists('error', $account)){
            ?>
            <div>
                <p><?php echo $account['error']['message'] ?></p>
                <?php foreach($account['error']['param'] as $params): ?>
                    <?php foreach($params as $value): ?>
                    <p><?php echo $value ?></p>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <?php
            delete_option('iloveimg_account');
        }else{
            $isLogged =  true;
        }
    }
    ?>
    <?php if(!$isLogged): ?>
            <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="iloveimg_action" value="iloveimg_action_login" />
                <div>
                    <label>Email:</label>
                    <input type="email" name="iloveimg_field_email" required/>
                </div>
                <div>
                    <label>Password:</label>
                    <input type="password" name="iloveimg_field_password" required/>
                </div>
            <?php
            wp_nonce_field( 'iloveimg_login', 'iloveimg_nonce_login' );
            submit_button('Login');
            ?>
            </form>

            

            <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                <input type="hidden" name="iloveimg_action" value="iloveimg_action_register" />    
                <div>
                    <label>Name:</label>
                    <input type="text" name="iloveimg_field_name" required/>
                </div>
                <div>
                    <label>Email:</label>
                    <input type="email" name="iloveimg_field_email" required/>
                </div>
                <div>
                    <label>Password:</label>
                    <input type="password" name="iloveimg_field_password" required/>
                </div>
                <div>
                    <label>Confirm Password:</label>
                    <input type="password" name="iloveimg_field_password_confirm" required/>
                </div>
            <?php
            wp_nonce_field( 'iloveimg_register', 'iloveimg_nonce_register' );
            submit_button('Register');
            ?>
            </form>
    <?php else: ?>
        <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="iloveimg_action" value="iloveimg_action_logout" />
            <h3>Account</h3>
            <p>Name: <?php echo $account['name'] ?></p>
            <p>Email: <?php echo $account['email'] ?></p>
            
            <?php  wp_nonce_field( 'iloveimg_logout', 'iloveimg_nonce_logout' );  ?>
            <?php submit_button('Logout'); ?>
        </form>

        <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="iloveimg_action" value="iloveimg_action_proyect" />
            <h3>Proyects</h3>
            <p>Select your working proyect
                <select name="iloveimg_field_proyect">
                    <?php foreach ($account['projects'] as $key => $project):  ?>
                        <option value="<?php echo $project['public_key'] ?>#<?php echo $project['secret_key'] ?>"><?php echo $project['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <?php  wp_nonce_field( 'iloveimg_proyect', 'iloveimg_nonce_proyect' );  ?>
            <?php submit_button(); ?>
        </form>

    <?php endif;?>
    
    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="iloveimg_action" value="iloveimg_action_options_compress" />
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