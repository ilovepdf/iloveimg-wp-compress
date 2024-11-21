<?php
namespace Ilove_Img_Compress;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://iloveimg.com/
 * @since      1.0.0
 *
 * @package    iloveimgcompress
 * @subpackage iloveimgcompress/admin
 */
class Ilove_Img_Compress_Plugin {

    /**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    VERSION    The current version of the plugin.
	 */
    const VERSION = '2.2.5';

    /**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    NAME    The string used to uniquely identify this plugin.
	 */
	const NAME = 'Ilove_Img_Compress_plugin';

    /**
	 * The unique nonce identifier.
	 *
	 * @since    1.0.6
	 * @access   public
	 * @var      string    $img_nonce    The string used to uniquely nonce identify.
	 */
	protected static $img_nonce;

    /**
	 * This constructor defines the core functionality of the plugin.
     *
     * In this method, we set the plugin's name and version for reference throughout the codebase. We also load any necessary dependencies, define the plugin's locale for translation purposes, and set up hooks for the admin area.
     *
     * This constructor is executed when the plugin is initialized.
	 *
	 * @since    1.0.0
	 */
    public function __construct() {
        add_action( 'admin_init', array( $this, 'admin_init' ) );
    }

    /**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
     *
     * This method is responsible for registering various hooks and filters specific to the admin area functionality of the plugin. These hooks and filters handle tasks such as enqueueing scripts, managing media columns, processing attachment metadata, and displaying notices.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
    public function admin_init() {
        // create nonce
        self::$img_nonce = wp_create_nonce();

        // Enqueue scripts for the admin area.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Manage media columns.
        add_filter( 'manage_media_columns', array( $this, 'column_id' ) );
        add_filter( 'manage_media_custom_column', array( $this, 'column_id_row' ), 10, 2 );

        // Handle AJAX requests for the library.
        add_action( 'wp_ajax_ilove_img_compress_library', array( $this, 'ilove_img_compress_library' ) );
        add_action( 'wp_ajax_ilove_img_compress_library_is_compressed', array( $this, 'ilove_img_compress_library_is_compressed' ) );
        add_action( 'wp_ajax_ilove_img_compress_restore_all', array( $this, 'ilove_img_restore_all' ) );
        add_action( 'wp_ajax_ilove_img_compress_clear_backup', array( $this, 'ilove_img_compress_clear_backup' ) );
        add_action( 'wp_ajax_ilove_img_compress_restore', array( $this, 'ilove_img_restore' ) );

        // Process attachment metadata.
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'process_attachment' ), 10, 2 );

        // Display media information in the attachment submit box.
        add_action( 'attachment_submitbox_misc_actions', array( $this, 'show_media_info' ) );

        // Display admin notices.
        add_action( 'admin_notices', array( $this, 'show_notices' ) );

        // Handle a specific event when watermarked images are completed.
        add_action( 'iloveimg_watermarked_completed', array( $this, 'iloveimg_watermarked_completed' ) );

        // Add Thickbox functionality.
        add_thickbox();
    }

    /**
	 * Register scripts and styles for the admin area functionality of the plugin.
     *
     * This method is responsible for registering the necessary scripts and styles for the admin area functionality of the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
    public function enqueue_scripts() {

        global $pagenow, $hook_suffix;

		if ( ( 'upload.php' === $pagenow || 'iloveimg_page_iloveimg-compress-admin-page' === $hook_suffix || 'iloveimg_page_iloveimg-watermark-admin-page' === $hook_suffix || 'media-new.php' === $pagenow || 'post.php' === $pagenow ) && get_current_screen()->post_type !== 'product' ) {

            // Enqueue the main JavaScript file.
            wp_enqueue_script(
                self::NAME . '_admin',
                plugins_url( '/assets/js/main.min.js', __DIR__ ),
                array(),
                self::VERSION,
                true
            );

            // Enqueue the main CSS file.
            wp_enqueue_style(
                self::NAME . '_admin',
                plugins_url( '/assets/css/app.min.css', __DIR__ ),
                array(),
                self::VERSION
            );
		}
    }

    /**
     * Handle the AJAX request to compress images using the iLoveIMG library.
     *
     * This method is responsible for handling an AJAX request to compress images using the iLoveIMG library. It checks for the presence of an 'id' parameter in the POST request, initializes the Ilove_Img_Compress_Process class, and attempts to compress the specified attachment. If compression is successful, it renders the compression details; otherwise, it displays a message indicating the need for more files.
     *
     * @since 1.0.0
     * @access public
     */
    public function ilove_img_compress_library() {

        if ( isset( $_POST['id'] ) && isset( $_POST['imgnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['imgnonce'] ) ) ) ) {
            $ilove         = new Ilove_Img_Compress_Process();
            $attachment_id = intval( $_POST['id'] );
            $images        = $ilove->compress( $attachment_id );

            if ( ! $images['error'] ) {
                Ilove_Img_Compress_Resources::render_compress_details( $attachment_id );
            } else {
                ?>
                <p><?php echo esc_html( $images['error_msg'] ); ?></p>
                <?php
            }
        }
        wp_die();
    }

    /**
     * Handle completion of watermarking and initiate asynchronous compression if auto-compression is enabled.
     *
     * This method is triggered when watermarking of an image is completed. It checks if automatic compression is enabled (indicated by a value of 1 in the configuration), and if so, it initiates an asynchronous compression process for the specified attachment.
     *
     * @param int $attachment_id The ID of the attachment for which watermarking is completed.
     *
     * @since 1.0.0
     * @access public
     */
    public function iloveimg_watermarked_completed( $attachment_id ) {
        if ( (int) Ilove_Img_Compress_Resources::is_auto_compress() === 1 ) {
            $this->async_compress( $attachment_id );
        }
    }

    /**
     * Handle the AJAX request to check if an image is compressed and return appropriate responses.
     *
     * This method is responsible for handling an AJAX request to check the compression status of an image. It checks for the presence of an 'id' parameter in the POST request, retrieves the status of compression for the specified attachment, and based on the status, it returns different responses. If the status is 1 or 3, it returns a 500 HTTP response code. If the status is 2, it renders the compression details. If the status is 0 or not set, it returns a message indicating the need to try again or purchase more files.
     *
     * @since 1.0.0
     * @access public
     */
    public function ilove_img_compress_library_is_compressed() {
        if ( isset( $_POST['id'] ) && isset( $_POST['imgnonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['imgnonce'] ) ) ) ) {
            $attachment_id   = intval( $_POST['id'] );
            $status_compress = get_post_meta( $attachment_id, 'iloveimg_status_compress', true );

            $images_compressed = Ilove_Img_Compress_Resources::get_sizes_compressed( $attachment_id );

            if ( ( 1 === (int) $status_compress || 3 === (int) $status_compress ) ) {
                update_post_meta( $attachment_id, 'iloveimg_status_compress', 0 );
                http_response_code( 500 );
            } elseif ( 2 === (int) $status_compress ) {
                Ilove_Img_Compress_Resources::render_compress_details( $attachment_id );
            } elseif ( 0 === (int) $status_compress && ! $status_compress ) {
                echo 'Try again or buy more credits';
            }
        }
        wp_die();
    }

    /**
     * Add the 'Status Compress' column to the media library if the plugin is activated.
     *
     * This method adds a new column titled 'Status Compress' to the media library columns array. The column is added only if the plugin is activated (indicated by a non-zero value). This column can be used to display the status of image compression.
     *
     * @param array $columns An array of existing columns in the media library.
     *
     * @return array An updated array of columns including the 'Status Compress' column, if applicable.
     *
     * @since 1.0.0
     * @access public
     */
    public function column_id( $columns ) {
        if ( (int) Ilove_Img_Compress_Resources::is_activated() === 0 ) {
            return $columns;
        }
        $columns['iloveimg_status_compress'] = __( 'Status Compress', 'iloveimg' );
        return $columns;
    }

    /**
     * Handle the content of the 'Status Compress' column in the media library.
     *
     * This method is responsible for determining and displaying the content of the 'Status Compress' column in the media library. It checks if the current column name matches 'iloveimg_status_compress' and then calls a method to get and render the status for the corresponding media item.
     *
     * @param string $column_name The name of the current column being processed.
     * @param int    $column_id   The ID of the media item corresponding to the column.
     *
     * @since 1.0.0
     * @access public
     */
    public function column_id_row( $column_name, $column_id ) {
        if ( 'iloveimg_status_compress' === $column_name ) {
            Ilove_Img_Compress_Resources::get_status_of_column( $column_id );
        }
    }

    /**
     * Process attachment metadata and initiate asynchronous compression if conditions are met.
     *
     * This method processes the metadata of an attachment, updates the 'iloveimg_status_compress' post meta value to indicate that the image is not compressed, and then checks various conditions. If automatic compression is enabled, the user is logged in, and the plugin is activated, it updates the attachment metadata and initiates an asynchronous compression process for the attachment.
     *
     * @param array $metadata      The metadata of the attachment being processed.
     * @param int   $attachment_id The ID of the attachment being processed.
     *
     * @return array The updated metadata.
     *
     * @since 1.0.0
     * @access public
     */
    public function process_attachment( $metadata, $attachment_id ) {
        update_post_meta( $attachment_id, 'iloveimg_status_compress', 0 ); // status no compressed

        $images_restore = null !== get_option( 'iloveimg_images_to_restore', null ) ? json_decode( get_option( 'iloveimg_images_to_restore' ), true ) : array();

        if ( (int) Ilove_Img_Compress_Resources::is_auto_compress() === 1 && Ilove_Img_Compress_Resources::is_loggued() && (int) Ilove_Img_Compress_Resources::is_activated() === 1 && ! in_array( $attachment_id, $images_restore, true ) ) {
            wp_update_attachment_metadata( $attachment_id, $metadata );
            $this->async_compress( $attachment_id );
        }

        return $metadata;
    }

    /**
     * Initiate an asynchronous image compression request for the specified attachment.
     *
     * This method initiates an asynchronous image compression request by making a POST request to the WordPress admin-ajax endpoint. It provides the necessary parameters, including the 'action' as 'ilove_img_compress_library' and the 'id' of the attachment to be compressed.

     * @param int $attachment_id The ID of the attachment to be compressed.
     *
     * @since 1.0.0
     * @access public
     */
    public function async_compress( $attachment_id ) {
        $args = array(
            'method'    => 'POST',
            'timeout'   => 0.01,
            'blocking'  => false,
            'body'      => array(
				'action'   => 'ilove_img_compress_library',
				'id'       => $attachment_id,
                'imgnonce' => self::get_img_nonce(),
			),
            'cookies'   => $_COOKIE,
            'sslverify' => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        );
        wp_remote_post( admin_url( 'admin-ajax.php' ), $args );
    }

    /**
     * Display admin notices based on various conditions.
     *
     * This method is responsible for displaying admin notices in the WordPress admin area. It checks for different conditions, including the user's login status, potential account errors, and limitations on file usage. Based on these conditions, it displays appropriate notices to inform the user about their status and provide necessary actions.

     * @since 1.0.0
     * @access public
     */
    public function show_notices() {
        if ( ! Ilove_Img_Compress_Resources::is_loggued() ) {
			?>
            <div class="notice notice-warning is-dismissible">
                <p><strong>iLoveIMG</strong> - Please you need to be logged or registered. <a href="<?php echo esc_url( admin_url( 'admin.php?page=iloveimg-compress-admin-page' ) ); ?>">Go to settings</a></p>
            </div>
            <?php
        }

        if ( get_option( 'iloveimg_account_error' ) ) {
            $iloveimg_account_error = json_decode( get_option( 'iloveimg_account_error' ), true );

            if ( 'login' === $iloveimg_account_error['action'] ) :
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>Your email or password is wrong.</p>
                </div>
            <?php endif; ?>
            <?php if ( 'register' === $iloveimg_account_error['action'] ) : ?>
                <div class="notice notice-error is-dismissible">
                    <p>This email address has already been taken.</p>
                </div>
            <?php endif; ?>
            <?php if ( 'register_limit' === $iloveimg_account_error['action'] ) : ?>
                <div class="notice notice-error is-dismissible">
                    <p>You have reached limit of different users to use this WordPress plugin. Please relogin with one of your existing users.</p>
                </div>
            <?php endif; ?>
            <?php

        }
        // do query
        if ( get_option( 'iloveimg_account' ) ) {
            $account = json_decode( get_option( 'iloveimg_account' ), true );
            if ( ! array_key_exists( 'error', $account ) ) {
                $token    = $account['token'];
                $response = wp_remote_get(
                    ILOVE_IMG_COMPRESS_USER_URL . '/' . $account['id'],
                    array(
                        'headers' => array( 'Authorization' => 'Bearer ' . $token ),
                    )
                );

                if ( isset( $response['response']['code'] ) && 200 === (int) $response['response']['code'] ) {
                    $account = json_decode( $response['body'], true );

                    if ( (int) $account['files_used'] >= (int) $account['free_files_limit'] &&
                    (int) $account['package_files_used'] >= (int) $account['package_files_limit'] &&
                    ( isset( $account['subscription_files_used'] ) && (int) $account['subscription_files_used'] >= (int) $account['subscription_files_limit'] ) ) {
                        ?>
                        <div class="notice notice-warning is-dismissible">
                            <p><strong>iLoveIMG</strong> - Please you need more credits. <a href="https://iloveapi.com/pricing" target="_blank">Buy more credits</a></p>
                        </div>
                        <?php
                    }
                }
            }
        }
    }

    /**
     * Display iLoveIMG information in the media details section.
     *
     * This method is responsible for displaying iLoveIMG-related information in the media details section of a post or media item. It retrieves the compression status and information about compressed image sizes and renders the appropriate content accordingly.

     * @since 1.0.0
     * @access public
     * @param \WP_Post $post Post object.
     */
    public function show_media_info( $post ) {
        $mime_type_accepted = array( 'image/jpeg', 'image/png', 'image/gif' );

        if ( in_array( $post->post_mime_type, $mime_type_accepted, true ) ) {

            echo '<div class="misc-pub-section iloveimg-compress-images">';
            echo '<h4>';
            esc_html_e( 'iLoveIMG', 'iloveimg' );
            echo '</h4>';
            echo '<div class="iloveimg-container">';
            echo '<table><tr><td>';
            $status_compress = get_post_meta( $post->ID, 'iloveimg_status_compress', true );

            $images_compressed = Ilove_Img_Compress_Resources::get_sizes_compressed( $post->ID );

            if ( 2 === (int) $status_compress ) {
                Ilove_Img_Compress_Resources::render_compress_details( $post->ID );
                Ilove_Img_Compress_Resources::render_button_restore( $post->ID );
            } else {
                Ilove_Img_Compress_Resources::get_status_of_column( $post->ID );
            }
            echo '</td></tr></table>';
            echo '</div>';
            echo '</div>';
        }
    }

    /**
     * Return Nonce seucrity code.
     *
     * @since 1.0.6
     * @access public
     */
    public static function get_img_nonce() {
        return self::$img_nonce;
    }

    /**
     * Check if any 'iloveimg' related plugins are activated.
     *
     * This function iterates through all installed plugins and checks if any of them are related to 'iloveimg'. It specifically
     * looks for plugins with names that start with 'iloveimg'. If such a plugin is found, it further checks if it is active.
     * If an active 'iloveimg' plugin related to 'watermark' is found, it returns true.
     *
     * @since 1.0.6
     * @return bool True if an active 'iloveimg' related plugin is found, false otherwise.
     */
    public static function check_iloveimg_plugins_is_activated() {
        $all_plugins = get_plugins();

        $iloveimg_watermark_found = false;

        foreach ( $all_plugins as $plugin_file => $plugin_info ) {

            if ( strpos( $plugin_file, 'iloveimg' ) || strpos( $plugin_file, 'ilove-img' ) ) {

                if ( is_plugin_active( $plugin_file ) ) {

                    if ( strpos( $plugin_file, 'watermark' ) !== false ) {
                        $iloveimg_watermark_found = true;

                        return $iloveimg_watermark_found;
                    }
                }
            }
        }

        return $iloveimg_watermark_found;
    }

    /**
     * Handle the AJAX request to restore all watermarked/compressed images.
     *
     * This method is responsible for processing an AJAX request to restore all watermarked/compressed images. It checks for the presence of a backup folder, restores the original images from the backup, and removes associated metadata and options related to watermarked and compressed images.
     *
     * @since 2.1.0
     */
    public function ilove_img_restore_all() {
        if ( is_dir( ILOVE_IMG_COMPRESS_BACKUP_FOLDER ) ) {

            $images_restore = json_decode( get_option( 'iloveimg_images_to_restore', array() ), true );

            foreach ( $images_restore as $key => $value ) {
                Ilove_Img_Compress_Resources::rcopy( ILOVE_IMG_COMPRESS_BACKUP_FOLDER . basename( get_attached_file( $value ) ), get_attached_file( $value ) );

                delete_post_meta( $value, 'iloveimg_status_watermark' );
                delete_post_meta( $value, 'iloveimg_watermark' );
                delete_post_meta( $value, 'iloveimg_status_compress' );
                delete_post_meta( $value, 'iloveimg_compress' );

                wp_delete_file( ILOVE_IMG_COMPRESS_BACKUP_FOLDER . basename( get_attached_file( $value ) ) );

                delete_option( 'iloveimg_images_to_restore' );

            }
        }

        wp_die();
    }

    /**
     * Handle the AJAX request to restore an watermarked/compressed image.
     *
     * This method is responsible for processing an AJAX request to restore an watermarked/compressed image. It checks for the presence of a backup folder, restores the original images from the backup, and removes associated metadata and options related to watermarked and compressed images.
     *
     * @since 2.1.0
     */
    public function ilove_img_restore() {

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ) ) ) {
            wp_send_json_error( 'Error processing your request. Invalid Nonce code', 401 );
        }

        if ( ! isset( $_POST['id'] ) ) {
            wp_send_json_error( 'Error processing your request. Invalid Image ID', 400 );
        }

        $attachment_id  = intval( $_POST['id'] );
        $images_restore = null !== get_option( 'iloveimg_images_to_restore', null ) ? json_decode( get_option( 'iloveimg_images_to_restore' ), true ) : array();
        $key_founded    = array_search( $attachment_id, $images_restore, true );

        if ( ! in_array( $attachment_id, $images_restore, true ) ) {
            wp_send_json_error( 'Sorry. There is no backup for this file', 404 );
        }

        Ilove_Img_Compress_Resources::rcopy( ILOVE_IMG_COMPRESS_BACKUP_FOLDER . basename( get_attached_file( $attachment_id ) ), get_attached_file( $attachment_id ) );

        Ilove_Img_Compress_Resources::regenerate_attachment_data( $attachment_id );

        delete_post_meta( $attachment_id, 'iloveimg_status_watermark' );
        delete_post_meta( $attachment_id, 'iloveimg_watermark' );
        delete_post_meta( $attachment_id, 'iloveimg_status_compress' );
        delete_post_meta( $attachment_id, 'iloveimg_compress' );

        if ( false !== $key_founded ) {
            unset( $images_restore[ $key_founded ] );
            wp_delete_file( ILOVE_IMG_COMPRESS_BACKUP_FOLDER . basename( get_attached_file( $attachment_id ) ) );
            Ilove_Img_Compress_Resources::update_option( 'iloveimg_images_to_restore', wp_json_encode( $images_restore ) );
        }

        wp_send_json_success( __( 'It was restored correctly', 'iloveimg' ), 200 );
    }

    /**
     * Handle the AJAX request to clear the backup of watermarked/compressed images.
     *
     * This method is responsible for processing an AJAX request to clear the backup of watermarked/compressed images. It checks for the presence of a backup folder and, if found, deletes the entire backup folder and related options.
     *
     * @since 2.1.0
     */
    public function ilove_img_compress_clear_backup() {
        if ( is_dir( ILOVE_IMG_COMPRESS_BACKUP_FOLDER ) ) {
            Ilove_Img_Compress_Resources::rrmdir( ILOVE_IMG_COMPRESS_BACKUP_FOLDER );
            delete_option( 'iloveimg_images_to_restore' );
        }

        wp_die();
    }
}