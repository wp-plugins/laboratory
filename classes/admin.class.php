<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Administration Class
 *
 * All functionality pertaining to the administration sections of Laboratory.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Administration
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $model
 *
 * - __construct()
 * - admin_screen_register()
 * - admin_menu_order()
 * - admin_screen()
 * - admin_head()
 * - admin_page_load()
 * - admin_styles()
 * - admin_styles_global()
 * - admin_scripts()
 * - ajax_component_toggle()
 * - ajax_component_display_toggle()
 * - ajax_get_closed_components()
 */
class Laboratory_Admin extends Laboratory_Base {
	var $model;
	var $hook;
	private $whitelist;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function __construct() {
		parent::__construct();		
		add_action( 'admin_menu', array( &$this, 'admin_screen_register' ) );
		add_action( 'wp_ajax_laboratory_component_toggle', array( &$this, 'ajax_component_toggle' ) );
		add_action( 'wp_ajax_laboratory_component_display_toggle', array( &$this, 'ajax_component_display_toggle' ) );
		add_action( 'wp_ajax_laboratory_get_closed_components', array( &$this, 'ajax_get_closed_components' ) );

		// Only these models and views can be loaded.
		$this->whitelist = array( 'main' );
	} // End __construct()
	
	/**
	 * admin_screen_register function.
	 *
	 * @description Register the admin screen and run the necessary procedures.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function admin_screen_register () {
		$hook = add_menu_page( $this->name, $this->name, 'manage_options', $this->slug_name, array( $this, 'admin_screen' ), $this->assets_url . 'images/menu-icon.png', 27 );
		
		add_action( 'load-' . $hook, array( $this, 'admin_page_load' ) );
		add_action( 'admin_head-' . $hook, array( $this, 'admin_head' ) ); 
		//add_filter( 'custom_menu_order', '__return_true' );
		//add_filter( 'menu_order', array( $this, 'admin_menu_order' ) );

		add_action( 'admin_print_styles-' . $hook, array( $this, 'admin_styles' ) );
		add_action( 'admin_head-' . $hook, array($this, 'laboratory_panel_css') );
		add_action( 'admin_print_scripts-' . $hook, array( $this, 'laboratory_admin_scripts' ) );
		
		// Global styles.
		add_action( 'admin_print_styles', array( $this, 'laboratory_admin_styles_global' ) );
		
		do_action( $this->token . '_admin_menu' );
		
		$this->hook = $hook; // Store the hook for later use.
	} // End admin_screen_register()
	
	/**
	 * Render custom css for panel color schemes
	 */
	function laboratory_panel_css(){
		require_once( plugin_dir_path( __FILE__ ).'scss.inc.php' );	

		global $_wp_admin_css_colors;
		$color_scheme = get_user_option( 'admin_color', get_current_user_id() );
				
		$scss = new scssc();
	
		if('fresh'==$color_scheme){
			$base_color = "#09C";
		}else{
			$base_color = $_wp_admin_css_colors[ $color_scheme ]->colors[1];
			$second_color = $_wp_admin_css_colors[ $color_scheme ]->colors[2];
			$highlight_color = $_wp_admin_css_colors[ $color_scheme ]->colors[0];
			$custom_color = $_wp_admin_css_colors[ $color_scheme ]->colors[3];
		}
		
		$panel_style = '<style>';

		if('fresh'==$color_scheme){
			$panel_style .= ".laboratory-sidebar, .laboratory-footnote, .settings-header-fixed{background: ".$base_color .";}";
			$panel_style .= ".laboratory-menu {background:#007EA8;}";
			$panel_style .= ".wp-core-ui .button-primary {background-color: #FFB101;border-color: #DA903B;box-shadow:0 1px 0 rgba(0, 0, 0, 0.2), 0 1px 0 0 rgba(255, 255, 255, 0.6) inset}";
			$panel_style .= ".wp-core-ui .button-primary:hover{background-color: #FFCA00;border-color: #DA903B;box-shadow: 0 1px 0 rgba(0, 0, 0, 0.2), 0 1px 0 0 rgba(255, 255, 255, 0.6) inset;}";
		}elseif('light'==$color_scheme){
			$panel_style .= ".laboratory-sidebar, .laboratory-footnote, .settings-header-fixed{background: ".$base_color .";}";
			$panel_style .= ".laboratory-menu {background: ".$highlight_color .";}";
			$panel_style .= ".btn-close {background: ".$second_color .";}";
			$panel_style .= ".laboratory-menu a {color:#333;}";
			$panel_style .= ".laboratory-menu a.active {color:#fff;}";
			$panel_style .= ".laboratory-menu .menu-hover {background: ".$second_color.";}";
		}elseif('blue'==$color_scheme){
			$panel_style .= ".laboratory-sidebar, .laboratory-footnote, .settings-header-fixed{background: ".$second_color .";}";
			$panel_style .= ".laboratory-menu {background: ".$base_color .";}";
			$panel_style .= ".btn-close {background: ".$highlight_color .";}";
			$panel_style .= ".laboratory-menu .menu-hover {background: ".$highlight_color.";}";
		}elseif('midnight'==$color_scheme){
			$panel_style .= ".laboratory-sidebar, .laboratory-footnote, .settings-header-fixed{background: ".$base_color .";}";
			$panel_style .= ".laboratory-menu {background: ".$highlight_color .";}";
			$panel_style .= ".btn-close {background: ".$custom_color .";}";
			$panel_style .= ".laboratory-menu .menu-hover {background: ".$custom_color.";}";
		}else{
			$panel_style .= ".laboratory-sidebar, .laboratory-footnote, .settings-header-fixed{background: ".$base_color .";}";
			$panel_style .= ".laboratory-menu {background: ".$highlight_color .";}";
			$panel_style .= ".btn-close {background: ".$second_color .";}";
			$panel_style .= ".laboratory-menu .menu-hover {background: ".$second_color.";}";
		}
		
		$panel_style .= '</style>';
		
		echo $panel_style;
	}
	
	/**
	 * admin_menu_order function.
	 *
	 * @description Move the menu item to be the second item in the menu.
	 * @access public
	 * @since 1.0.0
	 * @param mixed $menu_order
	 * @return void
	 */
	function admin_menu_order ( $menu_order ) {
		$new_menu_order = array();
		foreach ( $menu_order as $index => $item ) {
			if ( $item != $this->token )
				$new_menu_order[] = $item;

			if ( $index == 99 )
				$new_menu_order[] = $this->token;
		}
		return $new_menu_order;
	} // End admin_menu_order()
	
	/**
	 * admin_screen function.
	 *
	 * @description Load the main admin screen.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function admin_screen () {
		$screen = 'main';
		if ( isset( $_GET['screen'] ) && ( '' != $_GET['screen'] ) ) {
			$screen = esc_attr( trim( $_GET['screen'] ) );
		}
		
		$filetoken = 'main';
		
		$filetoken = str_replace( ' ', '-', strtolower( $screen ) );
		
		if ( in_array( $filetoken, $this->whitelist ) && file_exists( $this->views_path . $filetoken . '.php' ) ) {
			require_once( $this->views_path . $filetoken . '.php' );
		} else {
			return false;
		}
	} // End admin_screen()
	
	/**
	 * admin_head function.
	 *
	 * @description Run in the admin_head of the admin screen.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function admin_head () {
	
	} // End admin_head()
	
	/**
	 * admin_page_load function.
	 *
	 * @description Run when the admin screen loads.
	 * @access public
	 * @since 1.0.0
	 * @uses global $laboratory->settings
	 * @return void
	 */
	function admin_page_load () {
		global $laboratory;

		require_once( 'model.class.php' );
		

		$screen = 'main';
		if ( isset( $_GET['screen'] ) && ( '' != $_GET['screen'] ) ) {
			$screen = esc_attr( trim( $_GET['screen'] ) );
		}
		
		$default = 'Laboratory_Model';
		$filetoken = 'model';
		

		$filetoken = str_replace( ' ', '-', strtolower( $screen ) );
		$classname = $default . '_' . str_replace( ' ', '', ucwords( str_replace( '-', ' ', $screen ) ) );
		
		if ( ( $default != $classname ) && in_array( $filetoken, $this->whitelist ) && file_exists( $this->models_path . $filetoken . '.class.php' ) ) {
			require_once( $this->models_path . $filetoken . '.class.php' );
		} else {
			return false;
		}
		
		$this->model = new $classname();
		$this->model->admin_page_hook = $this->hook; // Send the admin page hook to the model.		
	} // End admin_page_load()
	
	/**
	 * admin_styles function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function admin_styles () {
		wp_register_style( $this->token . '-admin', $this->assets_url . 'css/admin.css', '', '1.0.0', 'screen' );
		wp_register_style( $this->token . '-codemirror', $this->assets_url . 'css/codemirror.css', '', '1.0.0', 'screen' );
		
	if(
		( isset($_GET['page']) && $_GET['page'] == 'laboratory' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'laboratory-fbcomments' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'laboratory-custom-code' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'laboratory-custom-slider' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'colabs7' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'crm' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'crm_inbound' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'laboratory-login-branding' ) ||
		( isset($_GET['page']) && $_GET['page'] == 'laboratory-shortlinks-settings' ) 	
	)
		wp_enqueue_style( $this->token . '-admin' );
		wp_enqueue_style( $this->token . '-codemirror' );
	} // End admin_styles()
	
	/**
	 * admin_styles_global function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function laboratory_admin_styles_global () {
		wp_register_style( $this->token . '-global', $this->assets_url . 'css/global.css', '', '1.0.0', 'screen' );
		
		wp_enqueue_style( $this->token . '-global' );
	} // End admin_styles_global()
	
	/**
	 * admin_scripts function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	function laboratory_admin_scripts () {
		wp_register_script( $this->token . '-admin', $this->assets_url . 'js/admin.js', array( 'jquery' ), '1.0.1', true );
        
        //admin UI script
		wp_register_script( $this->token . '-admin-plugins', $this->assets_url . 'js/admin-plugins.js', array( 'jquery' ), '1.0.1', true );
		
		wp_enqueue_script( $this->token . '-admin-plugins' );
		wp_enqueue_script( $this->token . '-admin' );
		
		$translation_strings = Laboratory_Utils::load_common_l10n();
		
		$ajax_vars = array( $this->token . '_component_toggle_nonce' => wp_create_nonce( $this->token . '_component_toggle_nonce' ) );

		$data = array_merge( $translation_strings, $ajax_vars );

		/* Specify variables to be made available to the admin.js file. */
		wp_localize_script( $this->token . '-admin', $this->token . '_localized_data', $data );
	} // End admin_scripts()
	
	/**
	 * ajax_component_toggle function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_component_toggle () {
		$nonce = $_POST[$this->token . '_component_toggle_nonce'];

		//Add nonce security to the request
		if ( ! wp_verify_nonce( $nonce, $this->token . '_component_toggle_nonce' ) ) {
			die();
		}
		
		// Make sure our model is available.
		$this->admin_page_load();

		// Component activation.
		if ( isset( $_POST['task'] ) && ( $_POST['task'] == 'activate-component' ) ) {
			echo $this->model->activate_component( trim( esc_attr( $_POST['component'] ) ), trim( esc_attr( $_POST['type'] ) ), false );
		}
		
		// Component deactivation.
		if ( isset( $_POST['task'] ) && ( $_POST['task'] == 'deactivate-component' ) ) {
			echo $this->model->deactivate_component( trim( esc_attr( $_POST['component'] ) ), trim( esc_attr( $_POST['type'] ) ), false );
		}
		
		die(); // WordPress may print out a spurious zero without this can be particularly bad if using JSON
	} // End ajax_component_toggle()
	
	/**
	 * ajax_component_display_toggle function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_component_display_toggle () {
		$nonce = $_POST[$this->token . '_component_toggle_nonce'];

		//Add nonce security to the request
		if ( ! wp_verify_nonce( $nonce, $this->token . '_component_toggle_nonce' ) ) {
			die();
		}
		
		// Get stored list of closed components.
		$closed = get_option( $this->token . '_closed_components', array() );
		
		$component = (array)$_POST['component'];
		array_map( 'esc_attr', $component );
		array_map( 'trim', $component );
		
		$status = esc_attr( trim( $_POST['status'] ) );
		
		foreach ( $component as $k => $v ) {
			if ( in_array( $v, $closed ) && ( $status == 'open' ) ) {
				foreach ( $closed as $i => $j ) {
					if ( $j == $v ) {
						unset( $closed[$i] );
						break;
					}
				}
			}
			
			if ( ( $status == 'closed' ) && ! in_array( $v, $closed ) ) {
				$closed[] = $v;
			}
		}

		// Update the database.
		echo update_option( $this->token . '_closed_components', $closed );
		
		die(); // WordPress may print out a spurious zero without this can be particularly bad if using JSON
	} // End ajax_component_toggle()
	
	/**
	 * ajax_get_closed_components function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_get_closed_components () {
		$nonce = $_POST[$this->token . '_component_toggle_nonce'];

		//Add nonce security to the request
		if ( ! wp_verify_nonce( $nonce, $this->token . '_component_toggle_nonce' ) ) {
			die();
		}
		
		// Get stored list of closed components.
		$closed = get_option( $this->token . '_closed_components', array() );
		
		echo json_encode( $closed );

		die(); // WordPress may print out a spurious zero without this can be particularly bad if using JSON
	} // End ajax_get_closed_components()
}

//twitter
class laboratory_twitter
{
	public $consumer_key = '9oniptvwS1XN16mCar5w';
	public $consumer_secret = 'RqEiNy3RksnYm29T3TCnb1pSbOZUcdIxZrAyS9Fs';
	/**
	* Linkify Twitter Text
	* 
	* @param string s Tweet
	* 
	* @return string a Tweet with the links, mentions and hashtags wrapped in <a> tags 
	*/
	function laboratory_linkify_twitter_text($tweet = ''){
		$url_regex = '/((https?|ftp|gopher|telnet|file|notes|ms-help):((\/\/)|(\\\\))+[\w\d:#@%\/\;$()~_?\+-=\\\.&]*)/';
		$tweet = preg_replace($url_regex, '<a href="$1" target="_blank">'. "$1" .'</a>', $tweet);
		$tweet = preg_replace( array(
		  '/\@([a-zA-Z0-9_]+)/', # Twitter Usernames
		  '/\#([a-zA-Z0-9_]+)/' # Hash Tags
		), array(
		  '<a href="http://twitter.com/$1" target="_blank">@$1</a>',
		  '<a href="http://twitter.com/search?q=%23$1" target="_blank">#$1</a>'
		), $tweet );
		
		return $tweet;
	}

	/**
	* Get User Timeline
	* 
	*/
	function laboratory_get_user_timeline( $username = '', $limit = 5, $include_retweets = false, $exclude_replies = false ) {
		$key = "twitter_user_timeline_{$username}_{$limit}";

		// Check if cache exists
		$timeline = get_transient( $key );
		if ($timeline !== false) {
		  return $timeline;
		} else {
			$retweets = '';
			$replies = '';
			if ( $include_retweets == true ) { $retweets = '&include_rts=1'; }
			if ( $exclude_replies == true ) { $replies = '&exclude_replies=1'; }
		  $headers = array( 'Authorization' => 'Bearer ' . $this->laboratory_get_access_token() );
		  $response = wp_remote_get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name={$username}&count={$limit}{$retweets}{$replies}", array( 
			'headers' => $headers, 
			'timeout' => 40,
			'sslverify' => false 
		  ));
		  if ( is_wp_error($response) ) {
			// In case Twitter is down we return error
			if(function_exists('dbgx_trace_var')) dbgx_trace_var($response);
			return array('error' => __('There is problem fetching twitter timeline', 'colabsthemes'));
		  } else {
			// If everything's okay, parse the body and json_decode it
			$json = json_decode(wp_remote_retrieve_body($response));

			// Check for error
			if( !count( $json ) ) {
			  return array('error' => __('There is problem fetching twitter timeline', 'colabsthemes'));
			} elseif( isset( $json->errors ) ) {
			  return array('error' => $json->errors[0]->message);
			} else {
			  set_transient( $key, $json, 60 * 60 );
			  return $json;
			}
		  }
		}
	}

	/**
	* Get Twitter application-only access token
	* @return string Access token
	*/
	function laboratory_get_access_token() {
		$consumer_key = urlencode( $this->consumer_key );
		$consumer_secret = urlencode( $this->consumer_secret );
		$bearer_token = base64_encode( $consumer_key . ':' . $consumer_secret );

		$oauth_url = 'https://api.twitter.com/oauth2/token';

		$headers = array( 'Authorization' => 'Basic ' . $bearer_token );
		$body = array( 'grant_type' => 'client_credentials' );

		$response = wp_remote_post( $oauth_url, array(
		  'headers' => $headers,
		  'body' => $body,
		  'timeout' => 40,
		  'sslverify' => false
		) );

		if( !is_wp_error( $response ) ) {
		  $response_json = json_decode( $response['body'] );
		  return $response_json->access_token;
		} else {
		  return false;
		}
	}


	/**
	* Builder Twitter timeline HTML markup
	*/
	function laboratory_build_twitter_markup( $timelines = array() ) { ?>
		<ul class="tweets">
		<?php foreach( $timelines as $item ) : ?>
		  <?php 
			$screen_name = $item->user->screen_name;
			$profile_link = "http://twitter.com/{$screen_name}";
			$status_url = "http://twitter.com/{$screen_name}/status/{$item->id}";
		  ?>
		  <li>
			<span class="content">
			  <?php echo $this->laboratory_linkify_twitter_text( $item->text ); ?>
			</span>
			 <a href="<?php echo $status_url; ?>" style="font-size:85%" class="time" target="_blank">
				<?php echo date('M j, Y', strtotime($item->created_at)); ?>
			  </a>
		  </li>
		<?php endforeach; ?>
		</ul>
		<?php 
	}
}
	//
?>