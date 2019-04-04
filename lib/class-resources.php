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
        $_aOptions = get_option( 'iLoveIMG_CreatePageGroup', array() );
        $image_sizes = $_aOptions['image_sizes'];
        $count = 0;
        foreach($image_sizes as $image){
            if($image){
                $count++;
            }
        }
        return $count;
    }

    public function isAutoCompress(){
        $_aOptions = get_option( 'iLoveIMG_CreatePageGroup', array() );
        return $_aOptions['autocompress'];
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

}