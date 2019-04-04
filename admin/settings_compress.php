<?php






class iLoveIMG_Compress_Options extends AdminPageFramework_PageMetaBox {
        
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        
        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            /*array(    // Single text field
                'field_id'      => 'enable',
                'type'          => 'checkbox',
                'title'         => 'Enable Compress Images',
            ),*/
            array(    // Single text field
                'field_id'      => 'autocompress',
                'type'          => 'checkbox',
                'title'         => 'Enable Autocompress Images',
                'tip'          => 'Activate this setting for active/inactive Compress on Images files.',
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            /*array(    // Single text field
                'field_id'      => 'preserve_original',
                'type'          => 'checkbox',
                'title'         => 'Preserve creation date and time in the original image',
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),*/
            array(    // Single text field
                'field_id'      => 'image_sizes',
                'type'          => 'block_mixed',
                'title'         => 'Images Sizes',
                'tip'          => '<strong>Select image sizes to be compressed</strong><br/>Wordpress generates resized versions of every image. Choose which sizes to compress.',
                'content'       => iLoveIMG_Resources::getTypeImages(),
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            array( // Submit button
                'field_id'      => 'submit_button',
                'type'          => 'submit',
                'value'         => 'Save Changes'
            )
        ); 
      
    }
   
}
 
new iLoveIMG_Compress_Options(
    'iloveimg_compress_options',                                           // meta box id - passing null will make it auto generate
    __( 'Configure your Compress Images settings', 'iLoveIMG' ), // title
    array( 'iloveimg_compress_options' ),
    'normal',                                         // context
    'default'                                       // priority
);


