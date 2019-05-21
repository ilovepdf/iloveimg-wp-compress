
<div class="wrap iloveimg_settings">
	<img src="<?php echo plugins_url("/iloveimg-compress/assets/images/logo.svg") ?>" class="logo" />
	<div class="iloveimg_settings__overview">
        <?php require_once "overview.php"; ?>
    </div>
    
	<?php 
	//Create an instance of our package class...
    $testListTable = new Media_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $testListTable->prepare_items();
    
    ?>
    <?php if($testListTable->total_items): ?>
    	<button type="button" id="iloveimg_allcompress" style="float: right; clear: right;" class="iloveimg-compress-all button button-small button-primary">Compress All</button>
	<?php endif; ?>
    <div class="wrap">
     <form id="images-filter" method="get">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
            <!-- Now we can render the completed list table -->
            <?php $testListTable->display() ?>
        </form>
        
    </div>
	<?php if($testListTable->total_items): ?>
    	<button type="button" id="iloveimg_allcompress" style="float: right; clear: right;" class="iloveimg-compress-all button button-small button-primary">Compress All</button>
	<?php endif; ?>
</div>