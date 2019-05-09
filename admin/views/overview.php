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
            
            <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                <h3>Login</h3>
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
                <h3>Register</h3>
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

        <div>
            <h3>Overview</h3>
            <h4>Free</h4>
            
            <p><?php echo $account['files_used'] ?>/<?php echo $account['free_files_limit'] ?> processed files this month. Free Tier.</p>
            <?php if($account['subscription_files_limit']): ?>
                <h4>Subscription</h4>
                <p><?php echo $account['files_used'] ?>/<?php echo $account['subscription_files_limit'] ?> processed files this month.</p>
            <?php endif; ?>
            <?php if($account['package_files_limit']): ?>
                <h4>Package</h4>
                <p><?php echo $account['package_files_used'] ?>/<?php echo $account['package_files_limit'] ?> processed files this month.</p>
            <?php endif; ?>

            <a href="https://developer.ilovepdf.com/pricing" target="_blank">Buy more files</a>

            <!-- <pre>
            <?php 
            print_r($account)
            ?>
            </pre> -->
        </div>

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