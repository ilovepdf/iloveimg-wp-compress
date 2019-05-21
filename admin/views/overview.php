<div class="iloveimg_settings__overview__statistics">
	<h3>Overview</h3>
	<div>
		<div class="iloveimg_settings__overview__statistics__column_left">
			<?php $imagesSizes =  iLoveIMG_Compress_Resources::getFilesSizes(); ?>
			<h4>Original size <strong><?php echo round($imagesSizes[0]/1024/1024, 2) ?> MB</strong> / Optimized size <strong><?php echo round($imagesSizes[1]/1024/1024, 2) ?> MB</strong></h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo ($imagesSizes[0] > 0) ? (100 - (($imagesSizes[1] * 100)/$imagesSizes[0])) : 0 ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	<p class="iloveimg_saving__number"><?php echo ($imagesSizes[0] > 0) ? (100 - round(($imagesSizes[1] * 100)/$imagesSizes[0])) : 0 ?>%</p>
            	<p>Thats the size you saved by using iLoveIMG</p>
            </div>
		</div>
		<div class="iloveimg_settings__overview__statistics__column_right">
			<h4>Original Compressed images <strong><?php echo iLoveIMG_Compress_Resources::getFilesCompressed() ?></strong> / Uploaded images <strong><?php echo iLoveIMG_Compress_Resources::getTotalImages() ?></strong></h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo (iLoveIMG_Compress_Resources::getTotalImages() > 0) ? round((iLoveIMG_Compress_Resources::getFilesCompressed() * 100)/iLoveIMG_Compress_Resources::getTotalImages()) : 0 ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	<p class="iloveimg_saving__number"><?php echo (iLoveIMG_Compress_Resources::getTotalImages() > 0) ? round((iLoveIMG_Compress_Resources::getFilesCompressed() * 100)/iLoveIMG_Compress_Resources::getTotalImages()) : 0 ?>%</p>
            	<p>Total images you optimized with iLoveIMG</p>
            </div>
		</div>
	</div>
</div>
<!-- <div class="iloveimg_settings__overview__compress-all">
	<button type="button" id="iloveimg_allcompress" class="iloveimg-compress-all button button-small button-primary">Compress All</button>
</div> -->