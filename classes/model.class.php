<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Model Class
 *
 * The base Model for Laboratory.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Administration
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $active_components
 * var $components
 * var $sections
 * protected $username
 *
 * - __construct()
 * - is_active_component()
 * - get_status_token()
 * - get_status_label()
 * - get_component()
 * - get_component_slug()
 * - get_component_path()
 * - clean_component_path()
 * - load_components()
 * - load_addon_components()
 * - activate_component()
 * - deactivate_component()
 * - get_screenshot_url()
 * - set_username()
 * - get_username()
 * - is_logged_in()
 * - get_request_error()
 */
class Laboratory_Model {
	var $active_components;
	protected $username;
	
	function __construct() {
		global $laboratory;
		
		$this->config = $laboratory->base;
		
		$this->active_components = array();
	} // End __construct()
	
	/**
	 * is_active_component function.
	 *
	 * @description Check if a specified component is active.
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return boolean $is_active
	 */
	public function is_active_component ( $component, $type ) {
		$is_active = false;


			if ( ! isset( $this->active_components[$type] ) ) {
				$this->active_components[$type] = get_option( $this->config->token . '_' . $type . '_active', array() );
			}
			
			if ( in_array( $component, array_keys( (array)$this->active_components[$type] ) ) ) {
				if ( $type == 'addon' && file_exists( $this->config->components_path . $this->components[$type][$component]->filepath ) ) {
					$is_active = true;
				} else {
					$this->deactivate_component( $component, $type, false );
				}
			}

		
		return $is_active;
	} // End is_active_component()
	

	/**
	 * get_status_token function.
	 * 
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return string $label
	 */
	public function get_status_token ( $component, $type ) {
		$label = 'disabled';
		
		if ( $this->is_active_component( $component, $type ) ) {
			$label = 'enabled';
		}
		
		return $label;
	} // End get_status_token()

	/**
	 * get_status_label function.
	 * 
	 * @access public
	 * @param string $component
	 * @param string $type
	 * @return string $label
	 */
	public function get_status_label ( $component, $type ) {
		$label = __( 'Disabled', 'laboratory' );
		
		if ( $this->is_active_component( $component, $type ) ) {
			$label = __( 'Enabled', 'laboratory' );
		}
		
		return $label;
	} // End get_status_label()
	
	/**
	 * get_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param mixed $component
	 * @return void
	 */
	public function get_component ( $component ) {
		$headers = array(
			'title' => 'Addon Name',
			'short_description' => 'Addon Description', 
			'version' => 'Addon Version', 
			'sort' => 'Sort Order', 
			'settings' => 'Addon Settings',
			'deps' => 'Dependencies'
		);
		$mod = get_file_data( $component, $headers );
		if ( empty( $mod['sort'] ) )
			$mod['sort'] = 10;
		if ( ! empty( $mod['title'] ) ) {
			$obj = new StdClass();
			
			foreach ( $mod as $k => $v ) {
				$obj->$k = $v;
			}

			if ( ! isset( $obj->product_id ) ) {
				$obj->product_id = 0;
			}

			return $obj;
		}
		return false;
	} // End get_component()
	
	/**
	 * get_component_slug function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param mixed $file
	 * @return void
	 */
	public function get_component_slug ( $file ) {
		return str_replace( '.php', '', basename( $file ) );
	} // End get_component_slug()
	
	/**
	 * get_component_path function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param mixed $slug
	 * @return void
	 */
	public function get_component_path ( $slug ) {
		return $this->config->components_path . $slug . '.php';
	} // End get_component_path()
	
	/**
	 * clean_component_path function.
	 *
	 * @description Return the component path, relative to the addon components directory.
	 * @access public
	 * @since 1.0.0
	 * @param string $path
	 * @return string $path
	 */
	public function clean_component_path ( $path ) {
		return str_replace( $this->config->components_path, '', $path );
	} // End clean_component_path()
	
	/**
	 * load_components function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function load_components () {
		$this->components['addon'] = $this->load_addon_components();

	} // End load_components()
	
	
	/**
	 * load_addon_components function.
	 *
	 * @description Load the components that come addon.
	 * @access public
	 * @since 1.0.0
	 * @return array $components
	 */
	public function load_addon_components () {
		/*
		static $components = array();

		if ( isset( $components ) )
			return $components;
		*/
		$files = Laboratory_Utils::glob_php( '*.php', GLOB_MARK, $this->config->components_path );

		foreach ( $files as $file ) {
			if ( $headers = $this->get_component( $file ) ) {
				$slug = $this->get_component_slug( $file );
				$components[$slug] = $headers;
				$components[$slug]->filepath = $this->clean_component_path( $file );
				$components[$slug]->current_version = $components[$slug]->version;
			}
		}

		return $components;
	} // End load_addon_components()
	
	/**
	 * activate_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @param boolean $redirect
	 * @return boolean $activated
	 */
	public function activate_component ( $component, $type = 'addon', $redirect = true ) {
		$activated = false;
		

			$filepath = $this->components[$type][$component]->filepath;
			$directory = $this->config->get_directory_by_type( $type );
			
			if ( $filepath != '' && file_exists( $directory . $filepath ) ) {
				$components = get_option( $this->config->token . '_' . $type . '_active', array() );
				
				if ( ! in_array( $filepath, $components ) ) {
					$components[$component] = $filepath;
					$activated = update_option( $this->config->token . '_' . $type . '_active', $components );
				}
			}
		
		if ( $redirect == true ) {
			if ( $activated == true ) {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&activated-component=' . $component . '&type=' . $type ) );
				exit;
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&activation-error=' . $component . '&type=' . $type ) );
				exit;
			}
		} else {
			return $activated;
		}
	} // End activate_component()
	
	/**
	 * deactivate_component function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @param boolean $redirect
	 * @return boolean $deactivated
	 */
	public function deactivate_component ( $component, $type = 'addon', $redirect = true ) {
		$deactivated = false;
		
		
			$components = get_option( $this->config->token . '_' . $type . '_active', array() );
	
			if ( in_array( $component, array_keys( $components ) ) ) {
				unset( $components[$component] );
				$deactivated = update_option( $this->config->token . '_' . $type . '_active', $components );
			}


		if ( $redirect == true ) {
			if ( $deactivated == true ) {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&deactivated-component=' . $component . '&type=' . $type ) );
				exit;
			} else {
				wp_redirect( admin_url( 'admin.php?page=' . $this->config->token . '&deactivation-error=' . $component . '&type=' . $type ) );
				exit;
			}
		} else {
			return $deactivated;
		}
	} // End deactivate_component()

	
	/**
	 * get_screenshot_url function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	public function get_screenshot_url ( $component, $type ) {
		global $laboratory;

		$html = '';
		
		switch ( $type ) {
				
				case 'addon':
				default:
					$path = $this->config->components_path;
					$url = $this->config->components_url;
				break;
			}
			
		
		// If no screenshot, look in the "assets/screenshots" folder for component-screenshot.ext.
		if ( $html == '' ) {
			foreach ( array( 'png', 'jpg', 'jpeg', 'gif' ) as $k => $v ) {
				if ( file_exists( $path  . esc_attr( $component ) . '/screenshot.' . $v ) ) {
					$html = $url  . esc_attr( $component ) . '/screenshot.' . $v;
					
					break;
				}
			}
		}

		// If no screenshot, replace with a placeholder image.
		if ( $html == '' ) {
			$html = $this->config->assets_url . 'images/default-screenshot.png';
		}

		return $html;
	} // End get_screenshot_url()
	
	/**
	 * get_username function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return string
	 */
	public function get_username () {
		if ( false === ( $username = get_option( $this->config->token . '-username' ) ) ) {} else {
			$this->username = $username;
		}
		return $this->username;
	} // End get_username()
	
	/**
	 * set_username function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $username
	 * @return boolean
	 */
	public function set_username ( $username ) {
		if ( $username != '' ) { return update_option( $this->config->token . '-username', $username ); } else { return false; }
	} // End set_username()


	/**
	 * get_request_error function.
	 * 
	 * @access protected
	 * @since 1.0.0
	 * @return string $message
	 */
	protected function get_request_error () {
		$notice = get_transient( $this->config->token . '-request-error' );
		$message = '';

		if ( $notice != '' && ! is_array( $notice ) ) { $message = wpautop( '<strong>' . __( 'Message:', 'laboratory' ) . '</strong> ' . $notice ); }
		if ( is_array( $notice ) && count( $notice ) > 0 ) {
			foreach ( $notice as $k => $v ) {
				$message .= wpautop( $v );
			}
		}

		return $message;
	} // End get_request_error()
}
?>