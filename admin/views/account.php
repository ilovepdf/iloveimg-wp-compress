<?php
$ilove_img_is_logged = false;

if ( get_option( 'iloveimg_account' ) ) {
	$ilove_img_account = json_decode( get_option( 'iloveimg_account' ), true );

	$ilove_img_is_logged = true;
	update_option( 'iloveimg_first_loggued', 1 );
	$ilove_img_token    = $ilove_img_account['token'];
	$ilove_img_response = wp_remote_get(
        ILOVE_IMG_COMPRESS_USER_URL . '/' . $ilove_img_account['id'],
		array(
			'headers' => array( 'Authorization' => 'Bearer ' . $ilove_img_token ),
		)
	);

	if ( isset( $ilove_img_response['response']['code'] ) && 200 === (int) $ilove_img_response['response']['code'] ) {
		$ilove_img_account          = json_decode( $ilove_img_response['body'], true );
		$ilove_img_account['token'] = $ilove_img_token;
		update_option( 'iloveimg_account', wp_json_encode( $ilove_img_account ) );
	}
} elseif ( get_option( 'iloveimg_account_error' ) ) {
		$ilove_img_account_error = unserialize( get_option( 'iloveimg_account_error' ) );
		delete_option( 'iloveimg_account_error' );
}

$ilove_img_get_section = isset( $_GET['section'] ) ? sanitize_text_field( wp_unslash( $_GET['section'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
?>
    <?php if ( ! $ilove_img_is_logged ) : ?>
            <?php if ( 'register' !== $ilove_img_get_section ) : ?>
                <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-login">
                    <div class="iloveimg_settings__overview__account__picture"></div>
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
                        <input type="hidden" name="action" value="update_compress" />
                        <h3>Login to your account</h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_login" />
                        <div>
                            <input type="email" class="iloveimg_field_email" name="iloveimg_field_email" placeholder="Email" required value="<?php echo isset( $ilove_img_account_error['email'] ) ? esc_html( $ilove_img_account_error['email'] ) : ''; ?>" />
                        </div>
                        <div>
                            <input type="password" class="iloveimg_field_password" name="iloveimg_field_password" placeholder="Password" required/>
                        </div>
                        <a class="forget" href="https://developer.iloveimg.com/login/reset" target="_blank">Forget Password?</a>
                        <?php
                        wp_nonce_field();
                        submit_button( 'Login' );
                        ?>
                        <div>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page&section=register' ) ); ?>">Register as iLovePDF developer</a>
                        </div>
                    </form>
                </div>
            <?php else : ?>
                <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-register">
                    <div class="iloveimg_settings__overview__account__picture"></div>
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
                        <input type="hidden" name="action" value="update_compress" />
                        <h3>Register as iLovePDF developer</h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_register" />
                        <div>
                            <div style="width: 100%;">
                                <div>
                                    <input type="text" class="iloveimg_field_name" name="iloveimg_field_name" placeholder="Name" required value="<?php echo isset( $ilove_img_account_error['name'] ) ? esc_html( $ilove_img_account_error['name'] ) : ''; ?>"/>
                                </div>
                                <div>
                                    <input type="email" class="iloveimg_field_email" name="iloveimg_field_email" placeholder="Email" required value="<?php echo isset( $ilove_img_account_error['email'] ) ? esc_html( $ilove_img_account_error['email'] ) : ''; ?>"/>
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
                        wp_nonce_field();
                        submit_button( 'Register' );
                        ?>
                        <div>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ); ?>">Login to your account</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
    <?php else : ?>
        <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-logged">
            <div class="iloveimg_settings__overview__account-logged__column_left">
                <div class="iloveimg_settings__overview__account-logged__column_left__stadistics">
                    <h4 style="color: #4D90FE">Free</h4>
                    <?php $ilove_img_percent = ( ( ( $ilove_img_account['files_used'] * 100 ) / $ilove_img_account['free_files_limit'] ) ); ?>
                    <div class="iloveimg_percent <?php echo ( $ilove_img_percent >= 100 ) ? 'iloveimg_percent-exceeded' : ''; ?> <?php echo ( $ilove_img_percent >= 90 && $ilove_img_percent < 100 ) ? 'iloveimg_percent-warning' : ''; ?>">
                        <div class="iloveimg_percent-total" style="width: <?php echo (int) $ilove_img_percent; ?>%;"></div>
                    </div>
                    <p><?php echo (int) $ilove_img_account['files_used']; ?>/<?php echo (int) $ilove_img_account['free_files_limit']; ?> processed files this month. Free Tier.</p>
                    <?php if ( $ilove_img_account['subscription_files_limit'] ) : ?>
                        <h4>Subscription files</h4>
                        <?php
                        $ilove_img_percent = 0;
                        if ( isset( $ilove_img_account['subscription_files_used'] ) && isset( $ilove_img_account['subscription_files_limit'] ) ) {
                            $ilove_img_percent = ( ( $ilove_img_account['subscription_files_used'] * 100 ) / $ilove_img_account['subscription_files_limit'] );
                        }
                        ?>
                        <div class="iloveimg_percent <?php echo ( $ilove_img_percent >= 100 ) ? 'iloveimg_percent-exceeded' : ''; ?> <?php echo ( $ilove_img_percent >= 90 && $ilove_img_percent < 100 ) ? 'iloveimg_percent-warning' : ''; ?>">
                            <div class="iloveimg_percent-total" style="width: <?php echo (int) $ilove_img_percent; ?>%;"></div>
                        </div>
                        <p><?php echo ( isset( $ilove_img_account['subscription_files_used'] ) ) ? (int) $ilove_img_account['subscription_files_used'] : 0; ?>/<?php echo (int) $ilove_img_account['subscription_files_limit']; ?> processed files this month.</p>
                    <?php endif; ?>
                    <?php if ( $ilove_img_account['package_files_limit'] ) : ?>
                        <h4>Package files</h4>
                        <?php $ilove_img_percent = ( ( $ilove_img_account['package_files_used'] * 100 ) / $ilove_img_account['package_files_limit'] ); ?>
                        <div class="iloveimg_percent <?php echo ( $ilove_img_percent >= 100 ) ? 'iloveimg_percent-exceeded' : ''; ?> <?php echo ( $ilove_img_percent >= 90 && $ilove_img_percent < 100 ) ? 'iloveimg_percent-warning' : ''; ?>">
                            <div class="iloveimg_percent-total" style="width: <?php echo (int) $ilove_img_percent; ?>%;"></div>
                        </div>
                        <p><?php echo (int) $ilove_img_account['package_files_used']; ?>/<?php echo (int) $ilove_img_account['package_files_limit']; ?> processed files this month.</p>
                    <?php endif; ?>
                </div>
                <div class="iloveimg_settings__overview__account-logged__column_left__details">
                    <p style="margin-top: 22px;">Every month since your registry you will get <?php echo (int) $ilove_img_account['free_files_limit']; ?> free file processes to use to compress or stamp your images.</p>
                    <p>To increase your file processes amount you can either open one of our <a href="https://developer.iloveimg.com/pricing" target="_blank">subscription plans</a> to get a fixed amount of additional processes per month or buy a <a href="https://developer.iloveimg.com/pricing" target="_blank">single package</a> of file processes.</p>
                    <a class="button button-secondary" href="https://developer.iloveimg.com/pricing" target="_blank">Buy more files</a>
                </div>
            </div>
            <div class="iloveimg_settings__overview__account-logged__column_right">
                <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="update_compress" />
                    <input type="hidden" name="iloveimg_action" value="iloveimg_action_logout" />
                    <h3>Account</h3>
                    <p style="margin: 0"><?php echo esc_html( $ilove_img_account['name'] ); ?></p>
                    <p style="margin-top: 0; color: #4D90FE;"><?php echo esc_html( $ilove_img_account['email'] ); ?></p>
                    
                    <?php wp_nonce_field(); ?>
                    <?php submit_button( 'Logout' ); ?>
                </form>

                <form class="iloveimg_settings__overview__account-logged__column_right-proyects" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="update_compress" />
                    <input type="hidden" name="iloveimg_action" value="iloveimg_action_proyect" />
                    <p><label>
                        Select your working proyect
                    </label>
                        <select name="iloveimg_field_proyect">
                            <?php foreach ( $ilove_img_account['projects'] as $ilove_img_key => $ilove_img_project ) : ?>
                                <option value="<?php echo esc_attr( $ilove_img_project['public_key'] ); ?>#<?php echo esc_attr( $ilove_img_project['secret_key'] ); ?>" 
                                    <?php
									if ( get_option( 'iloveimg_proyect' ) === $ilove_img_project['public_key'] . '#' . $ilove_img_project['secret_key'] ) {
										echo 'selected';
									}
                                    ?>
                                ><?php echo esc_html( $ilove_img_project['name'] ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="button button-secondary">Save</button>
                    </p>
                    <?php wp_nonce_field(); ?>
                    
                </form>
            </div>
        </div>
    <?php endif; ?>