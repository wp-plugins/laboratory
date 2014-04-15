<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Main Model
 *
 * The base Model for Laboratory.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Administration
 * @author Laboratory
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * private $components
 * var $sections
 * var $closed_components
 * var $admin_page_hook ( sent from the main admin class )
 * var $current_action_response
 *
 * - __construct()
 * - component_actions()
 * - admin_notices()
 * - get_section_links()
 * - get_closed_components()
 * - add_contextual_help()
 * - get_action_button()
 * - get_enable_button()
 * - get_disable_button()
 */
class Laboratory_Model_Main extends Laboratory_Model {
	var $components;
	var $current_action_response;

	public function __construct() {
		parent::__construct();

		$this->products = array();

		$this->components = array(
								'addon' => array()
								);

		$this->sections = array(
								'addon' => array(
														'name' => __( 'Features', 'laboratory' ), 
														'description' => __( 'Features addon with Laboratory.', 'laboratory' )
													)
								);
			
		$this->closed_components = array();
						
		$this->load_components();

		$this->get_closed_components();

		$this->current_action_response = $this->component_actions();


		add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		add_action( 'admin_head', array( $this, 'add_contextual_help' ) );	
	} // End __construct()
	
	/**
	 * component_actions function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function component_actions () {
		$response = false;

		// Component activation.
		if ( ( isset( $_GET['activate-component'] ) || isset( $_POST['activate-component'] ) ) && ( Laboratory_Utils::get_or_post( 'activate-component' ) != '' ) ) {
			$response = $this->activate_component( trim( esc_attr( Laboratory_Utils::get_or_post( 'activate-component' ) ) ), trim( esc_attr( Laboratory_Utils::get_or_post( 'component-type' ) ) ) );
		}
		
		// Component deactivation.
		if ( ( isset( $_GET['deactivate-component'] ) || isset( $_POST['deactivate-component'] ) ) && ( Laboratory_Utils::get_or_post( 'deactivate-component' ) != '' ) ) {
			$response = $this->deactivate_component( trim( esc_attr( Laboratory_Utils::get_or_post( 'deactivate-component' ) ) ), trim( esc_attr( Laboratory_Utils::get_or_post( 'component-type' ) ) ) );
		}
		

		return $response;
	} // End component_actions()

	/**
	 * admin_notices function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_notices () {
		$notice = '';

		// Successful Activation.
		if ( isset( $_GET['activated-component'] ) && ( $_GET['activated-component'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['activated-component']]->title;
			$notice = '<div id="message" class="success updated fade"><p>' . sprintf( __( '%s activated successfully.', 'laboratory' ), $name ) . '</p></div>' . "\n";
		}
		
		// Unsuccessful Activation.
		if ( isset( $_GET['activation-error'] ) && ( $_GET['activation-error'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['activation-error']]->title;
			$notice = '<div id="message" class="error"><p>' . sprintf( __( 'There was an error activating %s. Please try again.', 'laboratory' ), $name ) . '</p></div>' . "\n";
		}
		
		// Successful Deactivation.
		if ( isset( $_GET['deactivated-component'] ) && ( $_GET['deactivated-component'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['deactivated-component']]->title;
			$notice = '<div id="message" class="success updated fade"><p>' . sprintf( __( '%s deactivated successfully.', 'laboratory' ), $name ) . '</p></div>' . "\n";
		}
		
		// Unsuccessful Dectivation.
		if ( isset( $_GET['deactivation-error'] ) && ( $_GET['deactivation-error'] != '' ) ) {
			$name = $this->components[trim( esc_attr( $_GET['type'] ) )][$_GET['deactivation-error']]->title;
			$notice = '<div id="message" class="error"><p>' . sprintf( __( 'There was an error deactivating %s. Please try again.', 'laboratory' ), $name ) . '</p></div>' . "\n";
		}
		
		echo $notice;
	} // End admin_notices()

	/**
	 * get_section_links function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function get_section_links () {
		$html = '';
		
		$total = 0;
		
		$sections = array(
						'all' => array( 'href' => '#all', 'name' => __( 'All', 'laboratory' ), 'class' => 'current all tab' )
					);
					
		foreach ( $this->sections as $k => $v ) {
			$total += count( $this->components[$k] );
			$sections[$k] = array( 'href' => '#' . esc_attr( $this->config->token . '-' . $k ), 'name' => $v['name'], 'class' => 'tab', 'count' => count( $this->components[$k] ) );
		}
		
		$sections['all']['count'] = $total;
		
		$sections = apply_filters( $this->config->token . '_main_section_links_array', $sections );
		


		$count = 1;
		foreach ( $sections as $k => $v ) {
			$count++;
			if ( $v['count'] > 0 ) {
				$html .= '<li><a href="' . $v['href'] . '"';
				if ( isset( $v['class'] ) && ( $v['class'] != '' ) ) { $html .= ' class="' . esc_attr( $v['class'] ) . '"'; }
				$html .= '>' . $v['name'] . '</a>';
				$html .= ' <span>(' . $v['count'] . ')</span>';
				if ( $count <= count( $sections ) ) { $html .= ' | '; }
				$html .= '</li>' . "\n";
			}
		}
		
		echo $html;
		
		do_action( $this->config->token . '_main_get_section_links' );
	} // End get_section_links()
	
	/**
	 * get_closed_components function.
	 *
	 * @description Return an array of the tokens of components that are closed.
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function get_closed_components () {
		$this->closed_components = get_option( $this->config->token . '_closed_components', array() );
	} // End get_closed_components()

	/**
	 * add_contextual_help function.
	 *
	 * @description Add contextual help to the current screen.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function add_contextual_help () {
		get_current_screen()->add_help_tab( array(
		'id'		=> 'overview',
		'title'		=> __( 'Overview', 'laboratory' ),
		'content'	=>
			'<p>' . __( 'This screen provides an overview of all features available through Laboratory. Features are also enabled and disabled here.', 'laboratory' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
		'id'		=> 'addon-features',
		'title'		=> __( 'Addon Features', 'laboratory' ),
		'content'	=>
			'<p>' . __( 'Addon features are immediately available and come packaged with Laboratory.', 'laboratory' ) . '</p>' .
			'<ul>' .
				'<li>' . __( 'Enabling or disabling a addon feature can be done instantly. Simply click the "Enable" or "Disable" button for the desired feature under the "Addon Features" section.', 'laboratory' ) . '</li>' .
			'</ul>'
		) );

		get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:', 'laboratory' ) . '</strong></p>' .
        '<p><a href="http://colorlabsproject.com/resolve/" id="support-' . $this->config->token . '" target="_blank">' . __( 'Support Desk', 'laboratory' ) . '</a></p>'
	
		);
	} // End add_contextual_help()
	
	/**
	 * get_action_button function.
	 * 
	 * @access public
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $button
	 */
	public function get_action_button ( $component, $type ) {
		$i = $component;
		$k = $type;

		$button = '';
		
				if ( $this->is_active_component( $component, $type ) ) {
					$button = $this->get_disable_button( $component, $type );
				} else {
					$button = $this->get_enable_button( $component, $type );
				}
		
		return $button;
	} // End get_action_button()
	
	/**
	 * get_enable_button function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	private function get_enable_button ( $component, $type ) {
		$id = $this->components[$type][$component]->product_id;

		$html = '';
		$html .= '<input type="submit" id="button-component-' . $component . '-activate" class="button-primary component-control-save enable" value="' . esc_attr__( 'Activate', 'laboratory' ) . '" />' . "\n";
		$html .= '<input type="hidden" name="activate-component" id="component-' . $component . '-activate" value="' .  esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component_id" value="' . esc_attr( $id ) . '" />' . "\n";
		return $html;
	} // End get_enable_button()
	
	/**
	 * get_disable_button function.
	 * 
	 * @access private
	 * @since 1.0.0
	 * @param string $component
	 * @param string $type
	 * @return string $html
	 */
	private function get_disable_button ( $component, $type ) {
		$id = $this->components[$type][$component]->product_id;

		$html = '';
		$html .= '<input type="submit" id="button-component-' . $component . '-deactivate" class="button-primary component-control-save disable" value="' . esc_attr__( 'Deactivate', 'laboratory' ) . '" />' . "\n";
		$html .= '<input type="hidden" name="deactivate-component" id="component-' . $component . '-deactivate" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component" value="' . esc_attr( $component ) . '" />' . "\n";
		$html .= '<input type="hidden" name="component_id" value="' . esc_attr( $id ) . '" />' . "\n";
		return $html;
	} // End get_disable_button()
	
	



} // End Class
?>