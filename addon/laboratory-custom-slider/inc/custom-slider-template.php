<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'laboratory_slideshow' ) ) {
/**
 * CustomSlider template tag.
 * @since  1.0.0
 * @param  array   $args 	Optional array of arguments to customise this instance of the slider.
 * @param  boolean $echo 	Whether or not to echo the slider output (default: true)
 * @return string/void      Returns a string of $echo is false. Otherwise, returns void.
 */
function laboratory_slideshow ( $args = array(), $extra_args = array(), $echo = true ) {
	global $laboratory_slideshow;

	$defaults = $laboratory_slideshow->settings->get_settings();
	$defaults['slidetype'] = 'attachments';

	$settings = wp_parse_args( $args, $defaults );

	// Generate an ID for this slider.
	$settings['id'] = $laboratory_slideshow->slider_count++;

	$slides = $laboratory_slideshow->frontend->sliders->get_slides( $settings['slidetype'], $extra_args );

	$laboratory_slideshow->frontend->sliders->add( $slides, $settings, $extra_args );

	$html = '<div class="laboratory_slideshow laboratory_slideshow-id-' . esc_attr( $settings['id'] ) . ' laboratory_slideshow-type-' . esc_attr( $settings['slidetype'] ) . '"><ul class="slides">' . "\n";
	$html .= $laboratory_slideshow->frontend->sliders->render( $slides, $extra_args );
	$html .= '</ul></div>' . "\n";

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
} // End laboratory_slideshow()
}

if ( ! function_exists( 'laboratory_slideshow_shortcode' ) ) {
/**
 * CustomSlider shortcode wrapper.
 * @since  1.0.0
 * @param  array $atts    	Optional shortcode attributes, used to customise slider settings.
 * @param  string $content 	Content, if the shortcode supports wrapping of content.
 * @return string          	Rendered CustomSlider.
 */
function laboratory_slideshow_shortcode ( $atts, $content = null ) {
	global $laboratory_slideshow;
	$args = $laboratory_slideshow->settings->get_settings();
	$args['slidetype'] = 'attachments';
	$settings = shortcode_atts( $args, $atts );

	$extra_args = array();

	foreach ( (array)$atts as $k => $v ) {
		if ( ! in_array( $k, array_keys( $settings ) ) ) {
			$extra_args[$k] = $v;
		}
	}

	return laboratory_slideshow( $settings, $extra_args, false );
} // End laboratory_slideshow_shortcode()
}

add_shortcode( 'laboratory_slideshow', 'laboratory_slideshow_shortcode' );
?>