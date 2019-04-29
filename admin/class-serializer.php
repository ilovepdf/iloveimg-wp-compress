<?php

class Serializer {
 
    public function init() {
        add_action( 'admin_post', array( $this, 'save' ) );
    }
 
    public function save() {
		// First, validate the nonce and verify the user as permission to save.
        if ( ! ( $this->has_valid_nonce() && current_user_can( 'manage_options' ) ) ) {
            // TODO: Display an error message.
        }
		$postsValue = [];
		foreach($_POST as $key => $postValue){
			if(strpos($key, "iloveimg_field_") === 0){
				$postsValue[$key] = $postValue;
			}
		}
		update_option('iloveimg_options_compress', serialize($postsValue));
		
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