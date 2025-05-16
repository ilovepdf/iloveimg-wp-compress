<?php
namespace Ilove_Img_Compress;

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
class Ilove_Img_Compress_Process {

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
        global $wpdb;

        $images         = array();
        $file_mime_type = get_post_mime_type( $images_id );

        try {

            if ( ! in_array( $file_mime_type, Ilove_Img_Compress_Plugin::$accepted_file_format, true ) ) {
                return array(
                    'error'     => true,
                    'error_msg' => sprintf(
                        /* translators: %d: ID of File */
                        __( 'The file %d is not an image.', 'iloveimg' ),
                        $images_id
                    ),
                );
            }

            if ( get_option( 'iloveimg_proyect' ) ) {
                $proyect              = explode( '#', get_option( 'iloveimg_proyect' ) );
                $this->proyect_public = $proyect[0];
                $this->secret_key     = $proyect[1];
            } elseif ( get_option( 'iloveimg_account' ) ) {
                $account              = json_decode( get_option( 'iloveimg_account' ), true );
                $this->proyect_public = $account['projects'][0]['public_key'];
                $this->secret_key     = $account['projects'][0]['secret_key'];
            }

            $files_processing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_compress' AND meta_value = 1" ); // phpcs:ignore

            $image_watermark_processing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_watermark' AND meta_value = 1 AND post_id =  " . $images_id ); // phpcs:ignore

            if ( $files_processing < ILOVE_IMG_COMPRESS_NUM_MAX_FILES && 0 === (int) $image_watermark_processing ) {
                update_post_meta( $images_id, 'iloveimg_status_compress', 1 ); // status compressing

                $_sizes = get_intermediate_image_sizes();

                array_unshift( $_sizes, 'full' );

                $options_compress  = json_decode( get_option( 'iloveimg_options_compress' ), true );
                $options_watermark = json_decode( get_option( 'iloveimg_options_watermark' ), true );
                $images_restore    = get_option( 'iloveimg_images_to_restore' ) ? json_decode( get_option( 'iloveimg_images_to_restore' ), true ) : array();

                if ( ( isset( $options_compress['iloveimg_field_backup'] ) || isset( $options_watermark['iloveimg_field_backup'] ) ) && ! in_array( $images_id, $images_restore, true ) ) {

                    $attached_file = get_attached_file( $images_id );

                    Ilove_Img_Compress_Resources::rcopy( $attached_file, ILOVE_IMG_COMPRESS_BACKUP_FOLDER );

					$images_restore[] = $images_id;

                    $images_restore = array_unique( $images_restore );

                    Ilove_Img_Compress_Resources::update_option( 'iloveimg_images_to_restore', wp_json_encode( $images_restore, JSON_FORCE_OBJECT ) );
                }

                foreach ( $_sizes as $_size ) {
                    $path_file       = '';
                    $image           = wp_get_attachment_image_src( $images_id, $_size );
                    $parse_image_url = wp_parse_url( $image[0] );

                    if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
                        $path_file = sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) . $parse_image_url['path'];
                    }

                    $images[ $_size ] = array(
						'initial'    => filesize( $path_file ),
						'compressed' => null,
					);

                    if ( in_array( $_size, $options_compress['iloveimg_field_sizes'], true ) ) {
                        if ( 'full' === $_size ) {
                            if ( isset( $options_compress['iloveimg_field_resize_full'] ) && 'on' === $options_compress['iloveimg_field_resize_full'] ) {
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
                                    $file    = $my_task->addFile( $path_file );
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
                        $my_task          = new CompressImageTask( $this->proyect_public, $this->secret_key );
                        $file             = $my_task->addFile( $path_file );
                        $execute_compress = $my_task->execute();

                        if ( $execute_compress ) { /* @phpstan-ignore-line */
                            $my_task->download( dirname( $path_file ) );

                            if ( $images[ $_size ]['compressed'] < $images[ $_size ]['initial'] ) {
                                $images[ $_size ]['compressed'] = filesize( $path_file );
                            }
                        } else {
                            return false;
                        }
					}
                }
                update_post_meta( $images_id, 'iloveimg_compress', $images );
                update_post_meta( $images_id, 'iloveimg_status_compress', 2 ); // status compressed

                return array(
                    'error'  => false,
                    'images' => $images,
                );

            } else {
                update_post_meta( $images_id, 'iloveimg_status_compress', 3 ); // status queue

                return array(
                    'error'     => true,
                    'error_msg' => __( 'There was a problem processing your image.', 'iloveimg' ),
                );
            }
		} catch ( \Exception $e ) {
            update_post_meta( $images_id, 'iloveimg_status_compress', 0 );
            error_log('Exception on Compress Method: ' . print_r($e, true)); // phpcs:ignore

            $error_msg = __( 'There was a problem processing your image.', 'iloveimg' );

            if ( 401 === $e->getCode() ) {
                $error_msg = __( 'Check your credentials in the plugin settings page. If you recently deleted a project in your iloveapi account, try switching to another project to correctly save your API Keys.', 'iloveimg' );
            }

            return array(
                'error'     => true,
                'error_msg' => $error_msg,
            );
        }
    }
}
