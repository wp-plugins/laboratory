<?php
/**
 * Addon Name: Mailchimp Widgets
 * Addon Description: Integrated mailchimp with your site.
 * Addon Version: 1.0.0
 *
 * @package Laboratory
 * @subpackage Bundled
 * @author ColorLabs
 * @since 1.0.0
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

 /**
  * laboratory_mailchimpwidgets_register function.
  * 
  * @access public
  * @since 1.0.0
  * @return void
  */
  
 if ( ! function_exists( 'laboratory_mailchimpwidgets_register' ) ) {
 	 add_action( 'widgets_init', 'laboratory_mailchimpwidgets_register' );
 	 
	 function laboratory_mailchimpwidgets_register () {
	 	global $laboratory;
		
		include_once( untrailingslashit( dirname( __FILE__ ) ) . '/assets/mailchimp-api-class.php');

	 	$widgets = array(
	 					'Colabs_Widget_MailChimp' => 'widgets/widget-laboratory-mailchimp.php'
	 				);

	 	$widgets = apply_filters( 'laboratory_mailchimpwidgets_widgets', $widgets );
	 	
	 	if ( count( $widgets ) > 0 ) {
	 		foreach ( $widgets as $k => $v ) {
	 			if ( file_exists( $laboratory->base->components_path . 'laboratory-mailchimp-widgets/' . $v ) ) {
	 				require_once( $v );

	 				register_widget( $k );
	 			}
	 		}
	 	}
	 } // End laboratory_mailchimpwidgets_register()
 }
?>