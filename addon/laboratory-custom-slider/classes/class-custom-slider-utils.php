<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider Utilities Class
 *
 * Common utility functions for CustomSlider.
 *
 * @package WordPress
 * @subpackage CustomSlider
 * @category Utilities
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - get_slider_types()
 * - get_posts_layout_types()
 * - get_supported_effects()
 */
class CustomSlider_Utils {
	/**
	 * Get an array of the supported slider types.
	 * @since  1.0.0
	 * @return array The slider types supported by CustomSlider.
	 */
	public static function get_slider_types () {
		return (array)apply_filters( 'laboratory_slideshow_slider_types', array(
																	'attachments' => array( 'name' => __( 'Attached Images', 'laboratory' ), 'callback' => 'method' ), 
																	'slides' => array( 'name' => __( 'Slides', 'laboratory' ), 'callback' => 'method' ), 
																	'posts' => array( 'name' => __( 'Posts', 'laboratory' ), 'callback' => 'method' )
																	)
									);
	} // End get_slider_types()

	/**
	 * Get an array of the supported posts layout types.
	 * @since  1.0.0
	 * @return array The posts layout types supported by CustomSlider.
	 */
	public static function get_posts_layout_types () {
		return (array)apply_filters( 'laboratory_slideshow_posts_layout_types', array(
																	'text-left' => array( 'name' => __( 'Text Left', 'laboratory' ), 'callback' => 'method' ), 
																	'text-right' => array( 'name' => __( 'Text Right', 'laboratory' ), 'callback' => 'method' ), 
																	'text-top' => array( 'name' => __( 'Text Top', 'laboratory' ), 'callback' => 'method' ), 
																	'text-bottom' => array( 'name' => __( 'Text Bottom', 'laboratory' ), 'callback' => 'method' )
																	)
									);
	} // End get_posts_layout_types()

	/**
	 * Return an array of supported slider effects.
	 * @since  1.0.0
	 * @uses  filter: 'laboratory_slideshow_supported_effects'
	 * @return array Supported effects.
	 */
	public static function get_supported_effects () {
		return (array)apply_filters( 'laboratory_slideshow_supported_effects', array( 'fade', 'slide' ) );
	} // End get_supported_effects()

	/**
	 * Get the placeholder thumbnail image.
	 * @since  1.0.0
	 * @return string The URL to the placeholder thumbnail image.
	 */
	public static function get_placeholder_image () {
		global $laboratory_slideshow;
		return esc_url( apply_filters( 'laboratory_slideshow_placeholder_thumbnail', $laboratory_slideshow->plugin_url . 'assets/images/placeholder.png' ) );
	} // End get_placeholder_image()
} // End Class
?>