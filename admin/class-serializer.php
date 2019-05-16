<?php


class iLoveIMG_Compress_Serializer {
 
    public function init() {
        add_action( 'admin_post', array( $this, 'save' ) );
    }
 
    public function save() {
		// First, validate the nonce and verify the user as permission to save.
        // if ( ! ( $this->has_valid_nonce() && current_user_can( 'manage_options' ) ) ) {
            
        // }
        if ( ! (current_user_can( 'manage_options' ) ) ) {
            die();
        }
        
        if($_POST['iloveimg_action'] == 'iloveimg_action_options_compress'){
            $postsValue = [];
            foreach($_POST as $key => $postValue){
                if(strpos($key, "iloveimg_field_") === 0){
                    $postsValue[$key] = $postValue;
                }
            }
            update_option('iloveimg_options_compress', serialize($postsValue));
        }

        if($_POST['iloveimg_action'] == 'iloveimg_action_logout'){
            delete_option('iloveimg_account');
            delete_option('iloveimg_proyect');
            $options = unserialize(get_option('iloveimg_options_compress'));
            unset($options['iloveimg_field_compress_activated']);
            unset($options['iloveimg_field_autocompress']);
            unset($options['iloveimg_field_resize_full']);
            update_option('iloveimg_options_compress', serialize($options));
        }

        if($_POST['iloveimg_action'] == 'iloveimg_action_login'){
            $response = wp_remote_post(ILOVEIMG_LOGIN_URL, 
                array(
                    'body' => array(
                        'email' => sanitize_email($_POST['iloveimg_field_email']), 
                        'password' => sanitize_text_field($_POST['iloveimg_field_password']),
                        'wordpress_id' => md5(get_option('siteurl').get_option('admin_email'))
                    )
                )
            );
            
            update_option('iloveimg_account', $response["body"]);
            $options = unserialize(get_option('iloveimg_options_compress'));
            $options['iloveimg_field_compress_activated'] = 1;
            $options['iloveimg_field_autocompress'] = 1;
            update_option('iloveimg_options_compress', serialize($options));

        }

        

        if($_POST['iloveimg_action'] == 'iloveimg_action_register'){
            $response = wp_remote_post(ILOVEIMG_REGISTER_URL, 
                array(
                    'body' => array(
                        'name' => sanitize_text_field($_POST['iloveimg_field_name']), 
                        'email' => sanitize_email($_POST['iloveimg_field_email']), 
                        'new_password' => sanitize_text_field($_POST['iloveimg_field_password']), 
                        'free_files' => 0, 
                        'wordpress_id' => md5(get_option('siteurl').get_option('admin_email'))
                    )
                )
            );
            update_option('iloveimg_account', $response["body"]);
        }

        if($_POST['iloveimg_action'] == 'iloveimg_action_proyect'){
            update_option('iloveimg_proyect', sanitize_text_field($_POST['iloveimg_field_proyect']));
        }
		
        $this->redirect();
 
	}
	
	private function has_valid_nonce() {
 
		// If the field isn't even in the $_POST, then it's invalid.
		if ( ! isset( $_POST['iloveimg_nonce_settings'] ) ) { // Input var okay.
			return false;
		}
	 
		$field  = wp_unslash( $_POST['iloveimg_nonce_settings'] );
		$action = 'iloveimg_settings_save';
	 
		return wp_verify_nonce( $field, $action );
	 
	}

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