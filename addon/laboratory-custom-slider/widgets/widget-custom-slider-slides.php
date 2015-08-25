<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider "Slides" Widget Class
 *
 * Widget class for the "Slides" widget for CustomSlider.
 *
 * @package WordPress
 * @subpackage CustomSlider
 * @category Widgets
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - __construct()
 * - generate_slideshow()
 */
class CustomSlider_Widget_Slides extends CustomSlider_Widget_Base {
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct () {
		/* Widget variable settings. */
		$this->slidetype = 'slides';
		$this->laboratory_widget_cssclass = 'widget_laboratory_slideshow_slideshow_slides';
		$this->laboratory_widget_description = __( 'A slideshow of slides on your site', 'laboratory' );
		$this->laboratory_widget_idbase = 'laboratory_slideshow_slideshow_slides';
		$this->laboratory_widget_title = __('Laboratory Slides Slideshow', 'laboratory' );
    
    $this->init();

		$this->defaults = array(
						'title' => ''
					);
	} // End Constructor

	/**
	 * Generate the HTML for this slideshow.
	 * @since  1.0.0
	 * @return string The generated HTML.
	 */
	protected function generate_slideshow ( $instance ) {
		global $laboratory_slideshow;
		$settings = $laboratory_slideshow->settings->get_settings();
		$settings['slidetype'] = $this->slidetype;

		// Slide Pages.
		if ( isset( $instance['slide_page'] ) && is_array( $instance['slide_page'] ) ) {
			$count = 0;
			foreach ( $instance['slide_page'] as $k => $v ) {
				$count++;
				if ( $count > 1 ) {
					$settings['slide_page'] .= ',';
				}
				$settings['slide_page'] .= esc_attr( $v );
			}
			unset( $instance['slide_page'] );
		}

		$extra_args = array();

		foreach ( $instance as $k => $v ) {
			if ( ! in_array( $k, array_keys( $settings ) ) ) {
				$extra_args[$k] = esc_attr( $v );
			}
		}

		// Make sure the various settings are applied.
		foreach ( $settings as $k => $v ) {
			if ( isset( $instance[$k] ) && ( $instance[$k] != $settings[$k] ) ) {
				$settings[$k] = esc_attr( $instance[$k] );
			}
		}

		$html = laboratory_slideshow( $settings, $extra_args, false );

		return $html;
	} // End generate_slideshow()
} // End Class
?>