<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory - ShortLinks Settings
 *
 * Settings for the Laboratory - ShortLinks feature.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Addon
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 * 
 * - __construct()
 * - init_sections()
 * - init_fields()
 */
class Laboratory_ShortLinks_Settings extends Laboratory_Settings_API {
	
	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function __construct () {
	    parent::__construct(); // Required in extended classes.
	} // End __construct()

	/**
	 * init_sections function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_sections () {
	    $sections = array();
	    
	    $sections['main'] = array(
	    						'name' => __( 'Main Settings', 'laboratory' ), 
	    						'description' => __( 'Main settings and configuration', 'laboratory' )
	    						);
	    $sections['bitly-setup'] = array(
	    						'name' => __( 'Bitly Setup', 'laboratory' ), 
	    						'description' => __( 'Settings and configuration for Bitly', 'laboratory' )
	    						);
	    
	    $this->sections = $sections;
	} // End init_sections()
	
	/**
	 * init_fields function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init_fields () {
	    $fields = array();
	    
	    /* Setup default services list and make it filterable */
	    $service_list = array( 'native' => 'Native', 'bitly' => 'Bitly', 'tinyurl' => 'TinyURL' );
	    $service_list = apply_filters( 'laboratory_shortlinks_service_list', $service_list );
	    
	    $fields['service'] = array(
	    						'name' => __( 'Service Select', 'laboratory' ), 
	    						'description' => __( 'Select the service to use for generating a short url.', 'laboratory' ), 
	    						'type' => 'select', 
	    						'default' => 'native', 
	    						'section' => 'main', 
	    						'required' => 1, 
	    						'options' => $service_list
	    						);
	    
	    $fields['bitly_login'] = array(
	    						'name' => __( 'Bitly Username', 'laboratory' ), 
	    						'description' => __( 'Enter your Bitly username.', 'laboratory' ), 
	    						'type' => 'text', 
	    						'default' => '', 
	    						'section' => 'bitly-setup', 
	    						'required' => 0
	    						);
	    
	    $fields['bitly_api_key'] = array(
	    						'name' => __( 'Bitly API Key', 'laboratory' ), 
	    						'description' => __( 'Enter your Bitly API Key.', 'laboratory' ), 
	    						'type' => 'text', 
	    						'default' => '', 
	    						'section' => 'bitly-setup', 
	    						'required' => 0
	    						);
	    
	    $this->fields = $fields;
	} // End init_fields()
} // End Class Laboratory_ShortLinks_Settings
?>