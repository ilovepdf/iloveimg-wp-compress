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
            $token = $account['token'];
            $response = wp_remote_get(ILOVEIMG_USER_URL.'/'.$account['id'], 
                array(
                    'headers' => array('Authorization' => 'Bearer '.$token)
                )
            );

            if (isset($response['response']['code']) && $response['response']['code'] == 200) {
                $account = json_decode($response["body"], true);
                $account['token'] = $token;
                update_option('iloveimg_account', json_encode($account));
            }
        }
    }
    ?>
    <?php if(!$isLogged): ?>
            
            <?php if(@$_GET['section'] != 'register'): ?>
                <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-login">
                    <img src="<?php echo plugins_url("/iloveimg-compress/assets/images/iloveimg_picture_login.svg") ?>" />
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                        <h3>Login to your account</h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_login" />
                        <div>
                            <input type="email" name="iloveimg_field_email" placeholder="Email" required/>
                        </div>
                        <div>
                            <input type="password" name="iloveimg_field_password" placeholder="Password" required/>
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
                    <img src="<?php echo plugins_url("/iloveimg-compress/assets/images/iloveimg_picture_register.svg") ?>" />
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                        <h3>Register as iLovePDF developer</h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_register" />
                        <div>
                            <div>
                                <div>
                                    <input type="text" name="iloveimg_field_name" placeholder="Name" required/>
                                </div>
                                <div>
                                    <input type="email" name="iloveimg_field_email" placeholder="Email" required/>
                                </div>
                            </div>
                            <div>
                                <div>
                                    <input type="password" name="iloveimg_field_password" placeholder="Password" required/>
                                </div>
                                <div>
                                    <input type="password" name="iloveimg_field_password_confirm" placeholder="Confirm Password" required/>
                                </div>
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
                    <div class="iloveimg_percent">
                        <div class="iloveimg_percent-total" style="width: <?php echo ((($account['files_used']*100)/$account['free_files_limit'])) ?>%;"></div>
                    </div>
                    <p><?php echo $account['files_used'] ?>/<?php echo $account['free_files_limit'] ?> processed files this month. Free Tier.</p>
                    <?php if($account['subscription_files_limit']): ?>
                        <h4>Subscription files</h4>
                        <div class="iloveimg_percent">
                            <div class="iloveimg_percent-total" style="width: <?php echo @((($account['subscription_files_used']*100)/$account['subscription_files_limit'])) ?>%;"></div>
                        </div>
                        <p><?php echo (isset($account['subscription_files_used'])) ? $account['subscription_files_used'] : 0 ?>/<?php echo $account['subscription_files_limit'] ?> processed files this month.</p>
                    <?php endif; ?>
                    <?php if($account['package_files_limit']): ?>
                        <h4>Package files</h4>
                        <div class="iloveimg_percent">
                            <div class="iloveimg_percent-total" style="width: <?php echo (($account['package_files_used']*100)/$account['package_files_limit']) ?>%;"></div>
                        </div>
                        <p><?php echo $account['package_files_used'] ?>/<?php echo $account['package_files_limit'] ?> processed files this month.</p>
                    <?php endif; ?>
                </div>
                <div class="iloveimg_settings__overview__account-logged__column_left__details">
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Cras sed sapien quam. Sed dapibus est id enim facilisis, at posuere turpis adipiscing. Quisque sit amet dui dui.</p>
                    <p>Duis rhoncus velit nec est condimentum feugiat. Donec aliquam augue nec gravida lobortis.</p>
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