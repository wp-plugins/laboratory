<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider Frontend Class
 *
 * All functionality pertaining to the frontend of CustomSlider.
 *
 * @package WordPress
 * @subpackage CustomSlider
 * @category Frontend
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - __construct()
 * - init()
 * - trigger_javascript_generator()
 * - generate_slider_javascript()
 * - generate_single_slider_javascript()
 * - generate_slider_settings_javascript()
 * - enqueue_scripts()
 * - enqueue_styles()
 */
class CustomSlider_Frontend {
	public $token;

	/**
	 * Constructor.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct () {
		require_once( 'class-custom-slider-sliders.php' );
		$this->sliders = new CustomSlider_Sliders();
		$this->sliders->token = $this->token;
	} // End __construct()

	/**
	 * Initialise the code.
	 * @since  1.0.0
	 * @return void
	 */
	public function init () {
		add_action( 'template_redirect', array( &$this, 'trigger_javascript_generator' ) );
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
		add_action( 'wp_footer', array( &$this, 'enqueue_scripts' ) );
	} // End init()

	/**
	 * Trigger the function to generate the JavaScript code for each slider in use on the current screen.
	 * @since  1.0.0
	 * @return void
	 */
	public function trigger_javascript_generator () {
		if ( isset( $_GET[$this->token . '-javascript'] ) && ( $_GET[$this->token . '-javascript'] == 'load' ) ) {
			header( 'Content-Type:text/javascript' );
			
			$html = '';

			$data = get_transient( $this->token . '-slider-javascript' );

			if ( $data != '' && $data != false ) {
				$html = $data;

				delete_transient( $this->token . '-slider-javascript' );
			}

			echo $html;
			die();
		}
	} // End trigger_javascript_generator()

	/**
	 * Generate the JavaScript code for each slider in use on the current screen.
	 * @since  1.0.0
	 * @return void
	 */
	private function generate_slider_javascript () {
		$html = '';

		if ( is_array( $this->sliders->sliders ) && count( $this->sliders->sliders ) > 0 ) {
			$html .= 'jQuery(window).load(function() {' . "\n";
			foreach ( $this->sliders->sliders as $k => $v ) {
				if ( isset( $v['args']['id'] ) ) {
					$html .= $this->generate_single_slider_javascript( $v['args']['id'], $v['args'], $v['extra'] );
				}
			}
			$html .= "\n" . '});' . "\n";
		}

		set_transient( $this->token . '-slider-javascript', $html );
	} // End generate_slider_javascript()

	/**
	 * Generate the JavaScript for a specified slideshow.
	 * @uses generate_slider_settings_javascript()
	 * @since  1.0.0
	 * @param  int $id The ID of the slider for which to generate the JavaScript.
	 * @param  array $args Arguments to be used in the slider JavaScript.
	 * @param  array $extra Additional, contextual arguments to use when generating the slider JavaScript.
	 * @return string     The JavaScript code pertaining to the specified slider.
	 */
	private function generate_single_slider_javascript ( $id, $args, $extra = array() ) {
		$html = '';

		// Convert settings to a JavaScript-readable string.
		$args_output = $this->generate_slider_settings_javascript( $args, $extra );

		$html .= "\n" . 'jQuery( \'.laboratory_slideshow-id-' . esc_attr( intval( $id ) ) . '\' ).flexslider2(' . $args_output . ');' . "\n";

		return $html;
	} // End generate_single_slider_javascript()

	/**
	 * Generate a JavaScript-friendly string of an object containing the slider arguments.
	 * @since  1.0.0
	 * @param  array $args 	Arguments for this slideshow.
	 * @param  array $extra Additional, contextual arguments to use when generating the slider JavaScript.
	 * @return string       A JavaScript-friendly string of arguments.
	 */
	private function generate_slider_settings_javascript ( $args, $extra = array() ) {
		// Begin the arguments output
		$args_output = '{';

		$args_output .= 'namespace: "laboratory_slideshow-"' . "\n";

		// Animation
		if ( isset( $args['animation'] ) && in_array( $args['animation'], CustomSlider_Utils::get_supported_effects() ) ) {
			$args_output .= ', animation: \'' . $args['animation'] . '\'';
		}

		// Direction
		if ( ( $args['animation'] == 'slide' ) && isset( $args['direction'] ) && in_array( $args['direction'], array( 'horizontal', 'vertical' ) ) ) {
			$args_output .= ', direction: \'' . $args['direction'] . '\'';
		}

		// Slideshow Speed
		if ( isset( $args['slideshow_speed'] ) && is_numeric( $args['slideshow_speed'] ) && ( floatval( $args['slideshow_speed'] ) > 0 ) ) {
			$args_output .= ', slideshowSpeed: ' . ( $args['slideshow_speed'] ) * 1000;
		}

		// Animation Duration
		if ( isset( $args['animation_duration'] ) && is_numeric( $args['animation_duration'] ) && ( floatval( $args['animation_duration'] ) > 0 ) ) {
			$args_output .= ', animationSpeed: ' . ( $args['animation_duration'] ) * 1000;
		}

		// Checkboxes.
		$options = array(
						'autoslide' => 'slideshow', 
						'direction_nav' => 'directionNav', 
						'keyboard_nav' => 'keyboard', 
						'mousewheel_nav' => 'mousewheel', 
						'playpause' => 'pausePlay', 
						'randomize' => 'randomize', 
						'animation_loop' => 'animationLoop', 
						'pause_on_action' => 'pauseOnAction', 
						'pause_on_hover' => 'pauseOnHover', 
						'smoothheight' => 'smoothHeight'
						);

		if ( isset( $extra['thumbnails'] ) && ( $extra['thumbnails'] == 'true' || $extra['thumbnails'] == 1 ) ) {
			$args_output .= ', controlNav: "thumbnails"' . "\n";
		} else {
			$options['control_nav'] = 'controlNav';
		}

		// Process the checkboxes.
		foreach ( $options as $k => $v ) {
			$status = 'false';
			if ( isset( $args[$k] ) && ( ( $args[$k] == true ) || ( $args[$k] == 'true' && $args[$k] != 'false' ) ) ) {
				$status = 'true';
			}
			$args_output .= ', ' . esc_js( $v ) . ': ' . $status;
		}

		// Text fields.
		$options = array(
						'prev_text' => array( 'key' => 'prevText', 'default' => __( 'Previous', 'laboratory' ) ), 
						'next_text' => array( 'key' => 'nextText', 'default' => __( 'Next', 'laboratory' ) ), 
						'play_text' => array( 'key' => 'playText', 'default' => __( 'Play', 'laboratory' ) ), 
						'pause_text' => array( 'key' => 'pauseText', 'default' => __( 'Pause', 'laboratory' ) )
						);

		// Process the text fields.
		foreach ( $options as $k => $v ) {
			if ( isset( $args[$k] ) && ( $args[$k] != $v['default'] ) ) {
				$args_output .= ', ' . esc_js( $v['key'] ) . ': \'' . esc_js( $args[$k] ) . '\'';
			}
		}

		// End the arguments output
		$args_output .= '}';

		return $args_output;
	} // End generate_slider_settings_javascript()

	/**
	 * Enqueue frontend JavaScripts.
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_scripts () {
		global $laboratory_slideshow;

		$this->generate_slider_javascript();

		wp_register_script( $this->token . '-flexslider', esc_url( $laboratory_slideshow->plugin_url . 'assets/js/jquery.flexslider-min.js' ), array( 'jquery' ), '1.0.0', true );
		wp_register_script( $this->token . '-sliders', home_url( '/?' . $this->token . '-javascript=load&t=' . time() ), array( $this->token . '-flexslider' ), '1.0.0', true );

		wp_enqueue_script( $this->token . '-sliders' );
	} // End enqueue_scripts()

	/**
	 * Enqueue frontend CSS files.
	 * @since  1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		global $laboratory_slideshow;

		wp_register_style( $this->token . '-flexslider', esc_url( $laboratory_slideshow->plugin_url . 'assets/css/flexslider.css' ), '', '1.0.1', 'all' );
		wp_register_style( $this->token . '-common', esc_url( $laboratory_slideshow->plugin_url . 'assets/css/style.css' ), array( $this->token . '-flexslider' ), '1.0.1', 'all' );

		wp_enqueue_style( $this->token . '-common' );
	} // End enqueue_styles()
} // End Class
?>