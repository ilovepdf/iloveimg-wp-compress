<?php
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
    const VERSION = '1.0.5';

    /**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    NAME    The string used to uniquely identify this plugin.
	 */
	const NAME = 'Ilove_Img_Compress_plugin';

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
        // Enqueue scripts for the admin area.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Manage media columns.
        add_filter( 'manage_media_columns', array( $this, 'column_id' ) );
        add_filter( 'manage_media_custom_column', array( $this, 'column_id_row' ), 10, 2 );

        // Handle AJAX requests for the library.
        add_action( 'wp_ajax_ilove_img_compress_library', array( $this, 'ilove_img_compress_library' ) );
        add_action( 'wp_ajax_ilove_img_compress_library_is_compressed', array( $this, 'ilove_img_compress_library_is_compressed' ) );

        // Process attachment metadata.
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'process_attachment' ), 10, 2 );

        // Display media information in the attachment submit box.
        add_action( 'attachment_submitbox_misc_actions', array( $this, 'show_media_info' ) );

        // Check if the iLoveIMG library class exists, and initialize it if not.
        if ( ! class_exists( 'Ilove_Img_Library_Init' ) ) {
            require_once 'class-ilove-img-library-init.php';
            new Ilove_Img_Library_Init();
        }

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
        // Enqueue the main JavaScript file.
        wp_enqueue_script(
            self::NAME . '_admin',
            plugins_url( '/assets/js/main.js', __DIR__ ),
			array(),
            self::VERSION,
            true
        );

        // Enqueue the main CSS file.
        wp_enqueue_style(
            self::NAME . '_admin',
            plugins_url( '/assets/css/app.css', __DIR__ ),
			array(),
            self::VERSION
		);
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
        if ( isset( $_POST['id'] ) ) {
            $ilove         = new Ilove_Img_Compress_Process();
            $attachment_id = intval( $_POST['id'] );
            $images        = $ilove->compress( $attachment_id );
            if ( false !== $images ) {
                Ilove_Img_Compress_Resources::render_compress_details( $attachment_id );
            } else {
                ?>
                <p>You need more files</p>
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
        if ( isset( $_POST['id'] ) ) {
            $attachment_id   = intval( $_POST['id'] );
            $status_compress = get_post_meta( $attachment_id, 'iloveimg_status_compress', true );

            $images_compressed = Ilove_Img_Compress_Resources::get_sizes_compressed( $attachment_id );
            if ( ( 1 === (int) $status_compress || 3 === (int) $status_compress ) ) {
                http_response_code( 500 );
            } elseif ( 2 === (int) $status_compress ) {
                Ilove_Img_Compress_Resources::render_compress_details( $attachment_id );
            } elseif ( 0 === (int) $status_compress && ! $status_compress ) {
                echo 'Try again or buy more files';
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
        if ( 'iloveimg_status_compress' == $column_name ) {
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
        if ( (int) Ilove_Img_Compress_Resources::is_auto_compress() === 1 && Ilove_Img_Compress_Resources::is_loggued() && (int) Ilove_Img_Compress_Resources::is_activated() === 1 ) {
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
				'action' => 'ilove_img_compress_library',
				'id'     => $attachment_id,
			),
            'cookies'   => isset( $_COOKIE ) && is_array( $_COOKIE ) ? $_COOKIE : array(),
            'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
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
                <p><strong>iLoveIMG</strong> - Please you need to be logged or registered. <a href="<?php echo admin_url( 'admin.php?page=iloveimg-compress-admin-page' ); ?>">Go to settings</a></p>
            </div>
            <?php
        }

        if ( get_option( 'iloveimg_account_error' ) ) {
                $iloveimg_account_error = unserialize( get_option( 'iloveimg_account_error' ) );
            if ( 'login' == $iloveimg_account_error['action'] ) :
                ?>
                <div class="notice notice-error is-dismissible">
                    <p>Your email or password is wrong.</p>
                </div>
            <?php endif; ?>
            <?php if ( 'register' == $iloveimg_account_error['action'] ) : ?>
                <div class="notice notice-error is-dismissible">
                    <p>This email address has already been taken.</p>
                </div>
            <?php endif; ?>
            <?php if ( 'register_limit' == $iloveimg_account_error['action'] ) : ?>
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

                if ( isset( $response['response']['code'] ) && 200 == $response['response']['code'] ) {
                    $account = json_decode( $response['body'], true );
                    if ( $account['files_used'] >= $account['free_files_limit'] && $account['package_files_used'] >= $account['package_files_limit'] && @$account['subscription_files_used'] >= $account['subscription_files_limit'] ) {
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

    /**
     * Display iLoveIMG information in the media details section.
     *
     * This method is responsible for displaying iLoveIMG-related information in the media details section of a post or media item. It retrieves the compression status and information about compressed image sizes and renders the appropriate content accordingly.

     * @since 1.0.0
     * @access public
     */
    public function show_media_info() {
        global $post;
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
        } else {
            Ilove_Img_Compress_Resources::get_status_of_column( $post->ID );
        }
        echo '</td></tr></table>';
        echo '</div>';
        echo '</div>';
    }
}