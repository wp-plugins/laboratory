<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Class
 *
 * The main Laboratory class.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Core
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $version
 * var $base
 * var $admin
 * var $frontend
 * var $settings
 *
 * - __construct()
 * - load_localisation()
 * - activation()
 * - register_plugin_version()
 */
class Laboratory {
	var $file;
	var $version;
	var $base;
	var $admin;
	var $frontend;
	var $settings;
	var $updater;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct( $file ) {
		$this->version = '';
		$this->file = $file;

		require_once( 'base.class.php' );
		require_once( 'utils.class.php' );
		require_once( 'settings-api.class.php' );
		
		$this->base = new Laboratory_Base();
		
		add_action( 'init', array( &$this, 'load_localisation' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( $file ), array( &$this, 'laboratory_action_links' ) );
		if ( is_admin() ) {
			require_once( 'admin.class.php' );
			$this->admin = new Laboratory_Admin();
		} else {
			require_once( 'frontend.class.php' );
			$this->frontend = new Laboratory_Frontend();
		}

		// Run this on activation.
		register_activation_hook( $file, array( &$this, 'activation' ) );
	} // End __construct()
	
	/**
	 * laboratory_action_links function.
	 * 
	 * @access public
	 * @since 1.0.4
	 * @return void
	 */
	public function laboratory_action_links( $links ) {

		$plugin_links = array(
			'<a href="http://colorlabsproject.com/documentation/laboratory/" target="_blank">' . __( 'Documentation' ) . '</a>'
		);

		return array_merge( $plugin_links, $links );
	}
	
	/**
	 * load_localisation function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_localisation () {
		$lang_dir = trailingslashit( str_replace( 'classes', 'lang', basename( dirname(__FILE__) ) ) );
		load_plugin_textdomain( 'laboratory', false, $lang_dir );
	} // End load_localisation()

	/**
	 * activation function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function activation () {
		$this->register_plugin_version();
	} // End activation()

	/**
	 * register_plugin_version function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function register_plugin_version () {
		if ( $this->version != '' ) {
			update_option( $this->base->token . '-version', $this->version );
		}
	} // End register_plugin_version()
}
?>