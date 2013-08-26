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
 * @since 1.0.1
 *
 * TABLE OF CONTENTS
 * 
 * - __construct()
 * - init_sections()
 * - init_fields()
 * - validate_url()
 * - fb_lang_lists()
 * 
 */
class Laboratory_FBComments_Settings extends Laboratory_Settings_API {
	
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
			
		$sections['main-settings'] = array(
		    'name' 			=> __('Main Settings', 'laboratory' ), 
		    'description'	=> __('')
            );
            
		$sections['display-settings'] = array(
		    'name' 			=> __('Display Settings', 'laboratory' ), 
		    'description'	=> __('')
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
		
        //Main Settings
		$fields['mods'] = array(
            'name' => __('Moderators', 'laboratory' ), 
            'description' => 'By default, all admins to the App ID can moderate comments. To add moderators, enter each Facebook Profile ID by a comma <strong>without spaces</strong>. To find your Facebook User ID, click <a href="https://developers.facebook.com/tools/explorer/?method=GET&path=me" target="blank">here</a> where you will see your own. To view someone else\'s, replace "me" with their username in the input provided', 
            'type' => 'text', 
            'default' => '', 
            'section' => 'main-settings'
            );
            
		$fields['appID'] = array(
            'name' => __('Facebook App ID', 'laboratory' ), 
            'description' => '', 
            'type' => 'text', 
            'default' => '', 
            'section' => 'main-settings'
            );
		
    	$fields['fbml'] = array(
    		'name' => __('Enable FBML', 'laboratory'),
    		'description' => __( 'Only disable this if you already have XFBML enabled elsewhere', 'laboratory' ),
    		'type' => 'checkbox',
    		'default' => true,
    		'section' => 'main-settings'
    		);
		
    	$fields['fbns'] = array(
			'name' => __('Use Facebook NameServer', 'laboratory'),
			'description' => __( 'Only enable this if Facebook Comments do not appear', 'laboratory' ),
			'type' => 'checkbox',
			'default' => false,
			'section' => 'main-settings'
			);

    	$fields['opengraph'] = array(
			'name' => __('Use Open Graph NameServer', 'laboratory'),
			'description' => __( 'only enable this if Facebook comments are not appearing, not all information is being passed to Facebook or if you have not enabled Open Graph elsewhere within WordPress', 'laboratory' ),
			'type' => 'checkbox',
			'default' => false,
			'section' => 'main-settings'
			);
            
    	$fields['html5'] = array(
			'name' => __('Use HTML5', 'laboratory'),
			'description' => '',
			'type' => 'checkbox',
			'default' => true,
			'section' => 'main-settings'
			);
            
    	$fields['linklove'] = array(
			'name' => __('Credit', 'laboratory'),
			'description' => '',
			'type' => 'checkbox',
			'default' => false,
			'section' => 'main-settings'
			);
            
        //Display Settings
    	$fields['posts'] = array(
			'name' => __('Posts', 'laboratory'),
			'description' => '',
			'type' => 'checkbox',
			'default' => true,
			'section' => 'display-settings'
			);

    	$fields['pages'] = array(
			'name' => __('Pages', 'laboratory'),
			'description' => '',
			'type' => 'checkbox',
			'default' => false,
			'section' => 'display-settings'
			);

    	$fields['homepage'] = array(
			'name' => __('Homepage', 'laboratory'),
			'description' => '',
			'type' => 'checkbox',
			'default' => false,
			'section' => 'display-settings'
			);

    	$fields['language'] = array(
            'name' => __( 'Language', 'laboratory' ), 
            'description' => '', 
            'type' => 'select', 
            'default' => 'en_US',
            'section' => 'display-settings', 
            'required' => 0, 
            'options' => $this->fb_lang_lists()
            );
            
    	$fields['scheme'] = array(
            'name' => __( 'Colour Scheme', 'laboratory' ), 
            'description' => '', 
            'type' => 'select', 
            'default' => 'light',
            'section' => 'display-settings', 
            'required' => 0, 
            'options' => array( 'light' => __( 'Light', 'laboratory' ), 'dark' => __( 'Dark', 'laboratory' ) )
            );

    	$fields['num'] = array(
			'name' => __( 'Number of Comments', 'laboratory' ), 
			'description' => __( 'Default is 5', 'laboratory' ), 
			'type' => 'text', 
			'default' => __( '5', 'laboratory' ), 
			'section' => 'display-settings'
			);
            
    	$fields['width'] = array(
			'name' => __( 'Width', 'laboratory' ), 
			'description' => __( 'Default is 450', 'laboratory' ), 
			'type' => 'text', 
			'default' => __( '450', 'laboratory' ), 
			'section' => 'display-settings'
			);

    	$fields['title'] = array(
			'name' => __( 'Title', 'laboratory' ),
			'description' => '',
			'type' => 'text',
			'default' => 'Comments',
			'section' => 'display-settings'
			);

    	$fields['count'] = array(
			'name' => __('Show Comment Count', 'laboratory'),
			'description' => '',
			'type' => 'checkbox',
			'default' => true,
			'section' => 'display-settings'
			);

    	$fields['countmsg'] = array(
			'name' => __( 'Comment text', 'laboratory' ),
			'description' => '',
			'type' => 'text',
			'default' => 'comments',
			'section' => 'display-settings'
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
    
    private function fb_lang_lists(){
        $options = array();
        
        $dom_object = new DOMDocument();
        $dom_object->load("http://www.facebook.com/translations/FacebookLocales.xml");
        $langfeed = $dom_object->getElementsByTagName("locale");
        
        foreach ( $langfeed as $value) {
            $names = $value->getElementsByTagName("englishName");
            $name  = $names->item(0)->nodeValue;
            $representations = $value->getElementsByTagName("representation");
            $representation  = $representations->item(0)->nodeValue;
            $options[$representation] = $name;
        }
        
        return $options;
    }
    
} // End Class
?>