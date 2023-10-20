<div class="iloveimg_settings__overview__statistics">
	<h3>Overview</h3>
	<div>
		<div class="iloveimg_settings__overview__statistics__column_left">
			<?php $ilove_img_images_sizes = Ilove_Img_Compress_Resources::get_files_sizes(); ?>
			<h4>Original size <strong><?php echo (float) round( $ilove_img_images_sizes[0] / 1024 / 1024, 2 ); ?> MB</strong> / Optimized size <strong><?php echo (float) round( $ilove_img_images_sizes[1] / 1024 / 1024, 2 ); ?> MB</strong></h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo (int) ( $ilove_img_images_sizes[0] > 0 ) ? ( 100 - ( ( (int) $ilove_img_images_sizes[1] * 100 ) / $ilove_img_images_sizes[0] ) ) : 0; ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	<p class="iloveimg_saving__number"><?php echo (int) ( $ilove_img_images_sizes[0] > 0 ) ? (int) ( 100 - round( ( $ilove_img_images_sizes[1] * 100 ) / $ilove_img_images_sizes[0] ) ) : 0; ?>%</p>
            	<p>Thats the size you saved by using iLoveIMG</p>
            </div>
		</div>
		<div class="iloveimg_settings__overview__statistics__column_right">
			<h4>Original Compressed images <strong><?php echo (int) Ilove_Img_Compress_Resources::get_files_compressed(); ?></strong> / Uploaded images <strong><?php echo (int) Ilove_Img_Compress_Resources::get_total_images(); ?></strong></h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo (int) ( Ilove_Img_Compress_Resources::get_total_images() > 0 ) ? (int) round( ( Ilove_Img_Compress_Resources::get_files_compressed() * 100 ) / Ilove_Img_Compress_Resources::get_total_images() ) : 0; ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	<p class="iloveimg_saving__number"><?php echo ( Ilove_Img_Compress_Resources::get_total_images() > 0 ) ? (int) round( ( Ilove_Img_Compress_Resources::get_files_compressed() * 100 ) / Ilove_Img_Compress_Resources::get_total_images() ) : 0; ?>%</p>
            	<p>Total images you optimized with iLoveIMG</p>
            </div>
		</div>
	</div>
</div>
<!-- <div class="iloveimg_settings__overview__compress-all">
	<button type="button" id="iloveimg_allcompress" class="iloveimg-compress-all button button-small button-primary">Compress All</button>
</div> -->