<?php 
//Create an instance of our package class...
$testListTable = new iLoveIMG_Compress_Media_List_Table();
//Fetch, prepare, sort, and filter our data...
$testListTable->prepare_items();

?>
<div class="wrap iloveimg_settings">
	<img src="<?php echo iLoveIMG_Compress_Plugin_URL ?>assets/images/logo.svg" class="logo" />
	<div class="iloveimg_settings__overview">
        <?php require_once "overview.php"; ?>
        <?php if($testListTable->total_items): ?>
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
            <?php $testListTable->display() ?>
        </form>
        
    </div>
</div>