<?php
/**
 * Initializes the iLoveIMG library for WordPress integration.
 *
 * This class is responsible for initializing the iLoveIMG library, allowing it to be used within WordPress.
 * It includes the necessary iLoveIMG library files and sets up any required configurations.
 *
 * @since 1.0.0
 */
class Ilove_Img_Library_Init {

	/**
     * Constructs an instance of the Ilove_Img_Library_Init class.
     */
	public function __construct() {
		require_once dirname( __DIR__ ) . '/iloveimg-php/init.php';
	}
}
