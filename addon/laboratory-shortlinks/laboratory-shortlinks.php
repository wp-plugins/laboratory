<?php
/**
 * Addon Name: ShortLinks
 * Addon Description: Automatically generates short URLs for your posts, using your URL shortening service of choice.
 * Addon Version: 1.0.0
 * Addon Settings: laboratory-shortlinks-settings
 *
 * @package Laboratory
 * @subpackage Addon
 * @author Patrick
 * @since 1.0.0
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
 
 /* Include Shortlinks Class*/
 require_once( 'classes/laboratory-shortlinks.class.php' );
 /* Instantiate Shortlinks */
 if ( class_exists( 'Laboratory' ) ) {
 	$laboratory_shortlinks = new Laboratory_ShortLinks();
 } // End IF Statement
 
?>