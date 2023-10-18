<?php
/**
 * Class for handling serialization and management of iLoveIMG plugin settings.
 *
 * This class is responsible for managing the serialization and deserialization of iLoveIMG plugin settings, including options for compression, login, registration, and project settings. It also handles the validation of nonces and redirects based on user actions.

 * @since 1.0.0
 */
class iLoveIMG_Compress_Serializer {

    /**
     * Initializes the class and adds an action hook for saving settings.
     */
    public function init() {
        add_action( 'admin_post_update_compress', array( $this, 'save' ) );
    }

    /**
     * Handles the saving of plugin settings based on user actions.
     */
    public function save() {
        if ( ! ( current_user_can( 'manage_options' ) ) ) {
            die();
        }

        if ( isset( $_POST['iloveimg_action'] ) and $this->has_valid_nonce() ) {

            if ( $_POST['iloveimg_action'] == 'iloveimg_action_options_compress' ) {
                $posts_value = array();
                foreach ( $_POST as $key => $post_value ) {
                    if ( strpos( $key, 'iloveimg_field_' ) === 0 ) {
                        $posts_value[ $key ] = wp_unslash( $post_value );
                    }
                }
                update_option( 'iloveimg_options_compress', serialize( $posts_value ) );
            }

            if ( $_POST['iloveimg_action'] == 'iloveimg_action_logout' ) {
                delete_option( 'iloveimg_account' );
                delete_option( 'iloveimg_proyect' );
                $options = unserialize( get_option( 'iloveimg_options_compress' ) );
                unset( $options['iloveimg_field_compress_activated'] );
                unset( $options['iloveimg_field_autocompress'] );
                unset( $options['iloveimg_field_resize_full'] );
                update_option( 'iloveimg_options_compress', serialize( $options ) );
            }

            if ( $_POST['iloveimg_action'] == 'iloveimg_action_login' ) {
                if ( ! isset( $_POST['iloveimg_field_email'] ) and ! isset( $_POST['iloveimg_field_password'] ) ) {
                    $this->redirect();
                }
                $response = wp_remote_post(
                    ILOVE_IMG_COMPRESS_LOGIN_URL,
                    array(
                        'body' => array(
                            'email'        => sanitize_email( wp_unslash( $_POST['iloveimg_field_email'] ) ),
                            'password'     => sanitize_text_field( wp_unslash( $_POST['iloveimg_field_password'] ) ),
                            'wordpress_id' => md5( get_option( 'siteurl' ) . get_option( 'admin_email' ) ),
                        ),
                    )
                );
                if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
                    update_option( 'iloveimg_account', $response['body'] );
                    $options                                      = unserialize( get_option( 'iloveimg_options_compress' ) );
                    $options['iloveimg_field_compress_activated'] = 1;
                    $options['iloveimg_field_autocompress']       = 1;
                    update_option( 'iloveimg_options_compress', serialize( $options ) );
                } else {
                    update_option(
                        'iloveimg_account_error',
                        serialize(
                            array(
								'action' => 'login',
								'email'  => sanitize_email( wp_unslash( $_POST['iloveimg_field_email'] ) ),
                            )
                        )
                    );
                }
            }

            if ( $_POST['iloveimg_action'] == 'iloveimg_action_register' ) {
                if ( ! isset( $_POST['iloveimg_field_name'] ) and ! isset( $_POST['iloveimg_field_email'] ) and ! isset( $_POST['iloveimg_field_password'] ) ) {
                    $this->redirect();
                }
                $response = wp_remote_post(
                    ILOVE_IMG_COMPRESS_REGISTER_URL,
                    array(
                        'body' => array(
                            'name'         => sanitize_text_field( wp_unslash( $_POST['iloveimg_field_name'] ) ),
                            'email'        => sanitize_email( wp_unslash( $_POST['iloveimg_field_email'] ) ),
                            'new_password' => sanitize_text_field( wp_unslash( $_POST['iloveimg_field_password'] ) ),
                            'free_files'   => 0,
                            'wordpress_id' => md5( get_option( 'siteurl' ) . get_option( 'admin_email' ) ),
                        ),
                    )
                );
                if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
                    $key = 'iloveimg_number_registered_' . date( 'Ym' );
                    if ( get_option( $key ) ) {
                        $num = (int) get_option( $key );
                        $num = $num + 1;
                        update_option( $key, $num );
                    } else {
                        update_option( $key, 1 );
                    }
                    if ( (int) get_option( $key ) <= 3 ) {
                        update_option( 'iloveimg_account', $response['body'] );
                        $options                                      = unserialize( get_option( 'iloveimg_options_compress' ) );
                        $options['iloveimg_field_compress_activated'] = 1;
                        $options['iloveimg_field_autocompress']       = 1;
                        update_option( 'iloveimg_options_compress', serialize( $options ) );
                    } else {
                        update_option( 'iloveimg_account_error', serialize( array( 'action' => 'register_limit' ) ) );
                    }
                } else {
                    update_option(
                        'iloveimg_account_error',
                        serialize(
                            array(
                                'action' => 'register',
                                'email'  => sanitize_email( wp_unslash( $_POST['iloveimg_field_email'] ) ),
                                'name'   => sanitize_text_field( wp_unslash( $_POST['iloveimg_field_name'] ) ),
                            )
                        )
                    );
                }
            }

            if ( $_POST['iloveimg_action'] == 'iloveimg_action_proyect' ) {
                if ( ! isset( $_POST['iloveimg_field_proyect'] ) ) {
                    $this->redirect();
                }
                update_option( 'iloveimg_proyect', sanitize_text_field( wp_unslash( $_POST['iloveimg_field_proyect'] ) ) );
            }
        }

        $this->redirect();
	}

    /**
     * Checks if the nonce field is valid.
     *
     * @return bool True if the nonce is valid, false otherwise.
     */
	private function has_valid_nonce() {

		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return false;
		}

		$field = sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) );

		return wp_verify_nonce( $field );
	}

    /**
     * Redirects the user back to the admin page after processing settings.
     */
	private function redirect() {

        // To make the Coding Standards happy, we have to initialize this.
        if ( ! isset( $_POST['_wp_http_referer'] ) ) { // Input var okay.
            $_POST['_wp_http_referer'] = wp_login_url();
        }

        // Sanitize the value of the $_POST collection for the Coding Standards.
        $url = sanitize_text_field(
            wp_unslash( $_POST['_wp_http_referer'] ) // Input var okay.
        );

        // Finally, redirect back to the admin page.
        wp_safe_redirect( urldecode( $url ) );
        exit;
    }
}
