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
        ?>
        <form id="images-filter" method="get">
            <input type="hidden" name="page" value="iloveimg-media-page" />
            <?php $ilove_img_test_list_table->display(); ?>
        </form>
    </div>
</div>
