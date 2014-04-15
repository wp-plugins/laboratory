<?php
/**
 * Addon Name: Social Network Widgets
 * Addon Description: A collection of widgets to connect to your online social profiles.
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
  * laboratory_socialwidgets_register function.
  * 
  * @access public
  * @since 1.0.0
  * @return void
  */
  
 if ( ! function_exists( 'laboratory_socialwidgets_register' ) ) {
 	 add_action( 'widgets_init', 'laboratory_socialwidgets_register' );
 	 
	 function laboratory_socialwidgets_register () {
	 	global $laboratory;

	 	$widgets = array(
	 					'Laboratory_Widget_Tweets' => 'widgets/widget-laboratory-tweets.php', 
	 					'Laboratory_Widget_TwitterProfile' => 'widgets/widget-laboratory-twitter-profile.php', 
	 					'Laboratory_Widget_Instagram' => 'widgets/widget-laboratory-instagram.php', 
	 					'Laboratory_Widget_InstagramProfile' => 'widgets/widget-laboratory-instagram-profile.php',
						'Laboratory_Widget_Pinterest' => 'widgets/widget-laboratory-pinterest.php'
	 				);

	 	$widgets = apply_filters( 'laboratory_socialwidgets_widgets', $widgets );
	 	
	 	if ( count( $widgets ) > 0 ) {
	 		foreach ( $widgets as $k => $v ) {
	 			if ( file_exists( $laboratory->base->components_path . 'laboratory-social-widgets/' . $v ) ) {
	 				require_once( $v );

	 				register_widget( $k );
	 			}
	 		}
	 	}
	 } // End laboratory_socialwidgets_register()
 }
?>