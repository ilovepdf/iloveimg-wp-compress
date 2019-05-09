<?php
use Iloveimg\CompressImageTask;

class iLoveIMG_Compress_Process{

    public $proyect_public = '';
    public $secret_key = '';

    public function compress($imagesID){
        global $_wp_additional_image_sizes, $wpdb;

        $images = array();
        try { 

            if(get_option('iloveimg_proyect')){
                $proyect = explode("#", get_option('iloveimg_proyect'));
                $this->proyect_public = $proyect[0];
                $this->secret_key = $proyect[1];
            }else if(get_option('iloveimg_account')){
                $account = json_decode(get_option('iloveimg_account'), true);
                $this->proyect_public = $account['projects'][0]['public_key'];
                $this->secret_key = $account['projects'][0]['secret_key'];
            }

            update_post_meta($imagesID, 'iloveimg_status_compress', 1); //status compressing

            $_sizes = get_intermediate_image_sizes();
            
            array_unshift($_sizes,  "full");
            $_aOptions = unserialize(get_option('iloveimg_options_compress'));
            

            foreach ( $_sizes as $_size ) {
                $image = wp_get_attachment_image_src($imagesID, $_size);
                $pathFile = $_SERVER["DOCUMENT_ROOT"] . str_replace(site_url(), "", $image[0]);
                $images[$_size] = array("initial" => filesize($pathFile),  "compressed" => null);
                if(in_array($_size, $_aOptions['iloveimg_field_sizes'])){
                    $myTask = new CompressImageTask($this->proyect_public, $this->secret_key);
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
            print_r($e);
            die();
            return $images;
        }
        return false;
    }

}