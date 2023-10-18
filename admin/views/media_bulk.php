<?php
/**
 * Media Bulk View.
 */

// Create an instance of our package class...
$test_list_table = new iLoveIMG_Compress_Media_List_Table();
// Fetch, prepare, sort, and filter our data...
$test_list_table->prepare_items();

?>
<div class="wrap iloveimg_settings">
	<img src="<?php echo ILOVE_IMG_COMPRESS_PLUGIN_URL; ?>assets/images/logo.svg" class="logo" />
	<div class="iloveimg_settings__overview">
        <?php require_once 'overview.php'; ?>
        <?php if ( $test_list_table->total_items ) : ?>
            <div class="iloveimg_settings__overview__compressAll">
                <button type="button" id="iloveimg_allcompress" class="iloveimg-compress-all button button-small button-primary">
                    <span>Compress all</span>
                    <div class="iloveimg-compress-all__percent" style="width: 0%;"></div>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <div class="wrap">
        <form id="images-filter" method="get">
            <?php $test_list_table->display(); ?>
        </form>
    </div>
</div>