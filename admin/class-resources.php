<?php

class iLoveIMG_Compress_Resources{

    public static function getTypeImages(){
        global $_wp_additional_image_sizes;
        
        $sizes = array();
        $sizes[] =  array(
            'field_id'        => "full",
            'type'            => 'checkbox',
            'label'           =>  "Original image",
            'default'           => true
        );
        foreach ( get_intermediate_image_sizes() as $_size ) {
            if ( in_array( $_size, array('thumbnail', 'medium', 'medium_large', 'large') ) ) {
                $width = get_option( "{$_size}_size_w" );
                $height = get_option( "{$_size}_size_h" );
            }elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
                $width = $_wp_additional_image_sizes[ $_size ]['width'];
                $height = $_wp_additional_image_sizes[ $_size ]['height'];
            }


            $sizes[] =  array(
                'field_id'        => $_size,
                'type'            => 'checkbox',
                'label'           =>  $_size . " (". (($width == "0")  ? "?" : $width) ."x". (($height == "0")  ? "?" : $height) .")",
                'default'           => true
            );

        }
        return $sizes;
    }
    
    public static function getSaving($images){
        $initial = $compressed = 0;
        foreach($images as $image){
            if(!is_null($image['compressed'])){
                $initial+=$image['initial'];
                $compressed+=$image['compressed'];
            }
        }
        return round(100 - (($compressed * 100) / $initial));
    }

    public static function getSizesEnabled(){
        $_aOptions = unserialize(get_option('iloveimg_options_compress'));
        $image_sizes = $_aOptions['iloveimg_field_sizes'];
        $count = 0;
        foreach($image_sizes as $image){
            if($image){
                $count++;
            }
        }
        return $count;
    }

    public static function isAutoCompress(){
        $_aOptions = unserialize(get_option('iloveimg_options_compress'));
        return (isset($_aOptions['iloveimg_field_autocompress'])) ? 1 : 0;
    }

    public static function isActivated(){
        $_aOptions = unserialize(get_option('iloveimg_options_compress'));
        return (isset($_aOptions['iloveimg_field_compress_activated'])) ? 1 : 0;
    }

    public static function getSizesCompressed($columnID){
        $images = get_post_meta($columnID, 'iloveimg_compress', true);
        $count = 0;
        if(!$images)
            return $count;
        foreach($images as $image){
            if(!is_null($image['compressed'])){
                $count++;
            }
        }
        return $count;
    }

    public static function isLoggued(){
        if(get_option('iloveimg_account')){
            $account = json_decode(get_option('iloveimg_account'), true);
            if(array_key_exists('error', $account)){
                return false;
            }
            return true;
        }else{
            return false;
        }
    }

    public static function render_compress_details($imageID){
        $_sizes = get_post_meta($imageID, 'iloveimg_compress', true);
        $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($imageID);
        
        ?>
        <div id="iloveimg_detaills_compress_<?php echo $imageID ?>" style="display:none;">
            <table class="table__details__sizes">
                <tr>
                    <th>Name</th><th>Initial</th><th>Compressed</th>
                    <?php
                    $total_size = 0;
                    $total_compressed = 0;
                    foreach($_sizes as $key => $size){
                        ?>
                        <tr>
                            <td><?php echo $key ?></td>
                            <td><?php echo round($size['initial']/1024) ?> KB</td>
                            <td><?php 
                                if($size['compressed']){
                                    echo round($size['compressed']/1024) . " KB";
                                    $total_size = $total_size + (int)$size['initial'];
                                    $total_compressed = $total_compressed + (int)$size['compressed'];
                                    ?><small class="iloveimg__badge__percent">-<?php echo (100 - round(($size['compressed'] * 100) / $size['initial'])) ?>%</small><?php
                                }else{
                                    echo 'Not compressed';
                                }
                                ?></td>
                            </tr>
                        <?php
                    }
                    ?>
                </tr>
            </table>
        </div>
        <!-- <p>Now <?php echo iLoveIMG_Compress_Resources::getSaving($_sizes) ?>% smaller!</p> -->
        <p><a href="#TB_inline?&width=450&height=340&inlineId=iloveimg_detaills_compress_<?php echo $imageID ?>" class="thickbox iloveimg_sizes_compressed" title="<?php echo get_the_title($imageID) ?>"><?php echo $imagesCompressed ?> sizes compressed</a><small class="iloveimg__badge__percent">-<?php echo (100 - round(($total_compressed * 100) / $total_size)) ?>%</small></p>
        <?php
    }

    public static function getStatusOfColumn($columnID){
        $post = get_post($columnID);
        if(strpos($post->post_mime_type, "image/jpg") !== false or strpos($post->post_mime_type, "image/jpeg") !== false or strpos($post->post_mime_type, "image/png") !== false or strpos($post->post_mime_type, "image/gif") !== false):
            $_sizes = get_post_meta($columnID, 'iloveimg_compress', true);
            $status_compress = (int)get_post_meta($columnID, 'iloveimg_status_compress', true);
            $imagesCompressed = iLoveIMG_Compress_Resources::getSizesCompressed($columnID);
            
            if($_sizes && $imagesCompressed):
                self::render_compress_details($columnID);
            else:
                ?>                    
                    <?php if(iLoveIMG_Compress_Resources::isLoggued()): ?>
                        <!-- <p><?php echo iLoveIMG_Compress_Resources::getSizesEnabled() ?> sizes to be compressed</p> -->
                        <?php if(iLoveIMG_Compress_Resources::getSizesEnabled()) : ?>
                            <button type="button" class="iloveimg-compress button button-small button-primary" data-id="<?php echo $columnID ?>" <?php echo ($status_compress === 1 || $status_compress === 3) ? 'disabled="disabled"' :  '' ?>>Compress</button>
                            <img src="<?php echo plugins_url( '/assets/images/spinner.gif', dirname(__FILE__) ) ?>" width="20" height="20" style="<?php echo ($status_compress === 1 || $status_compress === 3) ? '' :  'display: none;' ?>; margin-top: 7px" />
                            <?php if($status_compress === 3): ?>
                                <!-- <p>In queue...</p> -->
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo admin_url( 'admin.php?page=iloveimg-admin-page' ) ?>" class="iloveimg_link">Go to settings</button>
                        <?php endif;
                    else: ?>
                        <p>You need to be registered</p>
                        <a href="<?php echo admin_url( 'admin.php?page=iloveimg-admin-page' ) ?>" class="iloveimg_link">Go to settings</button>
                    <?php
                    endif;
                    if($status_compress === 1 || $status_compress === 3): ?>
                        <div class="iloveimg_compressing" style="display: none;" data-id="<?php echo $columnID ?>"></div>
                    <?php endif;
            endif;
        endif;
    }

    public static function getFilesCompressed(){
        global $wpdb;
        return (int)$wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_compress'" );
    }

    public static function getTotalImages(){
        $query_img_args = array(
        'post_type' => 'attachment',
        'post_mime_type' =>array(
                        'jpg|jpeg|jpe' => 'image/jpeg',
                        'gif' => 'image/gif',
                'png' => 'image/png',
                ),
        'post_status' => 'inherit',
        'posts_per_page' => -1,
        );
        $query_img = new WP_Query( $query_img_args );
        return (int)$query_img->post_count;
    }

    public static function getFilesSizes(){
        global $wpdb;
        $rows = $wpdb->get_results( "SELECT * FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_compress'" );
        $total = 0;
        $totalCompressed = 0;
        foreach ( $rows as $row ) 
        {
            $stadistics = unserialize($row->meta_value);
            foreach ($stadistics as $key => $value) {
                $total = $total + (int)$value['initial'];
                $totalCompressed = $totalCompressed + (int)$value['compressed'];
            }
        }
        return [$total, $totalCompressed];

    }

    

}