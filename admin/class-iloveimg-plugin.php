<?php

class iLoveIMG_Compress_Plugin {
    const VERSION = '1.0.5';
	const NAME = 'iLoveIMG_Compress_plugin';
    public function __construct() {
        add_action( 'admin_init', array( $this, "admin_init" ));
    }

    public function admin_init() {
        add_action( 'admin_enqueue_scripts', array($this, "enqueue_scripts"));
        add_filter( 'manage_media_columns', array( $this, "column_id" ) );
        add_filter( 'manage_media_custom_column', array( $this, "column_id_row" ), 10, 2 );
        add_action( 'wp_ajax_iLoveIMG_Compress_library', array($this, "iLoveIMG_Compress_library") );
        add_action( 'wp_ajax_iLoveIMG_Compress_library_is_compressed', array($this, "iLoveIMG_Compress_library_is_compressed") );
        add_filter( 'wp_generate_attachment_metadata', array($this, 'process_attachment' ), 10, 2);
        add_action( 'attachment_submitbox_misc_actions', array($this, 'show_media_info'));
        
        if(!class_exists('iLoveIMG_Library_init')){
            require_once("class-iloveimg-library-init.php");
            new iLoveIMG_Library_init();
        }
        
        add_action( 'admin_notices', array($this, 'show_notices'));
        add_action( 'iloveimg_watermarked_completed', array($this, "iloveimg_watermarked_completed"));
        add_thickbox();
    }

    public function enqueue_scripts(){
        wp_enqueue_script( self::NAME . '_admin',
        plugins_url( '/assets/js/main.js', dirname(__FILE__) ),
			array(), self::VERSION, true
        );
        wp_enqueue_style( self::NAME . '_admin',
        plugins_url( '/assets/css/app.css', dirname(__FILE__) ),
			array(), self::VERSION
		);
    }
    
    public function iLoveIMG_Compress_library(){
        if(isset($_POST['id'])){
            $ilove = new iLoveIMG_Compress_Process();
            $attachment_id = intval($_POST['id']);
            $images = $ilove->compress($attachment_id);
            if($images !== false){
                iLoveIMG_Compress_Resources::render_compress_details($attachment_id);
            }else{
                ?>
                <p>You need more files</p>
                <?php
            }
        }
        wp_die();
    }

    public function iloveimg_watermarked_completed($attachment_id){
        if((int)iLoveIMG_Compress_Resources::isAutoCompress() === 1){
            $this->async_compress($attachment_id);
        }
    }
    
    public function iLoveIMG_Compress_library_is_compressed(){
        if(isset($_POST['id'])){
            $attachment_id = intval($_POST['id']);
            $status_compress = get_post_meta($attachment_id, 'iloveimg_status_compress', true);

            $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($attachment_id);
            if(((int)$status_compress === 1 || (int)$status_compress === 3)){
                http_response_code(500);
            }else if((int)$status_compress === 2){
                iLoveIMG_Compress_Resources::render_compress_details($attachment_id);
            }else if((int)$status_compress === 0 && !$status_compress){
                echo "Try again or buy more files";
            }
        }
        wp_die();
    }

    public function column_id($columns){
        if((int)iLoveIMG_Compress_Resources::isActivated() === 0){
            return $columns;
        }
        $columns['iloveimg_status_compress'] = __('Status Compress');
        return $columns;
    }

    public function column_id_row($columnName, $columnID){
        if($columnName == 'iloveimg_status_compress'){
            iLoveIMG_Compress_Resources::getStatusOfColumn($columnID);
        }
    }

    public function process_attachment($metadata, $attachment_id){
        update_post_meta($attachment_id, 'iloveimg_status_compress', 0); //status no compressed
        if((int)iLoveIMG_Compress_Resources::isAutoCompress() === 1 && iLoveIMG_Compress_Resources::isLoggued() && (int)iLoveIMG_Compress_Resources::isActivated() === 1){
            wp_update_attachment_metadata($attachment_id, $metadata);
            $this->async_compress($attachment_id);
        }
        
        return $metadata;
    }

    public function async_compress($attachment_id){
        $args = array(
            'method' => 'POST',
            'timeout' => 0.01,
            'blocking' => false,
            'body' => array( 'action' => 'iLoveIMG_Compress_library', 'id' => $attachment_id ),
            'cookies'   => isset( $_COOKIE ) && is_array( $_COOKIE ) ? $_COOKIE : array(),
            'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
        );
        wp_remote_post( admin_url( 'admin-ajax.php' ), $args );
    }

    public function show_notices(){
        if(!iLoveIMG_Compress_Resources::isLoggued()){
        ?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>iLoveIMG</strong> - Please you need to be logged or registered. <a href="<?php echo admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ?>">Go to settings</a></p>
            </div>
            <?php
        }

        if(get_option('iloveimg_account_error')){
                $iloveimg_account_error = unserialize(get_option('iloveimg_account_error'));
            if($iloveimg_account_error['action'] == 'login'):
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>Your email or password is wrong.</p>
                </div>
            <?php endif;  ?>
            <?php if($iloveimg_account_error['action'] == 'register'): ?>
                <div class="notice notice-error is-dismissible">
                    <p>This email address has already been taken.</p>
                </div>
            <?php endif;  ?>
            <?php if($iloveimg_account_error['action'] == 'register_limit'): ?>
                <div class="notice notice-error is-dismissible">
                    <p>You have reached limit of different users to use this Wordpress plugin. Please relogin with one of your existing users.</p>
                </div>
            <?php endif;  ?>
            <?php
            
        }
        //do query
        if(get_option('iloveimg_account')){
            $account = json_decode(get_option('iloveimg_account'), true);
            if(!array_key_exists('error', $account)){
                $token = $account['token'];
                $response = wp_remote_get(iLoveIMG_Compress_USER_URL.'/'.$account['id'], 
                    array(
                        'headers' => array('Authorization' => 'Bearer '.$token)
                    )
                );

                if (isset($response['response']['code']) && $response['response']['code'] == 200) {
                    $account = json_decode($response["body"], true);
                    if($account['files_used'] >=  $account['free_files_limit'] and $account['package_files_used'] >=  $account['package_files_limit'] and @$account['subscription_files_used'] >=  $account['subscription_files_limit']){
                        ?>
                        <div class="notice notice-warning is-dismissible">
                            <p><strong>iLoveIMG</strong> - Please you need more files. <a href="https://developer.iloveimg.com/pricing" target="_blank">Buy more files</a></p>
                        </div>
                        <?php
                    }
                }
            }
        }
    }

    public function show_media_info(){
        global $post;
        echo '<div class="misc-pub-section iloveimg-compress-images">';
        echo '<h4>';
        esc_html_e( 'iLoveIMG', 'iloveimg' );
        echo '</h4>';
        echo '<div class="iloveimg-container">';
        echo '<table><tr><td>';
        $status_compress = get_post_meta($post->ID, 'iloveimg_status_compress', true);

        $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($post->ID);
        
        if((int)$status_compress === 2){
            iLoveIMG_Compress_Resources::render_compress_details($post->ID);
        }else{
            iLoveIMG_Compress_Resources::getStatusOfColumn($post->ID);
        }
        echo '</td></tr></table>';
        echo '</div>';
        echo '</div>';
    }

}