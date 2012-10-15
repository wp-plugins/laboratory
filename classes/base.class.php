<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Base Class
 *
 * All functionality pertaining to both the administration and frontend sections of Laboratory.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Administration
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $name
 * var $token
 *
 * var $plugin_path
 * var $plugin_url
 *
 * var $assets_path
 * var $assets_url
 *
 * var $views_path
 * var $views_url
 *
 * var $components_path
 * var $components_url
 *
 * 
 * var $backups_path
 * var $backups_url
 *	
 * var $models_path
 * var $models_url
 *
 * - __construct()
 * - init_component_loaders()
 * - load_active_components()
 * - get_directory_by_type()
 */
class Laboratory_Base {
	var $name;
	var $token;
	
	var $plugin_path;
	var $plugin_url;
	
	var $assets_path;
	var $assets_url;
	
	var $views_path;
	var $views_url;
	
	var $components_path;
	var $components_url;

	var $backups_path;
	var $backups_url;
	
	var $models_path;
	var $models_url;
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function __construct() {
		/* Setup default name and token. */
		$this->name = __( 'Laboratory', 'laboratory' );
        $this->slug_name = 'laboratory'; 
		$this->token = 'laboratory';
		
		/* Setup plugin path and URL. */
		$this->plugin_path = trailingslashit( str_replace( '/classes', '', dirname( __FILE__ ) ) );
		$this->plugin_url = trailingslashit( str_replace( '/classes', '', plugins_url( plugin_basename( dirname( __FILE__ ) ) ) ) );

		/* Cater for Windows systems where / is not present. */
		$this->plugin_path = trailingslashit( str_replace( 'classes', '', $this->plugin_path ) );
		$this->plugin_url = trailingslashit( str_replace( 'classes', '', $this->plugin_url ) );
		
		/* Setup assets path and URL. */
		$this->assets_path = trailingslashit( $this->plugin_path . 'assets' );
		$this->assets_url = trailingslashit( $this->plugin_url . 'assets' );
		
		/* Setup views path and URL. */
		$this->views_path = trailingslashit( $this->plugin_path . 'views' );
		$this->views_url = trailingslashit( $this->plugin_url . 'views' );
		
		/* Setup addon components path and URL. */
		$this->components_path = trailingslashit( $this->plugin_path . 'addon' );
		$this->components_url = trailingslashit( $this->plugin_url . 'addon' );
		

		/* Setup component backups path and URL. */
		$this->backups_path = trailingslashit( $this->downloads_path . $this->token . '-backups' );
		$this->backups_url = trailingslashit( $this->downloads_url . $this->token . '-backups' );
		
		/* Setup models path and URL. */
		$this->models_path = trailingslashit( $this->plugin_path . 'models' );
		$this->models_url = trailingslashit( $this->plugin_url . 'models' );
		
		add_action( 'plugins_loaded', array( &$this, 'init_component_loaders' ) );
	} // End __construct()
	
	/**
	 * init_component_loaders function.
	 *
	 * @description Load active components.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_component_loaders () {
		$this->load_active_components( 'addon' );
	} // End init_component_loaders()
	
	/**
	 * load_active_components function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param $type
	 * @return void
	 */
	public function load_active_components ( $type = 'addon' ) {
		$components = get_option( $this->token . '_' . $type . '_active', array() );
		
		$path = $this->get_directory_by_type( $type );
		
		if ( is_array( $components ) && count( $components ) > 0 ) {
			do_action( $this->token . '_load_' . $type . '_components_before' ); // eg: laboratory_load_addon_components_before
			foreach ( $components as $k => $v ) {
				do_action( $this->token . '_load_' . $type . '_component_' . $k . '_before' ); // eg: laboratory_load_addon_component_laboratory-tabs_before
				if ( file_exists( $path . $v ) ) {
					require_once( $path . $v );
				}
				do_action( $this->token . '_load_' . $type . '_component_' . $k . '_after' ); // eg: laboratory_load_addon_component_laboratory-tabs_after
			}
			do_action( $this->token . '_load_' . $type . '_components_after' ); // eg: laboratory_load_addon_components_after
		}
	} // End load_active_components()
	
	/**
	 * get_directory_by_type function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $type (default: 'addon')
	 * @return string $path
	 */
	public function get_directory_by_type ( $type = 'addon' ) {
		$path = '';
		switch ( $type ) {
			
			case 'addon':
			default:
				$path = $this->components_path;
			break;
		}
		
		return $path;
	} // End get_directory_by_type()
}
?>