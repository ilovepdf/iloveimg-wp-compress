<?php


class iLoveIMG_Watermark_Options extends AdminPageFramework_PageMetaBox {
        
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        
        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            
            array(    // Single text field
                'field_id'      => 'enable',
                'type'          => 'checkbox',
                'title'         => 'Enable Watermark',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'autowatermark',
                'type'          => 'checkbox',
                'title'         => 'Enable Auto Watermark',
                'tip'          => 'Activate this setting for Auto Watermark on new Images uploads.',
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'backup',
                'type'          => 'checkbox',
                'title'         => 'Backup original',
                'tip'          => 'Activate this setting for create a copy when watermark is applied',
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
 
new iLoveIMG_Watermark_Options(
    'iloveimg_watermark_options',                                           // meta box id - passing null will make it auto generate
    __( 'Configure your Watermark Images settings', 'iLoveIMG' ), // title
    array( 'iloveimg_watermark_options' ),
    'normal',                                         // context
    'default'                                       // priority
);


class iLoveIMG_Watermark_Format_Options extends AdminPageFramework_PageMetaBox {
        
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
        
        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            
            array(    // Single text field
                'field_id'      => 'vertical',
                'type'          => 'select',
                'title'         => 'Vertical position',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'label'         => array(
                    'top' => 'Top',
                    'middle' => 'Middle',
                    'bottom' => 'Bottom',
                ),
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'horizontal',
                'type'          => 'select',
                'title'         => 'Horizontal position',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'label'         => array(
                    'top' => 'Left',
                    'middle' => 'Center',
                    'bottom' => 'Right',
                ),
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'mode',
                'type'          => 'select',
                'title'         => 'Mode',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'label'         => array(
                    'text' => 'Text',
                    'image' => 'Image'
                ),
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'image',
                'type'          => 'image',
                'title'         => 'Image',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'attributes'      => array(
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_image'
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'opacity',
                'type'          => 'number',
                'title'         => 'Opacity',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'after_input' => ' From 0 to 100',
                'default'       => 0,
                'attributes'      => array(
                    'size' => 3,
                    'min'   => 0,
                    'max' => 100,
                    'maxlength' => 3,
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_image'
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'rotation',
                'type'          => 'number',
                'title'         => 'Rotation',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'after_input' => ' From 0 to 360 degrees',
                'default'       => 0,
                'attributes'      => array(
                    'size' => 3,
                    'min'   => 0,
                    'max' => 360,
                    'maxlength' => 3,
                    'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_image'
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'waterkmark_text',
                'type'          => 'text',
                'title'         => 'Text',
                'tip'          => 'Activate this setting for active/inactive Watermark on Images files.',
                'default'       => 'iLoveIMG',
                'attributes'      => array(
                     'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_text'
                    )
                ),
            ),
            array(    // Single text field
                'field_id'      => 'waterkmark_size',
                'type'          => 'number',
                'title'         => 'Text Size',
                'after_input' => ' Indicate text size in pixels. From 5 to 80.',
                'default'       => 18,
                'attributes'      => array(
                    'maxlength' => 2,
                    'size' => 2,
                    'min'   => 5,
                    'max' => 80,
                     'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_text'
                    )
                ),
            ),   
            array(    // Single text field
                'field_id'      => 'waterkmark_family',
                'type'          => 'select',
                'title'         => 'Text Font Family',
                'label'         => array(
                    'Verdana' => 'Verdana',
                    'Courier' => 'Courier',
                ),
                'attributes'      => array(
                     'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_text'
                    )
                ),
            ), 
            array(    // Single text field
                'field_id'      => 'waterkmark_color',
                'type'          => 'color',
                'title'         => 'Text Color',
                'default'       => '#000000',
                'attributes'      => array(
                        'fieldrow'  => array(
                        'style'     => 'display: flex;',
                        'class'     => 'field_text'
                    )
                ),
            ),
           
            array( // Submit button
                'field_id'      => 'submit_button',
                'type'          => 'submit',
                'value'         => 'Save Changes',
            )
        ); 
      
    }
   
}
 
new iLoveIMG_Watermark_Format_Options(
    null,                                           // meta box id - passing null will make it auto generate
    __( 'Configure your Watermark format', 'iLoveIMG' ), // title
    array( 'iloveimg_watermark_options' ),
    'normal',                                         // context
    'default'                                       // priority
);
