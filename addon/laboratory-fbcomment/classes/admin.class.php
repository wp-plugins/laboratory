<?php

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory - Custom Login Logo
 *
 * Base class for the Laboratory Login Branding feature.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Addon
 * @author ColorLabs
 * @since 1.0.1
 *
 * TABLE OF CONTENTS
 *
 * var $token
 * var $settings_screen
 * var $settings;
 * 
 * - __construct()
 * - load_settings_screen()
 */
class Laboratory_FBComments extends Laboratory_Settings_API {

	/* Variable Declarations */
	var $token;
	var $settings_screen;
	var $settings;
    
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct () {

	    /* Settings Screen */
	    $this->load_settings_screen();
        
	} // End __construct()

	/**
	 * load_settings_screen function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_settings_screen () {
		/* Settings Screen */
		require_once( 'class-fbcomments-settings.php' );
		$this->settings = new Laboratory_FBComments_Settings();
		
		/* Setup login branding data */
		$this->settings->token = 'laboratory-fbcomments';
		if ( is_admin() ) {
			$this->settings->name = __( 'Facebook Comments', 'laboratory' );
			$this->settings->menu_label = __( 'Facebook Comments', 'laboratory' );
			$this->settings->page_slug = 'laboratory-fbcomments';
		}
		$this->settings->setup_settings();

	} // End load_settings_screen()

}

?>