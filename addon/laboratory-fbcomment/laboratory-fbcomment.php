<?php
/**
	* Addon Name: Facebook Comments
	* Addon Description: Easily integrate facebook comments into your posts and pages.
	* Addon Version: 1.0.0
	* Addon Settings: laboratory-fbcomments
	*
	* @package Laboratory
	* @subpackage Addon
	* @author ColorLabs
	* @since 1.0.1
*/

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

 /* Instantiate FB Comments */
 if ( class_exists( 'Laboratory' ) ) {
	/* Include FB Comments Class*/
 	require_once('classes/admin.class.php');
	global $laboratory_fbcomments;
 	$laboratory_fbcomments = new Laboratory_FBComments( __FILE__ );    
	$laboratory_fbcomments->version = '1.0.0';
    
		// Differentiate between administration and frontend logic.
		if ( !is_admin() ){
			require_once( 'classes/class-fbcomments-frontend.php' );
			$this->frontend = new FBComments_Frontend();
			$this->frontend->token = $this->token;
			$this->frontend->init();
		}    
 }

?>