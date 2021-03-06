<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory - Login Branding Settings
 *
 * Settings for the Laboratory - Login Branding feature.
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
class Laboratory_LoginBranding_Settings extends Laboratory_Settings_API {
	
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
			
		$sections['general'] = array(
		    'name' 			=> __('General Settings', 'laboratory' ), 
		    'description'	=> __('General login branding settings. If you do not wish to use a specific option, leave the setting blank to disable.', 'laboratory')
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
		
		$fields['logo_url'] = array(
		    'name' => __('Logo URL', 'laboratory' ), 
		    'description' => __('Change the logo image for the WordPress login page. This is the URL of your logo.', 'laboratory'), 
		    'type' => 'text', 
		    'default' => plugin_dir_url(plugin_basename(dirname(__FILE__))).'assets/colorlabs.png', 
		    'section' => 'general', 
		    'validate' => 'validate_url'
		);
		
		$fields['title_text'] = array(
		    'name' => __('Title Text', 'laboratory' ), 
		    'description' => __('Change the title of the logo image on the WordPress login page.', 'laboratory'), 
		    'type' => 'text', 
		    'default' => get_bloginfo('name').' &raqu; Log In', 
		    'section' => 'general'
		);
		
		$fields['login_url'] = array(
		    'name' => __('Logo Image URL', 'laboratory' ), 
		    'description' => __('Change the URL that the logo image on the WordPress login page links to when clicked on.', 'laboratory'), 
		    'type' => 'text', 
		    'default' => home_url(), 
		    'section' => 'general', 
		    'validate' => 'validate_url' 
		);
		
		$this->fields = $fields;
	} // End init_fields()

	/**
	 * Validate URL fields.
	 * @param  string $url The URL to be validated.
	 * @since  1.0.0
	 * @return string The validated URL.
	 */
	public function validate_url ( $url ) {
		return esc_url( $url );
	} // End validate_url()
} // End Class
?>