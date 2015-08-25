<?php
/**
 * Addon Name: Custom CSS/HTML
 * Addon Description: The Custom CSS/HTML feature adds the facility to easy add custom CSS code to your website, as well as custom HTML code in the <head> section or before the </body> tag.
 * Addon Version: 1.0.0
 * Addon Settings: laboratory-custom-code
 *
 * @package Laboratory
 * @subpackage Addon
 * @author ColorLabs
 * @since 1.0.0
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

 /* Include Class */
 require_once( 'classes/laboratory-custom-code.class.php' );
 /* Instantiate Class */
 if ( class_exists( 'Laboratory' ) ) {
 	$laboratory_custom_code = new Laboratory_CustomCode();
 } // End IF Statement
?>