<?php
use Ilove_Img_Compress\Ilove_Img_Compress_Resources;
?>
<div class="iloveimg_settings__overview__statistics">
	<h3><?php echo esc_html_x( 'Overview', 'title: admin settings overview', 'iloveimg' ); ?></h3>
	<div>
		<div class="iloveimg_settings__overview__statistics__column_left">
			<?php $ilove_img_images_sizes = Ilove_Img_Compress_Resources::get_files_sizes(); ?>
			<h4>
				<?php
				printf(
					wp_kses_post(
						/* translators: %1$s and %2$s images sizes */
						__( 'Original size %1$s / Optimized size %2$s', 'iloveimg' )
					),
					'<strong>' . (float) round( $ilove_img_images_sizes[0] / 1024 / 1024, 2 ) . ' MB</strong>',
					'<strong>' . (float) round( $ilove_img_images_sizes[1] / 1024 / 1024, 2 ) . ' MB</strong>'
				);
				?>
			</h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo ( $ilove_img_images_sizes[0] > 0 ) ? (float) ( 100 - ( ( $ilove_img_images_sizes[1] * 100 ) / $ilove_img_images_sizes[0] ) ) : 0; ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
            	
				<?php
				$ilove_img_porcentage_saved = $ilove_img_images_sizes[0] > 0 ? 100 - round( ( $ilove_img_images_sizes[1] * 100 ) / $ilove_img_images_sizes[0] ) : 0;

				printf(
					wp_kses_post(
						/* translators: %s porcentage of size saved */
						__( '%s Thats the size you saved by using iLoveIMG', 'iloveimg' )
					),
					'<p class="iloveimg_saving__number">' . (float) $ilove_img_porcentage_saved . '%</p>'
				);
				?>
				
            </div>
		</div>
		<div class="iloveimg_settings__overview__statistics__column_right">
			<h4>
				<?php
				printf(
					wp_kses_post(
						/* translators: %1$s and %2$s images sizes */
						__( 'Original Compressed images %1$s / Uploaded images %2$s', 'iloveimg' )
					),
					'<strong>' . (int) Ilove_Img_Compress_Resources::get_files_compressed() . '</strong>',
					'<strong>' . (int) Ilove_Img_Compress_Resources::get_total_images() . '</strong>'
				);
				?>
			</h4>
			<div class="iloveimg_percent  ">
                <div class="iloveimg_percent-total" style="width: <?php echo ( Ilove_Img_Compress_Resources::get_total_images() > 0 ) ? (float) round( ( Ilove_Img_Compress_Resources::get_files_compressed() * 100 ) / Ilove_Img_Compress_Resources::get_total_images() ) : 0; ?>%;"></div>
            </div>
            <div class="iloveimg_saving">
				<?php
				$ilove_img_porcentage_optimized = Ilove_Img_Compress_Resources::get_total_images() > 0 ? round( ( Ilove_Img_Compress_Resources::get_files_compressed() * 100 ) / Ilove_Img_Compress_Resources::get_total_images() ) : 0;

				printf(
					wp_kses_post(
						/* translators: %s porcentage optimized images */
						__( '%s Total images you optimized with iLoveIMG', 'iloveimg' )
					),
					'<p class="iloveimg_saving__number">' . (float) $ilove_img_porcentage_optimized . '%</p>'
				);
				?>
            </div>
		</div>
	</div>
</div>