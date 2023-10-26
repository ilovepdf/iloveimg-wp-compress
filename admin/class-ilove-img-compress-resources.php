<?php
/**
 * Utility class for managing resources and functionality related to image compression.
 *
 * This class serves as a utility for managing various resources and functionality
 * associated with image compression within the iLoveIMG plugin. It includes methods
 * for handling image compression resources, status, and related operations.
 *
 * @since 1.0.0
 */
class Ilove_Img_Compress_Resources {

    /**
     * Get an array of image size options for image type selection.
     *
     * This method retrieves an array of image size options to be used for image type selection
     * in the settings. It includes options for the original image as well as available image sizes.
     *
     * @return array An array of image size options.
     *
     * @since 1.0.0
     */
    public static function get_type_images() {
        global $_wp_additional_image_sizes;

        $sizes   = array();
        $sizes[] = array(
            'field_id' => 'full',
            'type'     => 'checkbox',
            'label'    => 'Original image',
            'default'  => true,
        );
        foreach ( get_intermediate_image_sizes() as $_size ) {
            if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {
                $width  = get_option( "{$_size}_size_w" );
                $height = get_option( "{$_size}_size_h" );
            } elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
                $width  = $_wp_additional_image_sizes[ $_size ]['width'];
                $height = $_wp_additional_image_sizes[ $_size ]['height'];
            }

            $sizes[] = array(
                'field_id' => $_size,
                'type'     => 'checkbox',
                'label'    => $_size . ' (' . ( ( 0 === (int) $width ) ? '?' : $width ) . 'x' . ( ( 0 === (int) $height ) ? '?' : $height ) . ')',
                'default'  => true,
            );

        }
        return $sizes;
    }

    /**
     * Calculate and return the savings achieved through image compression.
     *
     * This method calculates the percentage of savings achieved through image compression
     * by comparing the initial image size with the compressed image size.
     *
     * @param array $images An array of image data including initial and compressed sizes.
     * @return float The percentage of savings achieved.
     *
     * @since 1.0.0
     */
    public static function get_saving( $images ) {
        $initial    = 0;
        $compressed = 0;
        foreach ( $images as $image ) {
            if ( ! is_null( $image['compressed'] ) ) {
                $initial    += $image['initial'];
                $compressed += $image['compressed'];
            }
        }
        return round( 100 - ( ( $compressed * 100 ) / $initial ) );
    }

    /**
     * Get the count of enabled image sizes selected in the plugin settings.
     *
     * This method retrieves and returns the count of enabled image sizes selected in the plugin settings.
     *
     * @return int The count of enabled image sizes.
     *
     * @since 1.0.0
     */
    public static function get_sizes_enabled() {
        $options_compress = json_decode( get_option( 'iloveimg_options_compress' ), true );
        $image_sizes      = isset( $options_compress['iloveimg_field_sizes'] ) ? $options_compress['iloveimg_field_sizes'] : array();
        $count            = 0;
        foreach ( $image_sizes as $image ) {
            if ( $image ) {
                ++$count;
            }
        }
        return $count;
    }

    /**
     * Check if automatic image compression is enabled in the plugin settings.
     *
     * This method checks if automatic image compression is enabled in the plugin settings
     * and returns 1 if it is enabled or 0 if it is not.
     *
     * @return int 1 if auto compression is enabled, 0 if not.
     *
     * @since 1.0.0
     */
    public static function is_auto_compress() {
        $options_compress = json_decode( get_option( 'iloveimg_options_compress' ), true );
        return ( isset( $options_compress['iloveimg_field_autocompress'] ) ) ? 1 : 0;
    }

    /**
     * Check if image compression is activated in the plugin settings.
     *
     * This method checks if image compression is activated in the plugin settings
     * and returns 1 if it is activated or 0 if it is not.
     *
     * @return int 1 if compression is activated, 0 if not.
     *
     * @since 1.0.0
     */
    public static function is_activated() {
        $options_compress = json_decode( get_option( 'iloveimg_options_compress' ), true );
        return ( isset( $options_compress['iloveimg_field_compress_activated'] ) ) ? 1 : 0;
    }

    /**
     * Get the count of image sizes that have been successfully compressed for a specific attachment.
     *
     * This method retrieves and returns the count of image sizes that have been successfully compressed
     * for a specific attachment identified by its column ID.
     *
     * @param int $column_id The ID of the attachment in the WordPress media library.
     * @return int The count of compressed image sizes for the attachment.
     *
     * @since 1.0.0
     */
    public static function get_sizes_compressed( $column_id ) {
        $images = get_post_meta( $column_id, 'iloveimg_compress', true );
        $count  = 0;
        if ( ! $images ) {
            return $count;
        }
        foreach ( $images as $image ) {
            if ( ! is_null( $image['compressed'] ) ) {
                ++$count;
            }
        }
        return $count;
    }

    /**
     * Check if the user is logged into the iLoveIMG service via the plugin.
     *
     * This method checks if the user is logged into the iLoveIMG service via the plugin
     * and returns true if logged in, or false if not logged in.
     *
     * @return bool True if the user is logged in, false if not logged in.
     *
     * @since 1.0.0
     */
    public static function is_loggued() {
        if ( get_option( 'iloveimg_account' ) ) {
            $account = json_decode( get_option( 'iloveimg_account' ), true );
            if ( array_key_exists( 'error', $account ) ) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Render the details of image compression for a specific attachment.
     *
     * This method generates and displays the details of image compression for a specific attachment
     * identified by its image ID. It includes information on each size of the image and its compression
     * status.
     *
     * @param int $image_id The ID of the attachment in the WordPress media library.
     *
     * @since 1.0.0
     */
    public static function render_compress_details( $image_id ) {
        $_sizes            = get_post_meta( $image_id, 'iloveimg_compress', true );
        $images_compressed = self::get_sizes_compressed( $image_id );

        ?>
        <div id="iloveimg_detaills_compress_<?php echo (int) $image_id; ?>" style="display:none;">
            <table class="table__details__sizes">
                <tr>
                    <th>Name</th><th>Initial</th><th>Compressed</th>
                    <?php
                    $total_size       = 0;
                    $total_compressed = 0;
                    foreach ( $_sizes as $key => $size ) {
                        ?>
                        <tr>
                            <td><?php echo esc_attr( $key ); ?></td>
                            <td><?php echo (float) round( $size['initial'] / 1024 ); ?> KB</td>
                            <td>
                            <?php
							if ( $size['compressed'] ) {
								$percent          = (int) ( 100 - round( ( $size['compressed'] * 100 ) / $size['initial'] ) );
								$total_size       = $total_size + (int) $size['initial'];
								$total_compressed = $total_compressed + (int) $size['compressed'];
								if ( $percent > 0 ) {
                                    echo (float) round( $size['compressed'] / 1024 ) . ' KB';
                                    ?>
                                    <small class="iloveimg__badge__percent">-<?php echo (int) $percent; ?>%</small>
                                    <?php
								} else {
									echo 'Not compressed';
								}
							} else {
								echo 'Not compressed';
							}
							?>
                                </td>
                            </tr>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </div>
        <!-- <p>Now <?php echo (float) self::get_saving( $_sizes ); ?>% smaller!</p> -->
        <p><a href="#TB_inline?&width=450&height=340&inlineId=iloveimg_detaills_compress_<?php echo (int) $image_id; ?>" class="thickbox iloveimg_sizes_compressed" title="<?php echo esc_html( get_the_title( $image_id ) ); ?>"><?php echo (int) $images_compressed; ?> sizes compressed</a><small class="iloveimg__badge__percent">-<?php echo (float) ( 100 - round( ( $total_compressed * 100 ) / $total_size ) ); ?>%</small></p>
        <?php
    }

    /**
     * Get and display the status of image compression for a specific attachment column.
     *
     * This method retrieves and displays the status of image compression for a specific attachment
     * column identified by its column ID. It checks the image's MIME type and the status of compression
     * and provides relevant actions or information to the user.
     *
     * @param int $column_id The ID of the attachment column in the WordPress media library.
     *
     * @since 1.0.0
     */
    public static function get_status_of_column( $column_id ) {

        $post = get_post( $column_id );

        $img_nonce = Ilove_Img_Compress_Plugin::get_img_nonce();

        if ( strpos( $post->post_mime_type, 'image/jpg' ) !== false || strpos( $post->post_mime_type, 'image/jpeg' ) !== false || strpos( $post->post_mime_type, 'image/png' ) !== false || strpos( $post->post_mime_type, 'image/gif' ) !== false ) :
            $_sizes            = get_post_meta( $column_id, 'iloveimg_compress', true );
            $status_compress   = (int) get_post_meta( $column_id, 'iloveimg_status_compress', true );
            $images_compressed = self::get_sizes_compressed( $column_id );

            if ( $_sizes && $images_compressed ) :
                self::render_compress_details( $column_id );
            else :
                ?>
                                    
                    <?php if ( self::is_loggued() ) : ?>
                        <!-- <p><?php echo (int) self::get_sizes_enabled(); ?> sizes to be compressed</p> -->
                        <?php if ( self::get_sizes_enabled() ) : ?>
                            <button type="button" class="iloveimg-compress button button-small button-primary" data-imgnonce="<?php echo sanitize_key( wp_unslash( $img_nonce ) ); ?>" data-id="<?php echo (int) $column_id; ?>" <?php echo ( 1 === $status_compress || 3 === $status_compress ) ? 'disabled="disabled"' : ''; ?>>Compress</button>
                            <img class="iloveimg-spinner" src="<?php echo esc_url( plugins_url( '/assets/images/spinner.gif', __DIR__ ) ); ?>" width="20" height="20" style="<?php echo ( 1 === $status_compress || 3 === $status_compress ) ? '' : 'display: none;'; ?>; margin-top: 7px" />
                            <?php if ( 3 === $status_compress ) : ?>
                                <!-- <p>In queue...</p> -->
                            <?php endif; ?>
                        <?php else : ?>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ); ?>" class="iloveimg_link">Go to settings</button>
							<?php
                        endif;
                    else :
						?>
                        <p>You need to be registered</p>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ); ?>" class="iloveimg_link">Go to settings</button>
						<?php
                    endif;
                    if ( 1 === $status_compress || 3 === $status_compress ) :
						?>
                        <div class="iloveimg_compressing" style="display: none;" data-id="<?php echo (int) $column_id; ?>" data-imgnonce="<?php echo sanitize_key( wp_unslash( $img_nonce ) ); ?>"></div>
						<?php
                    endif;
            endif;
        endif;
    }

    /**
     * Get the count of files with compressed images in the media library.
     *
     * This method retrieves and returns the count of files in the WordPress media library that have
     * compressed images stored as metadata.
     *
     * @return int The count of files with compressed images.
     *
     * @since 1.0.0
     */
    public static function get_files_compressed() {
        global $wpdb;

        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_compress'" ); // phpcs:ignore
    }

    /**
     * Get the total count of images in the media library.
     *
     * This method retrieves and returns the total count of images in the WordPress media library.
     *
     * @return int The total count of images in the media library.
     *
     * @since 1.0.0
     */
    public static function get_total_images() {
        $query_img_args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => array(
				'jpg|jpeg|jpe' => 'image/jpeg',
				'gif'          => 'image/gif',
				'png'          => 'image/png',
			),
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
        );
        $query_img      = new WP_Query( $query_img_args );
        return (int) $query_img->post_count;
    }

    /**
     * Get the total file sizes and total compressed file sizes.
     *
     * This method retrieves and calculates the total file sizes and total compressed file sizes for
     * images in the media library. It returns an array with two values: total file size and total
     * compressed file size.
     *
     * @return array An array containing two integers: total file size and total compressed file size.
     *
     * @since 1.0.0
     */
    public static function get_files_sizes() {
        global $wpdb;
        $rows             = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_compress'" ); // phpcs:ignore
        $total            = 0;
        $total_compressed = 0;
        foreach ( $rows as $row ) {
            $stadistics = json_decode( $row->meta_value, true );
            foreach ( $stadistics as $key => $value ) {
                $total            = $total + (int) $value['initial'];
                $total_compressed = $total_compressed + (int) $value['compressed'];
            }
        }
        return array( $total, $total_compressed );
    }
}