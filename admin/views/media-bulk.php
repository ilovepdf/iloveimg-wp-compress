<?php
/**
 * Media Bulk View.
 *
 * @package iloveimgcompress
 */

use Ilove_Img_Compress\Ilove_Img_Compress_Media_List_Table;

// Create an instance of our package class...
$ilove_img_test_list_table = new Ilove_Img_Compress_Media_List_Table();
// Fetch, prepare, sort, and filter our data...
$ilove_img_test_list_table->prepare_items();

?>
<div class="wrap iloveimg_settings">
    <img src="<?php echo esc_url( ILOVE_IMG_COMPRESS_PLUGIN_URL . 'assets/images/logo.svg' ); ?>" class="logo" />
    <div class="iloveimg_settings__overview">
        <?php require_once 'overview.php'; ?>
        <?php if ( $ilove_img_test_list_table->total_items ) : ?>
            <div class="iloveimg_settings__overview__compressAll">
                <button type="button" id="iloveimg_allcompress" class="iloveimg-compress-all button button-small button-primary">
                    <span><?php echo esc_html_x( 'Compress all uploaded images', 'button', 'iloveimg' ); ?></span>
                    <div class="iloveimg-compress-all__percent" style="width: 0%;"></div>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="wrap">
        <?php
        // phpcs:disable WordPress.Security.NonceVerification.Recommended -- Displaying success message from redirect, no action taken
        // phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Local scope variables for view template

        // Display bulk compression results
        $iloveimg_compress_bulk_status   = isset( $_GET['iloveimg-bulk-compression'] ) ? sanitize_text_field( wp_unslash( $_GET['iloveimg-bulk-compression'] ) ) : '';
        $iloveimg_compress_updated_count = isset( $_GET['updated_count'] ) ? intval( wp_unslash( $_GET['updated_count'] ) ) : 0;
        $iloveimg_compress_success_ids   = get_transient( 'iloveimg_bulk_success' );
        $iloveimg_compress_error_items   = get_transient( 'iloveimg_bulk_errors' );

        if ( 'success' === $iloveimg_compress_bulk_status && ! empty( $iloveimg_compress_success_ids ) ) {
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    /* translators: %s: number of images compressed */
                    echo esc_html( sprintf( _n( '%s image compressed successfully.', '%s images compressed successfully.', count( $iloveimg_compress_success_ids ), 'iloveimg' ), count( $iloveimg_compress_success_ids ) ) );
                    ?>
                </p>
            </div>
            <?php
            delete_transient( 'iloveimg_bulk_success' );
        } elseif ( 'partial' === $iloveimg_compress_bulk_status ) {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php
                    /* translators: %1$s: number of compressed, %2$s: number of failed */
                    echo esc_html( sprintf( _x( '%1$s images compressed, %2$s images failed.', 'bulk action result', 'iloveimg' ), count( $iloveimg_compress_success_ids ), count( $iloveimg_compress_error_items ) ) );
                    ?>
                </p>
            </div>
            <?php
            delete_transient( 'iloveimg_bulk_success' );
            delete_transient( 'iloveimg_bulk_errors' );
        } elseif ( 'error' === $iloveimg_compress_bulk_status && ! empty( $iloveimg_compress_error_items ) ) {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php
                    /* translators: %s: number of images that failed */
                    echo esc_html( sprintf( _n( '%s image failed to compress.', '%s images failed to compress.', count( $iloveimg_compress_error_items ), 'iloveimg' ), count( $iloveimg_compress_error_items ) ) );
                    ?>
                </p>
            </div>
            <?php
            delete_transient( 'iloveimg_bulk_errors' );
        }

        if ( isset( $_GET['deleted'] ) && intval( $_GET['deleted'] ) > 0 ) :
			?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    /* translators: %s: number of images deleted */
                    echo esc_html( sprintf( _n( '%s image deleted successfully.', '%s images deleted successfully.', intval( $_GET['deleted'] ), 'iloveimg' ), intval( $_GET['deleted'] ) ) );
                    ?>
                </p>
            </div>
			<?php
        endif;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
        // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
        ?>
        <form id="images-filter" method="get">
            <input type="hidden" name="page" value="iloveimg-media-page" />
            <?php wp_nonce_field( 'iloveimg_bulk_compress_action', 'iloveimg_bulk_nonce' ); ?>
            <?php $ilove_img_test_list_table->display(); ?>
        </form>
    </div>
</div>
