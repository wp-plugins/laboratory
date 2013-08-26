<?php
/**
 * Addon Name: Tabs Widget
 * Addon Description: The popular Tabs widget, classically placed within your website's main widgetized area.
 * Addon Version: 1.0.0
 *
 * @package Laboratory
 * @subpackage Addon
 * @author Matty
 * @since 1.0.0
 */
 
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}
 
 require_once( 'classes/widget-laboratory-tabs.php' );

 /**
  * laboratory_tabs_register function.
  * 
  * @access public
  * @since 1.0.0
  * @return void
  */
 if ( ! function_exists( 'laboratory_tabs_register' ) ) {
 	 add_action( 'widgets_init', 'laboratory_tabs_register' );
 	 
	 function laboratory_tabs_register () {
	 	return register_widget( 'Laboratory_Widget_Tabs' );
	 } // End laboratory_tabs_register()
 }
?>