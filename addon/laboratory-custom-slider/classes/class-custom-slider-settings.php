<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider Settings Class
 *
 * All functionality pertaining to the settings in CustomSlider.
 *
 * @package WordPress
 * @subpackage CustomSlider
 * @category Core
 * @author ColorLabs
 * @since 1.0.0
 * 
 * TABLE OF CONTENTS
 *
 * - __construct()
 * - init_sections()
 * - init_fields()
 * - get_duration_options()
 */
class CustomSlider_Settings extends CustomSlider_Settings_API {
//class CustomSlider_Settings extends Laboratory_Settings_API {	
    
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct () {
	    parent::__construct(); // Required in extended classes.
        if ( isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_slug ) ) {
	       add_action( 'admin_head', array( $this, 'add_contextual_help' ) );
        }  
	} // End __construct()
	
	/**
	 * register_settings_screen function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings_screen () {
		global $laboratory_slideshow;

		$hook = add_submenu_page( 'laboratory', $this->name, $this->menu_label, 'manage_options', $this->page_slug, array( &$this, 'settings_screen' ) );
		
		$this->hook = $hook;

		if ( isset( $_GET['page'] ) && ( $_GET['page'] == $this->page_slug ) ) {
			add_action( 'admin_notices', array( &$this, 'settings_errors' ) );
			add_action( 'admin_print_scripts', array( &$this, 'enqueue_scripts' ) );
			add_action( 'admin_print_styles', array( &$this, 'enqueue_styles' ) );
		}
	} // End register_settings_screen()

	/**
	 * init_sections function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_sections () {
	
		$sections = array();

		$sections['default-settings'] = array(
					'name' 			=> __( 'General Settings', 'laboratory' ), 
					'description'	=> __( 'Settings to apply to all slideshows, unless overridden.', 'laboratory' )
				);

		$sections['control-settings'] = array(
					'name' 			=> __( 'Control Settings', 'laboratory' ), 
					'description'	=> __( 'Customise the ways in which slideshows can be controlled.', 'laboratory' )
				);

		$sections['button-settings'] = array(
					'name' 			=> __( 'Button Settings', 'laboratory' ), 
					'description'	=> __( 'Customise the texts of the various slideshow buttons.', 'laboratory' )
				);
		
		$this->sections = $sections;
		
	} // End init_sections()
	
	/**
	 * init_fields function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @uses  CustomSlider_Utils::get_slider_types()
	 * @return void
	 */
	public function init_fields () {
		global $pagenow;

	    $fields = array();

    	$fields['animation'] = array(
								'name' => __( 'Animation', 'laboratory' ), 
								'description' => __( 'The slider animation', 'laboratory' ), 
								'type' => 'select', 
								'default' => 'fade', 
								'section' => 'default-settings', 
								'required' => 0, 
								'options' => array( 'fade' => __( 'Fade', 'laboratory' ), 'slide' => __( 'Slide', 'laboratory' ) )
								);

    	$fields['direction'] = array(
								'name' => __( 'Slide Direction', 'laboratory' ), 
								'description' => __( 'The direction to slide (if using the "Slide" animation)', 'laboratory' ), 
								'type' => 'select', 
								'default' => 'horizontal', 
								'section' => 'default-settings', 
								'required' => 0, 
								'options' => array( 'horizontal' => __( 'Horizontal', 'laboratory' ), 'vertical' => __( 'Vertical', 'laboratory' ) )
								);

    	$fields['slideshow_speed'] = array(
								'name' => __( 'Slideshow Speed', 'laboratory' ), 
								'description' => __( 'Set the delay between each slide animation (in seconds)', 'laboratory' ), 
								'type' => 'range', 
								'default' => '7.0', 
								'section' => 'default-settings', 
								'required' => 0, 
								'options' => $this->get_duration_options( false )
								);

    	$fields['animation_duration'] = array(
								'name' => __( 'Animation Speed', 'laboratory' ), 
								'description' => __( 'Set the duration of each slide animation (in seconds)', 'laboratory' ), 
								'type' => 'range', 
								'default' => '0.6', 
								'section' => 'default-settings', 
								'required' => 0, 
								'options' => $this->get_duration_options()
								);

    	// Button Settings
    	$fields['prev_text'] = array(
								'name' => __( '"Previous" Link Text', 'laboratory' ), 
								'description' => __( 'The text to display on the "Previous" button.', 'laboratory' ), 
								'type' => 'text', 
								'default' => __( 'Previous', 'laboratory' ), 
								'section' => 'button-settings'
								);

    	$fields['next_text'] = array(
								'name' => __( '"Next" Link Text', 'laboratory' ), 
								'description' => __( 'The text to display on the "Next" button.', 'laboratory' ), 
								'type' => 'text', 
								'default' => __( 'Next', 'laboratory' ), 
								'section' => 'button-settings'
								);

    	$fields['play_text'] = array(
								'name' => __( '"Play" Button Text', 'laboratory' ), 
								'description' => __( 'The text to display on the "Play" button.', 'laboratory' ), 
								'type' => 'text', 
								'default' => __( 'Play', 'laboratory' ), 
								'section' => 'button-settings'
								);

    	$fields['pause_text'] = array(
								'name' => __( '"Pause" Button Text', 'laboratory' ), 
								'description' => __( 'The text to display on the "Pause" button.', 'laboratory' ), 
								'type' => 'text', 
								'default' => __( 'Pause', 'laboratory' ), 
								'section' => 'button-settings'
								);

    	// Control Settings
    	$fields['autoslide'] = array(
								'name' => '', 
								'description' => __( 'Animate the slideshows automatically', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => true, 
								'section' => 'control-settings'
								);

    	$fields['smoothheight'] = array(
								'name' => '', 
								'description' => __( 'Adjust the height of the slideshow to the height of the current slide', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => false, 
								'section' => 'control-settings'
								);

    	$fields['direction_nav'] = array(
								'name' => '', 
								'description' => __( 'Display the "Previous/Next" navigation', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => true, 
								'section' => 'control-settings'
								);

    	$fields['control_nav'] = array(
								'name' => '', 
								'description' => __( 'Display the slideshow pagination', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => true, 
								'section' => 'control-settings'
								);

    	$fields['keyboard_nav'] = array(
								'name' => '', 
								'description' => __( 'Enable keyboard navigation', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => false, 
								'section' => 'control-settings'
								);

    	$fields['mousewheel_nav'] = array(
								'name' => '', 
								'description' => __( 'Enable the mousewheel navigation', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => false, 
								'section' => 'control-settings'
								);

    	$fields['playpause'] = array(
								'name' => '', 
								'description' => __( 'Enable the "Play/Pause" event', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => false, 
								'section' => 'control-settings'
								);

    	$fields['randomize'] = array(
								'name' => '', 
								'description' => __( 'Randomize the order of slides in slideshows', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => false, 
								'section' => 'control-settings'
								);

    	$fields['animation_loop'] = array(
								'name' => '', 
								'description' => __( 'Loop the slideshow animations', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => true, 
								'section' => 'control-settings'
								);

    	$fields['pause_on_action'] = array(
								'name' => '', 
								'description' => __( 'Pause the slideshow autoplay when using the pagination or "Previous/Next" navigation', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => true, 
								'section' => 'control-settings'
								);

    	$fields['pause_on_hover'] = array(
								'name' => '', 
								'description' => __( 'Pause the slideshow autoplay when hovering over a slide', 'laboratory' ), 
								'type' => 'checkbox', 
								'default' => false, 
								'section' => 'control-settings'
								);
		
		$this->fields = $fields;
	
	} // End init_fields()

	/**
	 * Get options for the duration fields.
	 * @since  1.0.0
	 * @param  $include_milliseconds (default: true) Whether or not to include milliseconds between 0 and 1.
	 * @return array Options between 0.1 and 10 seconds.
	 */
	private function get_duration_options ( $include_milliseconds = true ) {
		$numbers = array( '1.0', '1.5', '2.0', '2.5', '3.0', '3.5', '4.0', '4.5', '5.0', '5.5', '6.0', '6.5', '7.0', '7.5', '8.0', '8.5', '9.0', '9.5', '10.0' );
		$options = array();

		if ( true == (bool)$include_milliseconds ) {
			$milliseconds = array( '0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9' );
			foreach ( $milliseconds as $k => $v ) {
				$options[$v] = $v;
			}
		} else {
			$options['0.5'] = '0.5';
		}

		foreach ( $numbers as $k => $v ) {
			$options[$v] = $v;
		}

		return $options;
	} // End get_duration_options()

	/**
	 * Add contextual help to the settings screen.
	 * @access public
	 * @since   1.0.0
	 * @return   void
	 */
	public function add_contextual_help () {
		get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __( 'Overview', 'laboratory' ),
		'content'	=>
			'<p>' . __( 'This screen contains all the default settings for your slideshows created by CustomSlider (animation duration, speeds, display of slideshow controls, etc). Anything set here will apply to all CustomSlider slideshows, unless overridden by a slideshow.', 'laboratory' ) . '</p>'
		) );
		
		get_current_screen()->add_help_tab( array(
		'id'		=> 'general-settings',
		'title'		=> __( 'General Settings', 'laboratory' ),
		'content'	=>
			'<p>' . __( 'Settings to apply to all slideshows, unless overridden.', 'laboratory' ) . '</p>' . 
			'<ol>' . 
			'<li><strong>' . __( 'Animation', 'laboratory' ) . '</strong> - ' . __( 'The default animation to use for your slideshows ("slide" or "fade").', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Slide Direction', 'laboratory' ) . '</strong> - ' . __( 'Slide the slideshows either vertically or horizontally (works only with the "slide" animation).', 'laboratory' ) . ' <em>' . __( 'NOTE: When sliding vertically, all slides need to have the same height.', 'laboratory' ) . '</em></li>' .
			'<li><strong>' . __( 'Slideshow Speed', 'laboratory' ) . '</strong> - ' . __( 'The delay between each slide animation (in seconds).', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Animation Speed', 'laboratory' ) . '</strong> - ' . __( 'The duration of each slide animation (in seconds).', 'laboratory' ) . '</li>' . 
			'</ol>'
		) );

		get_current_screen()->add_help_tab( array(
		'id'		=> 'control-settings',
		'title'		=> __( 'Control Settings', 'laboratory' ),
		'content'	=>
			'<p>' . __( 'Customise the ways in which slideshows can be controlled.', 'laboratory' ) . '</p>' . 
			'<ol>' . 
			'<li><strong>' . __( 'Animate the slideshows automatically', 'laboratory' ) . '</strong> - ' . __( 'Whether or not to automatically animate between the slides (the alternative is to slide only when using the controls).', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Adjust the height of the slideshow to the height of the current slide', 'laboratory' ) . '</strong> - ' . __( 'Alternatively, the slideshow will take the height from it\'s tallest slide.', 'laboratory' ) . '</li>' .
			'<li><strong>' . __( 'Display the "Previous/Next" navigation', 'laboratory' ) . '</strong> - ' . __( 'Show/hide the "Previous" and "Next" button controls.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Display the slideshow pagination', 'laboratory' ) . '</strong> - ' . __( 'Show/hide the pagination bar below the slideshow.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Enable keyboard navigation', 'laboratory' ) . '</strong> - ' . __( 'Enable navigation of this slideshow via the "left" and "right" arrow keys on the viewer\'s computer keyboard.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Enable the mousewheel navigation', 'laboratory' ) . '</strong> - ' . __( 'Enable navigation of this slideshow via the viewer\'s computer mousewheel.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Enable the "Play/Pause" event', 'laboratory' ) . '</strong> - ' . __( 'Show/hide the "Play/Pause" button below the slideshow for pausing and resuming the automated slideshow.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Randomize the order of slides in slideshows', 'laboratory' ) . '</strong> - ' . __( 'Display the slides in the slideshow in a random order.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Loop the slideshow animations', 'laboratory' ) . '</strong> - ' . __( 'When arriving at the end of the slideshow, carry on sliding from the first slide, indefinitely.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Pause the slideshow autoplay when using the pagination or "Previous/Next" navigation', 'laboratory' ) . '</strong> - ' . __( 'Pause the slideshow automation when the viewer decides to navigate using the manual controls.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( 'Pause the slideshow autoplay when hovering over a slide', 'laboratory' ) . '</strong> - ' . __( 'Pause the slideshow automation when the viewer hovers over the slideshow.', 'laboratory' ) . '</li>' .
			'</ol>'
		) );

		get_current_screen()->add_help_tab( array(
		'id'		=> 'button-settings',
		'title'		=> __( 'Button Settings', 'laboratory' ),
		'content'	=>
			'<p>' . __( 'Customise the texts of the various slideshow buttons.', 'laboratory' ) . '</p>' . 
			'<ol>' . 
			'<li><strong>' . __( '"Previous" Link Text', 'laboratory' ) . '</strong> - ' . __( 'The text for the "Previous" button.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( '"Next" Link Text', 'laboratory' ) . '</strong> - ' . __( 'The text for the "Next" button.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( '"Play" Button Text', 'laboratory' ) . '</strong> - ' . __( 'The text for the "Play" button.', 'laboratory' ) . '</li>' . 
			'<li><strong>' . __( '"Pause" Button Text', 'laboratory' ) . '</strong> - ' . __( 'The text for the "Pause" button.', 'laboratory' ) . '</li>' . 
			'</ol>'
		) );

		get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'laboratory' ) . '</strong></p>' .
		'<p><a href="http://colorlabsproject.com/resolve" target="_blank">' . __( 'Support Desk', 'laboratory' ) . '</a></p>'
		);
	} // End add_contextual_help()
} // End Class
?>