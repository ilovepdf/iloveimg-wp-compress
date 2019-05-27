<?php 
    $isLogged = false;
    if(get_option('iloveimg_account')){
        $account = json_decode(get_option('iloveimg_account'), true);
        
        $isLogged =  true;
        update_option('iloveimg_first_loggued', 1);
        $token = $account['token'];
        $response = wp_remote_get(iLoveIMG_Compress_USER_URL.'/'.$account['id'], 
            array(
                'headers' => array('Authorization' => 'Bearer '.$token)
            )
        );

        if (isset($response['response']['code']) && $response['response']['code'] == 200) {
            $account = json_decode($response["body"], true);
            $account['token'] = $token;
            update_option('iloveimg_account', json_encode($account));
        }
    }else{
        if(get_option('iloveimg_account_error')){
            $iloveimg_account_error = unserialize(get_option('iloveimg_account_error'));
            delete_option('iloveimg_account_error');
        }
    }
    ?>
    <?php if(!$isLogged): ?>
            
            <?php if(@$_GET['section'] != 'register'): ?>
                <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-login">
                    <!-- <img src="<?php echo plugins_url("/iloveimg-compress/assets/images/iloveimg_picture_login.svg") ?>" /> -->
                    <div class="iloveimg_settings__overview__account__picture"></div>
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
                        <h3>Login to your account</h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_login" />
                        <div>
                            <input type="email" class="iloveimg_field_email" name="iloveimg_field_email" placeholder="Email" required value="<?php echo isset($iloveimg_account_error['email']) ? $iloveimg_account_error['email'] : "" ?>" />
                        </div>
                        <div>
                            <input type="password" class="iloveimg_field_password" name="iloveimg_field_password" placeholder="Password" required/>
                        </div>
                        <a class="forget" href="https://developer.ilovepdf.com/login/reset" target="_blank">Forget Password?</a>
                        <?php
                        wp_nonce_field( 'iloveimg_login', 'iloveimg_nonce_login' );
                        submit_button('Login');
                        ?>
                        <div>
                            <a href="<?php echo admin_url( 'admin.php?page=iloveimg-admin-page&section=register' ) ?>">Register as iLovePDF developer</a>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-register">
                    <div class="iloveimg_settings__overview__account__picture"></div>
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
                        <h3>Register as iLovePDF developer</h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_register" />
                        <div>
                            <div style="width: 100%;">
                                <div>
                                    <input type="text" class="iloveimg_field_name" name="iloveimg_field_name" placeholder="Name" required value="<?php echo isset($iloveimg_account_error['name']) ? $iloveimg_account_error['name'] : "" ?>"/>
                                </div>
                                <div>
                                    <input type="email" class="iloveimg_field_email" name="iloveimg_field_email" placeholder="Email" required value="<?php echo isset($iloveimg_account_error['email']) ? $iloveimg_account_error['email'] : "" ?>"/>
                                </div>
                                <div>
                                    <input type="password" class="iloveimg_field_password" name="iloveimg_field_password" placeholder="Password" required/>
                                </div>
                            </div>
                            <div>
                                
                                <!-- <div>
                                    <input type="password" class="iloveimg_field_password" name="iloveimg_field_password_confirm" placeholder="Confirm Password" required/>
                                </div> -->
                            </div>
                        </div>
                        <?php
                        wp_nonce_field( 'iloveimg_register', 'iloveimg_nonce_register' );
                        submit_button('Register');
                        ?>
                        <div>
                            <a href="<?php echo admin_url( 'admin.php?page=iloveimg-admin-page' ) ?>">Login to your account</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
    <?php else: ?>
        <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-logged">
            <div class="iloveimg_settings__overview__account-logged__column_left">
                <div class="iloveimg_settings__overview__account-logged__column_left__stadistics">
                    <h4 style="color: #4D90FE">Free</h4>
                    <?php $percent = ((($account['files_used']*100)/$account['free_files_limit'])); ?>
                    <div class="iloveimg_percent <?php echo ($percent >= 100) ? 'iloveimg_percent-exceeded':'' ?> <?php echo ($percent >= 90 and $percent < 100) ? 'iloveimg_percent-warning':'' ?>">
                        <div class="iloveimg_percent-total" style="width: <?php echo $percent ?>%;"></div>
                    </div>
                    <p><?php echo $account['files_used'] ?>/<?php echo $account['free_files_limit'] ?> processed files this month. Free Tier.</p>
                    <?php if($account['subscription_files_limit']): ?>
                        <h4>Subscription files</h4>
                        <?php $percent = @((($account['subscription_files_used']*100)/$account['subscription_files_limit'])); ?>
                        <div class="iloveimg_percent <?php echo ($percent >= 100) ? 'iloveimg_percent-exceeded':'' ?> <?php echo ($percent >= 90 and $percent < 100) ? 'iloveimg_percent-warning':'' ?>">
                            <div class="iloveimg_percent-total" style="width: <?php echo $percent ?>%;"></div>
                        </div>
                        <p><?php echo (isset($account['subscription_files_used'])) ? $account['subscription_files_used'] : 0 ?>/<?php echo $account['subscription_files_limit'] ?> processed files this month.</p>
                    <?php endif; ?>
                    <?php if($account['package_files_limit']): ?>
                        <h4>Package files</h4>
                        <?php $percent = (($account['package_files_used']*100)/$account['package_files_limit']); ?>
                        <div class="iloveimg_percent <?php echo ($percent >= 100) ? 'iloveimg_percent-exceeded':'' ?> <?php echo ($percent >= 90 and $percent < 100) ? 'iloveimg_percent-warning':'' ?>">
                            <div class="iloveimg_percent-total" style="width: <?php echo $percent ?>%;"></div>
                        </div>
                        <p><?php echo $account['package_files_used'] ?>/<?php echo $account['package_files_limit'] ?> processed files this month.</p>
                    <?php endif; ?>
                </div>
                <div class="iloveimg_settings__overview__account-logged__column_left__details">
                    <p style="margin-top: 22px;">Every month since your registry you will get <?php echo $account['free_files_limit'] ?> free file processes to use to compress or stamp your images.</p>
                    <p>To increase your file processes amount you can either open one of our <a href="https://developer.ilovepdf.com/pricing" target="_blank">subscription plans</a> to get a fixed amount of additional processes per month or buy a <a href="https://developer.ilovepdf.com/pricing" target="_blank">single package</a> of file processes.</p>
                    <a class="button button-secondary" href="https://developer.ilovepdf.com/pricing" target="_blank">Buy more files</a>
                </div>
            </div>
            <div class="iloveimg_settings__overview__account-logged__column_right">
                <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="iloveimg_action" value="iloveimg_action_logout" />
                    <h3>Account</h3>
                    <p style="margin: 0"><?php echo $account['name'] ?></p>
                    <p style="margin-top: 0; color: #4D90FE;"><?php echo $account['email'] ?></p>
                    
                    <?php  wp_nonce_field( 'iloveimg_logout', 'iloveimg_nonce_logout' );  ?>
                    <?php submit_button('Logout'); ?>
                </form>

                <form class="iloveimg_settings__overview__account-logged__column_right-proyects" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="iloveimg_action" value="iloveimg_action_proyect" />
                    <p><label>
                        Select your working proyect
                    </label>
                        <select name="iloveimg_field_proyect">
                            <?php foreach ($account['projects'] as $key => $project):  ?>
                                <option value="<?php echo $project['public_key'] ?>#<?php echo $project['secret_key'] ?>" 
                                    <?php
                                        if(get_option('iloveimg_proyect') == $project['public_key'] . "#" . $project['secret_key']){
                                            echo "selected";
                                        }
                                    ?>
                                ><?php echo $project['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button button-secondary">Save</button>
                    </p>
                    <?php  wp_nonce_field( 'iloveimg_proyect', 'iloveimg_nonce_proyect' );  ?>
                    
                </form>
            </div>
        </div>
        

    <?php endif;?>