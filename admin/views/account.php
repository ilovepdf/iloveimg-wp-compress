<?php
use Ilove_Img_Compress\Ilove_Img_Compress_Resources;

$ilove_img_is_logged = false;
$ilove_img_account   = array();

if ( get_option( 'iloveimg_account' ) ) {

    if ( ! get_option( 'iloveimg_user_is_migrated' ) ) {

        delete_option( 'iloveimg_account' );
        delete_option( 'iloveimg_proyect' );
        $ilove_img_options = json_decode( get_option( 'iloveimg_options_compress' ), true );
        unset( $ilove_img_options['iloveimg_field_compress_activated'] );
        unset( $ilove_img_options['iloveimg_field_autocompress'] );
        unset( $ilove_img_options['iloveimg_field_resize_full'] );
        Ilove_Img_Compress_Resources::update_option( 'iloveimg_options_compress', wp_json_encode( $ilove_img_options ) );

        wp_safe_redirect( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) );
        exit();
    }

	$ilove_img_account = json_decode( get_option( 'iloveimg_account' ), true );

	$ilove_img_is_logged = true;
    Ilove_Img_Compress_Resources::update_option( 'iloveimg_first_loggued', 1 );
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
        Ilove_Img_Compress_Resources::update_option( 'iloveimg_account', wp_json_encode( $ilove_img_account ) );
	}
} elseif ( get_option( 'iloveimg_account_error' ) ) {
		$ilove_img_account_error = json_decode( get_option( 'iloveimg_account_error' ), true );
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
                        <h3><?php esc_html_e( 'Login to your account', 'iloveimg' ); ?></h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_login" />
                        <div>
                            <input type="email" class="iloveimg_field_email" name="iloveimg_field_email" placeholder="Email" required value="<?php echo isset( $ilove_img_account_error['email'] ) ? esc_html( $ilove_img_account_error['email'] ) : ''; ?>" />
                        </div>
                        <div>
                            <input type="password" class="iloveimg_field_password" name="iloveimg_field_password" placeholder="<?php esc_html_e( 'Password', 'iloveimg' ); ?>" required/>
                        </div>
                        <a class="forget" href="https://iloveapi.com/login/reset" target="_blank"><?php esc_html_e( 'Forget Password?', 'iloveimg' ); ?></a>
                        <?php
                        wp_nonce_field();
                        submit_button( __( 'Login', 'iloveimg' ) );
                        ?>
                        <div>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page&section=register' ) ); ?>"><?php esc_html_e( 'Register as iLoveAPI developer', 'iloveimg' ); ?></a>
                        </div>
                    </form>
                </div>
            <?php else : ?>
                <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-register">
                    <div class="iloveimg_settings__overview__account__picture"></div>
                    <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>" autocomplete="off">
                        <input type="hidden" name="action" value="update_compress" />
                        <h3><?php esc_html_e( 'Register as iLoveAPI developer', 'iloveimg' ); ?></h3>
                        <input type="hidden" name="iloveimg_action" value="iloveimg_action_register" />
                        <div>
                            <div style="width: 100%;">
                                <div>
                                    <input type="text" class="iloveimg_field_name" name="iloveimg_field_name" placeholder="<?php esc_html_e( 'Name', 'iloveimg' ); ?>" required value="<?php echo isset( $ilove_img_account_error['name'] ) ? esc_html( $ilove_img_account_error['name'] ) : ''; ?>"/>
                                </div>
                                <div>
                                    <input type="email" class="iloveimg_field_email" name="iloveimg_field_email" placeholder="<?php esc_html_e( 'Email', 'iloveimg' ); ?>" required value="<?php echo isset( $ilove_img_account_error['email'] ) ? esc_html( $ilove_img_account_error['email'] ) : ''; ?>"/>
                                </div>
                                <div>
                                    <input type="password" class="iloveimg_field_password" name="iloveimg_field_password" placeholder="<?php esc_html_e( 'Password', 'iloveimg' ); ?>" required/>
                                </div>
                            </div>
                            <div>
                                
                                <!-- <div>
                                    <input type="password" class="iloveimg_field_password" name="iloveimg_field_password_confirm" placeholder="<?php esc_html_e( 'Confirm Password', 'iloveimg' ); ?>" required/>
                                </div> -->
                            </div>
                        </div>
                        <?php
                        wp_nonce_field();
                        submit_button( __( 'Register', 'iloveimg' ) );
                        ?>
                        <div>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ); ?>"><?php esc_html_e( 'Login to your account', 'iloveimg' ); ?></a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
    <?php else : ?>
        <div class="iloveimg_settings__overview__account iloveimg_settings__overview__account-logged">
            <div class="iloveimg_settings__overview__account-logged__column_left">
                <div class="iloveimg_settings__overview__account-logged__column_left__stadistics">
                    <h4 style="color: #4D90FE"><?php esc_html_e( 'Free', 'iloveimg' ); ?></h4>
                    <?php
                    $ilove_img_percent = ( ( ( $ilove_img_account['files_used'] * 100 ) / $ilove_img_account['free_files_limit'] ) );
					?>
                    <div class="iloveimg_percent <?php echo ( $ilove_img_percent >= 100 ) ? 'iloveimg_percent-exceeded' : ''; ?> <?php echo ( $ilove_img_percent >= 90 && $ilove_img_percent < 100 ) ? 'iloveimg_percent-warning' : ''; ?>">
                        <div class="iloveimg_percent-total" style="width: <?php echo (float) $ilove_img_percent; ?>%;"></div>
                    </div>
                    <p><?php echo (int) $ilove_img_account['files_used']; ?>/<?php echo (int) $ilove_img_account['free_files_limit']; ?> <?php esc_html_e( 'credits used this month. Free Tier.', 'iloveimg' ); ?></p>
                    <?php if ( $ilove_img_account['subscription_files_limit'] ) : ?>
                        <h4><?php esc_html_e( 'Subscription plan', 'iloveimg' ); ?></h4>
                        <?php
                        $ilove_img_percent = 0;
                        if ( isset( $ilove_img_account['subscription_files_used'] ) && isset( $ilove_img_account['subscription_files_limit'] ) ) {
                            $ilove_img_percent = ( ( $ilove_img_account['subscription_files_used'] * 100 ) / $ilove_img_account['subscription_files_limit'] );
                        }
                        ?>
                        <div class="iloveimg_percent <?php echo ( $ilove_img_percent >= 100 ) ? 'iloveimg_percent-exceeded' : ''; ?> <?php echo ( $ilove_img_percent >= 90 && $ilove_img_percent < 100 ) ? 'iloveimg_percent-warning' : ''; ?>">
                            <div class="iloveimg_percent-total" style="width: <?php echo (float) $ilove_img_percent; ?>%;"></div>
                        </div>
                        <p><?php echo ( isset( $ilove_img_account['subscription_files_used'] ) ) ? (int) $ilove_img_account['subscription_files_used'] : 0; ?>/<?php echo (int) $ilove_img_account['subscription_files_limit']; ?> <?php esc_html_e( 'credits used this month.', 'iloveimg' ); ?></p>
                    <?php endif; ?>
                    <?php if ( $ilove_img_account['package_files_limit'] ) : ?>
                        <h4><?php esc_html_e( 'Prepaid packages.', 'iloveimg' ); ?></h4>
                        <?php $ilove_img_percent = ( ( $ilove_img_account['package_files_used'] * 100 ) / $ilove_img_account['package_files_limit'] ); ?>
                        <div class="iloveimg_percent <?php echo ( $ilove_img_percent >= 100 ) ? 'iloveimg_percent-exceeded' : ''; ?> <?php echo ( $ilove_img_percent >= 90 && $ilove_img_percent < 100 ) ? 'iloveimg_percent-warning' : ''; ?>">
                            <div class="iloveimg_percent-total" style="width: <?php echo (float) $ilove_img_percent; ?>%;"></div>
                        </div>
                        <p><?php echo (int) $ilove_img_account['package_files_used']; ?>/<?php echo (int) $ilove_img_account['package_files_limit']; ?> <?php esc_html_e( 'credits used this month.', 'iloveimg' ); ?></p>
                    <?php endif; ?>
                </div>
                <div class="iloveimg_settings__overview__account-logged__column_left__details">
                    <p style="margin-top: 22px;"><?php esc_html_e( 'Every month since your registry you will get', 'iloveimg' ); ?> <?php echo (int) $ilove_img_account['free_files_limit']; ?> <?php esc_html_e( 'free credits to use to compress or stamp your images.', 'iloveimg' ); ?></p>
                    <p><?php esc_html_e( 'To increase your credits amount you can either open one of our', 'iloveimg' ); ?> <a href="https://iloveapi.com/pricing" target="_blank"><?php esc_html_e( 'subscription plans', 'iloveimg' ); ?></a> <?php esc_html_e( 'to get a fixed amount of additional credits per month or buy a', 'iloveimg' ); ?> <a href="https://iloveapi.com/pricing" target="_blank"><?php esc_html_e( 'single package', 'iloveimg' ); ?></a> <?php esc_html_e( 'of credits.', 'iloveimg' ); ?></p>
                    <a class="button button-secondary" href="https://iloveapi.com/pricing" target="_blank"><?php esc_html_e( 'Buy more credits', 'iloveimg' ); ?></a>
                </div>
            </div>
            <div class="iloveimg_settings__overview__account-logged__column_right">
                <form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="update_compress" />
                    <input type="hidden" name="iloveimg_action" value="iloveimg_action_logout" />
                    <h3><?php esc_html_e( 'Account', 'iloveimg' ); ?></h3>
                    <p style="margin: 0"><?php echo esc_html( $ilove_img_account['name'] ); ?></p>
                    <p style="margin-top: 0; color: #4D90FE;"><?php echo esc_html( $ilove_img_account['email'] ); ?></p>
                    
                    <?php wp_nonce_field(); ?>
                    <?php submit_button( __( 'Logout', 'iloveimg' ) ); ?>
                </form>

                <form class="iloveimg_settings__overview__account-logged__column_right-proyects" method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">
                    <input type="hidden" name="action" value="update_compress" />
                    <input type="hidden" name="iloveimg_action" value="iloveimg_action_proyect" />
                    <p><label>
                        <?php esc_html_e( 'Select your working proyect', 'iloveimg' ); ?>
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
                        <button type="submit" class="button button-secondary"><?php esc_html_e( 'Save', 'iloveimg' ); ?></button>
                    </p>
                    <?php wp_nonce_field(); ?>
                    
                </form>
            </div>
        </div>
    <?php endif; ?>