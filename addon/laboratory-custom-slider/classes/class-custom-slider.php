<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider Class
 *
 * Base class for CustomSlider.
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
 * - register_widgets()
 * - load_localisation()
 * - load_plugin_textdomain()
 * - activation()
 * - register_plugin_version()
 * - ensure_post_thumbnails_support()
 */
class CustomSlider {
	public $updater;
	public $admin;
	public $frontend;
	public $post_types;
	public $token = 'laboratory_slideshow';
	public $plugin_url;
	public $plugin_path;
	public $slider_count = 1;
	public $version;
	private $file;

	/**
	 * Constructor.
	 * @param string $file The base file of the plugin.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct ( $file ) {
		$this->file = $file;
		$this->plugin_url = trailingslashit( plugins_url( '', $plugin = $file ) );
		$this->plugin_path = trailingslashit( dirname( $file ) );

		$this->load_plugin_textdomain();
		add_action( 'init', array( &$this, 'load_localisation' ), 0 );

		// Run this on activation.
		register_activation_hook( $this->file, array( &$this, 'activation' ) );

		// Load the Utils class.
		require_once( 'class-custom-slider-utils.php' );

		// Setup post types.
		require_once( 'class-custom-slider-posttypes.php' );
		$this->post_types = new CustomSlider_PostTypes();

		// Setup settings screen.
		require_once( 'class-custom-slider-settings-api.php' );
		require_once( 'class-custom-slider-settings.php' );
		$this->settings = new CustomSlider_Settings();
		$this->settings->token = 'laboratory_slideshow-settings';
		if ( is_admin() ) {
			$this->settings->has_tabs 	= true;
			$this->settings->name 		= __( 'Custom Slider', 'laboratory' );
			$this->settings->menu_label	= __( 'Custom Slider', 'laboratory' );
			$this->settings->page_slug	= 'laboratory-custom-slider';
		}

		$this->settings->setup_settings();
		
		// Differentiate between administration and frontend logic.
		if ( is_admin() ) {
			require_once( 'class-custom-slider-admin.php' );
			$this->admin = new CustomSlider_Admin();
			$this->admin->token = $this->token;

		} else {
			require_once( 'class-custom-slider-frontend.php' );
			$this->frontend = new CustomSlider_Frontend();
			$this->frontend->token = $this->token;
			$this->frontend->init();
		}

		add_action( 'widgets_init', array( &$this, 'register_widgets' ) );
		add_action( 'after_setup_theme', array( &$this, 'ensure_post_thumbnails_support' ) );
	} // End __construct()

	/**
	 * Register the widgets.
	 * @return [type] [description]
	 */
	public function register_widgets () {
		require_once( $this->plugin_path . 'widgets/widget-custom-slider-base.php' );
		require_once( $this->plugin_path . 'widgets/widget-custom-slider-attachments.php' );
		require_once( $this->plugin_path . 'widgets/widget-custom-slider-posts.php' );
		require_once( $this->plugin_path . 'widgets/widget-custom-slider-slides.php' );

		register_widget( 'CustomSlider_Widget_Attachments' );
		register_widget( 'CustomSlider_Widget_Posts' );
		register_widget( 'CustomSlider_Widget_Slides' );
	} // End register_widgets()

	/**
	 * Load the plugin's localisation file.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'laboratory', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation()

	/**
	 * Load the plugin textdomain from the main WordPress "languages" folder.
	 * @since  1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'laboratory';
	    // The "plugin_locale" filter is also used in load_plugin_textdomain()
	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );
	 
	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain()

	/**
	 * Run on activation.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * Register the plugin's version.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	private function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( 'laboratory' . '-version', $this->version );
		}
	} // End register_plugin_version()

	/**
	 * Ensure that "post-thumbnails" support is available for those themes that don't register it.
	 * @since  1.0.1
	 * @return  void
	 */
	public function ensure_post_thumbnails_support () {
		if ( ! current_theme_supports( 'post-thumbnails' ) ) { add_theme_support( 'post-thumbnails' ); }
	} // End ensure_post_thumbnails_support()
} // End Class
?>