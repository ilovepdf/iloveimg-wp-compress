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
     * @param int $imagesID The ID of the image to compress.
     * @return array|false An array of compressed image data or false if compression fails.
     *
     * @since 1.0.0
     * @access public
     */
    public function compress( $imagesID ) {
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

            $filesProcessing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_compress' AND meta_value = 1" );

            $imageWatermarkProcessing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_watermark' AND meta_value = 1 AND post_id =  " . $imagesID );

            if ( $filesProcessing < ILOVE_IMG_COMPRESS_NUM_MAX_FILES and $imageWatermarkProcessing == 0 ) {
                update_post_meta( $imagesID, 'iloveimg_status_compress', 1 ); // status compressing

                $_sizes = get_intermediate_image_sizes();

                array_unshift( $_sizes, 'full' );
                $_aOptions = unserialize( get_option( 'iloveimg_options_compress' ) );

                foreach ( $_sizes as $_size ) {
                    $image    = wp_get_attachment_image_src( $imagesID, $_size );
                    $pathFile = str_replace( site_url(), '', $image[0] );
                    if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
                        $pathFile = sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) . $pathFile;
                    }
                    $images[ $_size ] = array(
						'initial'    => filesize( $pathFile ),
						'compressed' => null,
					);
                    if ( in_array( $_size, $_aOptions['iloveimg_field_sizes'] ) ) {
                        if ( $_size == 'full' ) {
                            if ( $_aOptions['iloveimg_field_resize_full'] == 'on' ) {
                                $metadata = wp_get_attachment_metadata( $imagesID );
                                $editor   = wp_get_image_editor( $pathFile );
                                if ( ! is_wp_error( $editor ) ) {
                                    $editor->resize( $_aOptions['iloveimg_field_size_full_width'], $_aOptions['iloveimg_field_size_full_height'], false );
                                    $editor->save( $pathFile );
                                    $resize             = $editor->get_size();
                                    $metadata['width']  = $resize['width'];
                                    $metadata['height'] = $resize['height'];

                                } else {
                                    $myTask = new ResizeImageTask( $this->proyect_public, $this->secret_key );
                                    $file   = $myTask->addFile( $pathFile );
                                    $myTask->setPixelsWidth( $_aOptions['iloveimg_field_size_full_width'] );
                                    $myTask->setPixelsHeight( $_aOptions['iloveimg_field_size_full_height'] );
                                    $myTask->execute();
                                    $myTask->download( dirname( $pathFile ) );
                                    list($width, $height) = getimagesize( $pathFile );
                                    $metadata['width']    = $width;
                                    $metadata['height']   = $height;
                                }
                                wp_update_attachment_metadata( $imagesID, $metadata );
                            }
                        }
                        $myTask = new CompressImageTask( $this->proyect_public, $this->secret_key );
                        $file   = $myTask->addFile( $pathFile );
                        $myTask->execute();
                        $myTask->download( dirname( $pathFile ) );
                        if ( $images[ $_size ]['compressed'] < $images[ $_size ]['initial'] ) {
                            $images[ $_size ]['compressed'] = filesize( $pathFile );
                        }
					}
                }
                update_post_meta( $imagesID, 'iloveimg_compress', $images );
                update_post_meta( $imagesID, 'iloveimg_status_compress', 2 ); // status compressed
                return $images;

            } else {
                update_post_meta( $imagesID, 'iloveimg_status_compress', 3 ); // status queue
                sleep( 2 );
                return $this->compress( $imagesID );
            }

            // print_r($imagesID);
        } catch ( Exception $e ) {
            update_post_meta( $imagesID, 'iloveimg_status_compress', 0 );
            // echo $e->getCode();
            return false;
        }
        return false;
    }
}
