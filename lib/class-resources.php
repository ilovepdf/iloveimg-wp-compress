<?php

class iLoveIMG_Resources{

    public function getTypeImages(){
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
    
    public function getSaving($images){
        $initial = $compressed = 0;
        foreach($images as $image){
            if(!is_null($image['compressed'])){
                $initial+=$image['initial'];
                $compressed+=$image['compressed'];
            }
        }
        return round(100 - (($compressed * 100) / $initial));
    }

    public function getSizesEnabled(){
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

    public function isAutoCompress(){
        $_aOptions = unserialize(get_option('iloveimg_options_compress'));
        return ($_aOptions['iloveimg_field_autocompress']) ? 1 : 0;
    }

    public function getSizesCompressed($columnID){
        $images = get_post_meta($columnID, 'iloveimg_compress', true);
        $count = 0;
        foreach($images as $image){
            if(!is_null($image['compressed'])){
                $count++;
            }
        }
        return $count;
    }

    public function getStatusOfColumn($columnID){
        $post = get_post($columnID);
        if(strpos($post->post_mime_type, "image/") !== false):
            $_sizes = get_post_meta($columnID, 'iloveimg_compress', true);
            $status_compress = get_post_meta($columnID, 'iloveimg_status_compress', true);
            $imagesCompressed = iLoveIMG_Resources::getSizesCompressed($columnID);
            
            if($_sizes && $imagesCompressed):
                add_thickbox();
                ?>
                <div id="iloveimg_detaills_compress" style="display:none;">
                    <table>
                        <tr>
                            <th>Name</th><th>Initial</th><th>Compressed</th>
                            <?php
                            foreach($_sizes as $key => $size){
                                ?>
                                <tr><td><?php echo $key ?></td><td><?php echo round($size['initial']/1024) ?> KB</td><td><?php echo $size['compressed'] ? round($size['compressed']/1024) . " KB": 'No compressed' ?></td></tr>
                                <?php
                            }
                            ?>
                        </tr>
                    </table>
                </div>
                <p>Now <?php echo iLoveIMG_Resources::getSaving($_sizes) ?>% smaller!</p>
                <p><a href="#TB_inline?&width=500&height=500&inlineId=iloveimg_detaills_compress" class="thickbox"><?php echo $imagesCompressed ?> sizes compressed</a></p>
                <?php
            else:
                ?>
                    <p><?php echo iLoveIMG_Resources::getSizesEnabled() ?> sizes to be compressed</p>
                    <?php if(iLoveIMG_Resources::getSizesEnabled()) : ?>
                        <button type="button" class="iloveimg-compress button button-small button-primary" data-id="<?php echo $columnID ?>" <?php echo ((int)$status_compress === 1) ? 'disabled="disabled"' :  '' ?>>Compress</button>
                        <img src="<?php echo plugins_url( '/assets/images/spinner.gif', dirname(__FILE__) ) ?>" width="20" height="20" style="<?php echo ((int)$status_compress === 1) ? '' :  'display: none;' ?>" />
                    <?php else: ?>
                        <button type="button" class="button button-small button-primary" data-id="<?php echo $columnID ?>">Enable</button>
                <?php endif;
                if((int)$status_compress === 1){
                    //interval
                   ?><div class="iloveimg_compressing" style="display: none;" data-id="<?php echo $columnID ?>"></div><?php
                }
            endif;
        endif;
    }

}