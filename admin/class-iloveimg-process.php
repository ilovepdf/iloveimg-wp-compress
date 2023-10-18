<?php
use Iloveimg\CompressImageTask;
use Iloveimg\ResizeImageTask;

/**
 * Class for handling image compression and processing using the iLoveIMG API.
 *
 * This class is responsible for image compression and processing tasks
 * using the iLoveIMG API. It provides methods for compressing images
 * and performing various image-related operations.
 *
 * @since 1.0.0
 */
class iLoveIMG_Compress_Process {

    /**
     * Public project key for iLoveIMG API.
     *
     * @var string $proyect_public The public project key for the iLoveIMG API.
     *
     * @since 1.0.0
     * @access public
     */
    public $proyect_public = '';

    /**
     * Secret key for iLoveIMG API.
     *
     * @var string $secret_key The secret key for the iLoveIMG API.
     *
     * @since 1.0.0
     * @access public
     */
    public $secret_key = '';

    /**
     * Compress and optimize images using the iLoveIMG service.
     *
     * This method handles the compression and optimization of images using the iLoveIMG service. It retrieves the project's API keys, checks if the images are ready for compression, and processes them accordingly. It also updates the status of the images during the compression process.
     *
     * @param int $images_id The ID of the image to compress.
     * @return array|false An array of compressed image data or false if compression fails.
     *
     * @since 1.0.0
     * @access public
     */
    public function compress( $images_id ) {
        global $_wp_additional_image_sizes, $wpdb;

        $images = array();
        try {

            if ( get_option( 'iloveimg_proyect' ) ) {
                $proyect              = explode( '#', get_option( 'iloveimg_proyect' ) );
                $this->proyect_public = $proyect[0];
                $this->secret_key     = $proyect[1];
            } elseif ( get_option( 'iloveimg_account' ) ) {
                $account              = json_decode( get_option( 'iloveimg_account' ), true );
                $this->proyect_public = $account['projects'][0]['public_key'];
                $this->secret_key     = $account['projects'][0]['secret_key'];
            }

            $files_processing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_compress' AND meta_value = 1" );

            $image_watermark_processing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_watermark' AND meta_value = 1 AND post_id =  " . $images_id );

            if ( $files_processing < ILOVE_IMG_COMPRESS_NUM_MAX_FILES and $image_watermark_processing == 0 ) {
                update_post_meta( $images_id, 'iloveimg_status_compress', 1 ); // status compressing

                $_sizes = get_intermediate_image_sizes();

                array_unshift( $_sizes, 'full' );
                $options_compress = unserialize( get_option( 'iloveimg_options_compress' ) );

                foreach ( $_sizes as $_size ) {
                    $image    = wp_get_attachment_image_src( $images_id, $_size );
                    $path_file = str_replace( site_url(), '', $image[0] );
                    if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
                        $path_file = sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) . $path_file;
                    }
                    $images[ $_size ] = array(
						'initial'    => filesize( $path_file ),
						'compressed' => null,
					);
                    if ( in_array( $_size, $options_compress['iloveimg_field_sizes'] ) ) {
                        if ( $_size == 'full' ) {
                            if ( $options_compress['iloveimg_field_resize_full'] == 'on' ) {
                                $metadata = wp_get_attachment_metadata( $images_id );
                                $editor   = wp_get_image_editor( $path_file );
                                if ( ! is_wp_error( $editor ) ) {
                                    $editor->resize( $options_compress['iloveimg_field_size_full_width'], $options_compress['iloveimg_field_size_full_height'], false );
                                    $editor->save( $path_file );
                                    $resize             = $editor->get_size();
                                    $metadata['width']  = $resize['width'];
                                    $metadata['height'] = $resize['height'];

                                } else {
                                    $my_task = new ResizeImageTask( $this->proyect_public, $this->secret_key );
                                    $file   = $my_task->addFile( $path_file );
                                    $my_task->setPixelsWidth( $options_compress['iloveimg_field_size_full_width'] );
                                    $my_task->setPixelsHeight( $options_compress['iloveimg_field_size_full_height'] );
                                    $my_task->execute();
                                    $my_task->download( dirname( $path_file ) );
                                    list($width, $height) = getimagesize( $path_file );
                                    $metadata['width']    = $width;
                                    $metadata['height']   = $height;
                                }
                                wp_update_attachment_metadata( $images_id, $metadata );
                            }
                        }
                        $my_task = new CompressImageTask( $this->proyect_public, $this->secret_key );
                        $file   = $my_task->addFile( $path_file );
                        $my_task->execute();
                        $my_task->download( dirname( $path_file ) );
                        if ( $images[ $_size ]['compressed'] < $images[ $_size ]['initial'] ) {
                            $images[ $_size ]['compressed'] = filesize( $path_file );
                        }
					}
                }
                update_post_meta( $images_id, 'iloveimg_compress', $images );
                update_post_meta( $images_id, 'iloveimg_status_compress', 2 ); // status compressed
                return $images;

            } else {
                update_post_meta( $images_id, 'iloveimg_status_compress', 3 ); // status queue
                sleep( 2 );
                return $this->compress( $images_id );
            }

            // print_r($images_id);
        } catch ( Exception $e ) {
            update_post_meta( $images_id, 'iloveimg_status_compress', 0 );
            // echo $e->getCode();
            return false;
        }
        return false;
    }
}
