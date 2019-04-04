<?php






class iLoveIMG_Signup extends AdminPageFramework_PageMetaBox {
        
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
    
        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            array(    // Single text field
                'field_id'      => 'name',
                'type'          => 'text',
                'title'         => 'Name',
            ),
            array(    // Single text field
                'field_id'      => 'email',
                'type'          => 'email',
                'title'         => 'Email',
            ),
            array(    // Single text field
                'field_id'      => 'password',
                'type'          => 'password',
                'title'         => 'Password',
            ),
            array(    // Single text field
                'field_id'      => 'confirm_password',
                'type'          => 'password',
                'title'         => 'Confirm Password',
            ),  
            array( // Submit button
                'field_id'      => 'submit_button',
                'type'          => 'submit',
                'value'         => 'Register & Generate keys'
            ) 
        ); 
      
    }
   
}
 
new iLoveIMG_Signup(
    null,                                           // meta box id - passing null will make it auto generate
    __( 'Register as iLovePDF developer', 'iLoveIMG' ), // title
    array( 'iloveimg_account' ),
    'normal',                                         // context
    'default'                                       // priority
);


class iLoveIMG_Login extends AdminPageFramework_PageMetaBox {
        
    /*
     * Use the setUp() method to define settings of this meta box.
     */
    public function setUp() {
    
        /**
         * Adds setting fields in the meta box.
         */
        $this->addSettingFields(
            array(    // Single text field
                'field_id'      => 'email',
                'type'          => 'email',
                'title'         => 'Email',
            ),
            array(    // Single text field
                'field_id'      => 'password',
                'type'          => 'password',
                'title'         => 'Password',
            ),
            array( // Submit button
                'field_id'      => 'submit_button',
                'type'          => 'submit',
                'value'         => 'Login'
            ) 
        ); 
      
    }
   
}
 
new iLoveIMG_Login(
    null,                                           // meta box id - passing null will make it auto generate
    __( 'Login', 'iLoveIMG' ), // title
    array( 'iloveimg_account' ),
    'side',                                         // context
    'default'                                       // priority
);