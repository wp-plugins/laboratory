<?php
/**
	* Addon Name: Custom Login
	* Addon Description: Automatically rebrands your Login screen with a custom logo.
	* Addon Version: 1.0.0
	* Addon Settings: laboratory-login-branding
	*
	* @package Laboratory
	* @subpackage Addon
	* @author Patrick
	* @since 1.0.0
*/

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
	
 /* Instantiate Login Branding */
 if ( class_exists( 'Laboratory' ) ) {
	/* Include Login Branding Class*/
 	require_once('classes/laboratory-login-branding.class.php');
 	$laboratory_login_branding = new Laboratory_Login_Branding();
 }