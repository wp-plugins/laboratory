<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider "Posts" Widget Class
 *
 * Widget class for the "Posts" widget for CustomSlider.
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
class CustomSlider_Widget_Posts extends CustomSlider_Widget_Base {
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct () {
		/* Widget variable settings. */
		$this->slidetype = 'posts';
		$this->laboratory_widget_cssclass = 'widget_laboratory_slideshow_slideshow_posts';
		$this->laboratory_widget_description = __( 'A slideshow of posts on your site', 'laboratory' );
		$this->laboratory_widget_idbase = 'laboratory_slideshow_slideshow_posts';
		$this->laboratory_widget_title = __('Laboratory Posts Slide', 'laboratory' );

		$this->init();

		$this->defaults = array(
						'title' => __( 'Posts', 'laboratory' )
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

		$extra_args = array();

		// Tags.
		if ( isset( $instance['tag'] ) && is_array( $instance['tag'] ) ) {
			$count = 0;
			foreach ( $instance['tag'] as $k => $v ) {
				$count++;
				if ( $count > 1 ) {
					$extra_args['tag'] .= '+';
				}
				$extra_args['tag'] .= esc_attr( $v );
			}
			unset( $instance['tag'] );
		}

		// Categories.
		if ( isset( $instance['category'] ) && is_array( $instance['category'] ) ) {
			$count = 0;
			foreach ( $instance['category'] as $k => $v ) {
				$count++;
				if ( $count > 1 ) {
					$extra_args['category'] .= ',';
				}
				$extra_args['category'] .= esc_attr( $v );
			}
			unset( $instance['category'] );
		}

		foreach ( $instance as $k => $v ) {
			if ( ! in_array( $k, array_keys( $settings ) ) ) {
				$extra_args[$k] = esc_attr( $v );
			}
		}

		// Make sure the various settings are applied.
		foreach ( $settings as $k => $v ) {
			if ( isset( $instance[$k] ) ) {
				$settings[$k] = esc_attr( $instance[$k] );
			}
		}

		$html = laboratory_slideshow( $settings, $extra_args, false );

		return $html;
	} // End generate_slideshow()
} // End Class
?>