<?php

class iLoveIMG_Compress_Plugin {
    const VERSION = '1.0.0';
	const NAME = 'iloveimg_compress_plugin';
    public function __construct() {
        add_action( 'admin_init', array( $this, "admin_init" ));
    }

    public function admin_init() {
        add_action( 'admin_enqueue_scripts', array($this, "enqueue_scripts"));
        add_filter( 'manage_media_columns', array( $this, "column_id" ) );
        add_filter( 'manage_media_custom_column', array( $this, "column_id_row" ), 10, 2 );
        add_action( 'wp_ajax_iloveimg_compress_library', array($this, "iloveimg_compress_library") );
        add_action( 'wp_ajax_iloveimg_compress_library_is_compressed', array($this, "iloveimg_compress_library_is_compressed") );
        add_filter( 'wp_generate_attachment_metadata', array($this,'process_attachment' ), 10, 2);
        add_action( 'admin_action_iloveimg_bulk_action', array($this, "media_library_bulk_action"));

        require_once( dirname(dirname(__FILE__)) . '/iloveimg-php/init.php');
        
    }

    public function enqueue_scripts(){
        wp_enqueue_script( self::NAME . '_admin',
        plugins_url( '/assets/js/main.js', dirname(__FILE__) ),
			array(), self::VERSION, true
        );
        wp_enqueue_style( self::NAME . '_admin',
        plugins_url( '/assets/css/bulk_optimized.css', dirname(__FILE__) ),
			array(), self::VERSION
		);
    }
    
    public function iloveimg_compress_library(){
        $ilove = new iLoveIMG_Compress_Process();
        $images = $ilove->compress($_POST['id']);
        
        $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($_POST['id']);
        ?>
        <p>Now <?php echo iLoveIMG_Compress_Resources::getSaving($images) ?>% smaller!</p>
        <p><a href="#"><?php echo $imagesCompressed ?> sizes compressed</a></p>
        <?php
        wp_die();
    }
    
    public function iloveimg_compress_library_is_compressed(){
        $status_compress = get_post_meta($_POST['id'], 'iloveimg_status_compress', true);

        $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($_POST['id']);
        if(((int)$status_compress === 1)){
            echo "processing";
        }else if((int)$status_compress === 2){
            $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($_POST['id']);
            $_sizes = get_post_meta($_POST['id'], 'iloveimg_compress', true);
            ?>
            <p>Now <?php echo iLoveIMG_Compress_Resources::getSaving($_sizes) ?>% smaller!</p>
            <p><a href="#"><?php echo $imagesCompressed ?> sizes compressed</a></p>
        <?php
        }
        wp_die();
    }

    public function column_id($columns){
        $columns['iloveimg_compression'] = __('iLoveIMG');
        return $columns;
    }

    public function column_id_row($columnName, $columnID){
        if($columnName == 'iloveimg_compression'){
            iLoveIMG_Compress_Resources::getStatusOfColumn($columnID);
        }
    }

    public function process_attachment($metadata, $attachment_id){
        update_post_meta($attachment_id, 'iloveimg_status_compress', 0); //status no compressed
        if((int)iLoveIMG_Compress_Resources::isAutoCompress() === 1){
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
            'body' => array( 'action' => 'iloveimg_compress_library', 'id' => $attachment_id ),
            'cookies'   => isset( $_COOKIE ) && is_array( $_COOKIE ) ? $_COOKIE : array(),
            'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
        );
        if ( getenv( 'WORDPRESS_HOST' ) !== false ) {
			wp_remote_post( getenv( 'WORDPRESS_HOST' ) . '/wp-admin/admin-ajax.php', $args );
		} else {
			wp_remote_post( admin_url( 'admin-ajax.php' ), $args );
		}
    }

    public function media_library_bulk_action(){
        foreach($_REQUEST['media'] as $attachment_id){
            $post = get_post($attachment_id);
            if(strpos($post->post_mime_type, "image/") !== false){
                $status_compress = get_post_meta($attachment_id, 'iloveimg_status_compress', true);
                if((int)$status_compress === 0){
                    $this->async_compress($attachment_id);
                }
            }
        }
    }
}