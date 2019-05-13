<?php
use Iloveimg\CompressImageTask;
use Iloveimg\ResizeImageTask;

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

            


            
            $filesProcessing = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->postmeta WHERE meta_key = 'iloveimg_status_compress' AND meta_value = 1" );
            if( $filesProcessing <  ILOVEIMG_NUM_MAX_FILES){
                update_post_meta($imagesID, 'iloveimg_status_compress', 1); //status compressing

                $_sizes = get_intermediate_image_sizes();
                
                array_unshift($_sizes,  "full");
                $_aOptions = unserialize(get_option('iloveimg_options_compress'));
                

                foreach ( $_sizes as $_size ) {
                    $image = wp_get_attachment_image_src($imagesID, $_size);
                    $pathFile = $_SERVER["DOCUMENT_ROOT"] . str_replace(site_url(), "", $image[0]);
                    $images[$_size] = array("initial" => filesize($pathFile),  "compressed" => null);
                    if(in_array($_size, $_aOptions['iloveimg_field_sizes'])){
                        if($_size == 'full'){
                            if($_aOptions['iloveimg_field_resize_full'] == 'on'){
                                $editor = wp_get_image_editor( $pathFile );
                                if ( ! is_wp_error( $editor ) ) {
                                    $editor->resize( $_aOptions['iloveimg_field_size_full_width'], $_aOptions['iloveimg_field_size_full_height'], false );
                                    $editor->save( $pathFile );
                                }
                                $resize = $editor->get_size();
                                $metadata = wp_get_attachment_metadata($imagesID);
                                $metadata['width'] = $resize['width'];
                                $metadata['height'] = $resize['height'];
                                wp_update_attachment_metadata($imagesID, $metadata);
                            }
                        }
                        $myTask = new CompressImageTask($this->proyect_public, $this->secret_key);
                        $file = $myTask->addFile($pathFile);
                        $myTask->execute();
                        $myTask->download(dirname($pathFile));
                        $images[$_size]["compressed"] = filesize($pathFile);

                        
                    }
                }
                update_post_meta($imagesID, 'iloveimg_compress', $images);
                update_post_meta($imagesID, 'iloveimg_status_compress', 2); //status compressed
                return $images;

            }else{
                update_post_meta($imagesID, 'iloveimg_status_compress', 3); //status queue
                sleep(2);
                return $this->compress($imagesID);
            }

            //print_r($imagesID);
        } catch (Exception $e)  {
            update_post_meta($imagesID, 'iloveimg_status_compress', 0);
            //echo $e->getCode();
            return false;
        }
        return false;
    }

}