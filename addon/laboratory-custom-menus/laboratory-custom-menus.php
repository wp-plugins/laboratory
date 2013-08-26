<?php
/**
 * Addon Name: Custom Menus
 * Addon Description: Dynamically select a custom menu for the available Menu Locations in your posts, pages and custom post types.
 * Addon Version: 1.0.0
 *
 * @package Laboratory
 * @subpackage Addon
 * @author ColorLabs
 * @since 1.0.0
*/

 /* Instantiate The Feature */
 if ( class_exists( 'Laboratory' ) ) {
 	require_once( 'classes/custom-menus.class.php' );
 	$laboratory_custom_menus = new Laboratory_Custom_Menu;
 }