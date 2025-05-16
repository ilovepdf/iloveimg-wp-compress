<?php
use Ilove_Img_Compress\Ilove_Img_Compress_Resources;
?>
<div class="iloveimg_settings__overview__statistics">
	<h3><?php esc_html_e( 'Overview', 'iloveimg' ); ?></h3>
	<div>
		<div class="iloveimg_settings__overview__statistics__column_left">
			<?php $ilove_img_images_sizes = Ilove_Img_Compress_Resources::get_files_sizes(); ?>
			<h4><?php esc_html_e( 'Original size', 'iloveimg' ); ?> <strong><?php echo (float) round( $ilove_img_images_sizes[0] / 1024 / 1024, 2 ); ?> MB</strong> / <?php esc_html_e( 'Optimized size', 'iloveimg' ); ?> <strong><?php echo (float) round( $ilove_img_images_sizes[1] / 1024 / 1024, 2 ); ?> MB</strong></h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo ( $ilove_img_images_sizes[0] > 0 ) ? (float) ( 100 - ( ( $ilove_img_images_sizes[1] * 100 ) / $ilove_img_images_sizes[0] ) ) : 0; ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	<p class="iloveimg_saving__number"><?php echo ( $ilove_img_images_sizes[0] > 0 ) ? (float) ( 100 - round( ( $ilove_img_images_sizes[1] * 100 ) / $ilove_img_images_sizes[0] ) ) : 0; ?>%</p>
            	<p><?php esc_html_e( 'Thats the size you saved by using iLoveIMG', 'iloveimg' ); ?></p>
            </div>
		</div>
		<div class="iloveimg_settings__overview__statistics__column_right">
			<h4><?php esc_html_e( 'Original Compressed images', 'iloveimg' ); ?> <strong><?php echo (int) Ilove_Img_Compress_Resources::get_files_compressed(); ?></strong> / <?php esc_html_e( 'Uploaded images', 'iloveimg' ); ?> <strong><?php echo (int) Ilove_Img_Compress_Resources::get_total_images(); ?></strong></h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo ( Ilove_Img_Compress_Resources::get_total_images() > 0 ) ? (float) round( ( Ilove_Img_Compress_Resources::get_files_compressed() * 100 ) / Ilove_Img_Compress_Resources::get_total_images() ) : 0; ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	<p class="iloveimg_saving__number"><?php echo ( Ilove_Img_Compress_Resources::get_total_images() > 0 ) ? (float) round( ( Ilove_Img_Compress_Resources::get_files_compressed() * 100 ) / Ilove_Img_Compress_Resources::get_total_images() ) : 0; ?>%</p>
            	<p><?php esc_html_e( 'Total images you optimized with iLoveIMG', 'iloveimg' ); ?></p>
            </div>
		</div>
	</div>
</div>