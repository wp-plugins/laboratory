<?php
/**
 * Addon Name: Custom Slider
 * Addon Description: Add responsive sliders to your website using shortcodes from add media wordpress feature, template tags or widgets, to showcase custom slides, blog posts or other content in a responsive animated slider.
 * Addon Version: 1.0.0
 * Addon Settings: laboratory-custom-slider
 *
 * @package Laboratory
 * @subpackage Addon
 * @author ColorLabs
 * @since 1.0.0
*/

 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    
 /* Instantiate The Feature */
 if ( class_exists( 'Laboratory' ) ) {
	require_once( 'classes/class-custom-slider.php' );
	if ( ! is_admin() ) require_once( 'inc/custom-slider-template.php' );

	global $laboratory_slideshow;
	$laboratory_slideshow = new CustomSlider( __FILE__ );
	$laboratory_slideshow->version = '1.0.0';
 }
?>