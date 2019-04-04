<?php
use Ilovepdf\CompressimageTask;
class iLoveIMG_Process{

    public $proyect_public = 'project_public_9ef8b6348283c8172cf1aede443d8df0_khhfA9126b563681d869d06b25f068422f53a';
    public $secret_key = 'secret_key_d1db014351ae2a73188ddb3a5d89bf2f_28HLl90321141562466414763b3dd8491c4b9';

    public function compress($imagesID){
        global $_wp_additional_image_sizes, $wpdb;

        $images = array();
        try { 
            update_post_meta($imagesID, 'iloveimg_status_compress', 1); //status compressing

            $_sizes = get_intermediate_image_sizes();
            
            array_unshift($_sizes,  "full");
            $_aOptions = get_option( 'iLoveIMG_CreatePageGroup', array() );
            

            foreach ( $_sizes as $_size ) {
                $image = wp_get_attachment_image_src($imagesID, $_size);
                $pathFile = $_SERVER["DOCUMENT_ROOT"] . str_replace(site_url(), "", $image[0]);
                $images[$_size] = array("initial" => filesize($pathFile),  "compressed" => null);
                if((int)$_aOptions['image_sizes'][$_size] !== 0){
                    $myTask = new CompressimageTask($this->proyect_public, $this->secret_key);
                    $file = $myTask->addFile($pathFile);
                    $myTask->execute();
                    $myTask->download(dirname($pathFile));
                    //$myTask->download($_SERVER["DOCUMENT_ROOT"] . "/a/");
                    $images[$_size]["compressed"] = filesize($pathFile);
                }
            }
            update_post_meta($imagesID, 'iloveimg_compress', $images);
            update_post_meta($imagesID, 'iloveimg_status_compress', 2); //status compressed
            return $images;

            //print_r($imagesID);
        } catch (Exception $e)  {
            return $images;
        }
        return false;
    }

}