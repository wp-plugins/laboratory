<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider Widget Base Class
 *
 * Base widget class for CustomSlider.
 *
 * @package WordPress
 * @subpackage CustomSlider
 * @category Widgets
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - __construct()
 * - init()
 * - widget()
 * - update()
 * - form()
 * - generate_field_by_type()
 * - generate_slideshow()
 */
class CustomSlider_Widget_Base extends WP_Widget {

	/* Variable Declarations */
	protected $slidetype;
	protected $can_show_slideshow;
	protected $defaults = array( 'title' => '' );
	protected $laboratory_widget_cssclass;
	protected $laboratory_widget_description;
	protected $laboratory_widget_idbase;
	protected $laboratory_widget_title;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct () {
		/* Widget variable settings. */
		$this->laboratory_widget_cssclass = 'widget_laboratory_slideshow_slideshow';
		$this->laboratory_widget_description = __( 'A slideshow of the content on your site', 'laboratory' );
		$this->laboratory_widget_idbase = 'laboratory_slideshow_slideshow';
		$this->laboratory_widget_title = __( 'Laboratory Slideshow', 'laboratory' );

		$this->can_show_slideshow = true;
    
	} // End Constructor
  
  public function init(){
    $widget_ops = array( 'classname' => $this->laboratory_widget_cssclass, 'description' => $this->laboratory_widget_description );

		parent::__construct(false,$this->laboratory_widget_title,$widget_ops);
  }

	/**
	 * widget function.
	 * @since  1.0.0
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget ( $args, $instance ) {
		$slideshow_html = $this->generate_slideshow( $instance );
		if ( $slideshow_html == '' ) { return; }

		$html = '';
		
		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $before_title . esc_html( $title ) . $after_title;
		}
		
		/* Widget content. */
		
		// Add actions for plugins/themes to hook onto.
		do_action( $this->laboratory_widget_cssclass . '_top' );
		
		// Load widget content here.
		$html = '';

		$html .= $slideshow_html;

		echo $html;

		// Add actions for plugins/themes to hook onto.
		do_action( $this->laboratory_widget_cssclass . '_bottom' );

		/* After widget (defined by themes). */
		echo $after_widget;

	} // End widget()

	/**
	 * update function.
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array $instance
	 */
	public function update ( $new_instance, $old_instance ) {
		global $laboratory_slideshow;

		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = esc_html( $new_instance['title'] );

		/* Save fields for the various contexts. */
		$fields = $laboratory_slideshow->admin->generate_default_conditional_fields( array( $this->slidetype => $this->slidetype ) );
		
		/* Advanced Settings switch and related fields. */
		$instance['show_advanced_settings'] = (bool)intval( $new_instance['show_advanced_settings'] );

		if ( isset( $instance['show_advanced_settings'] ) && ( $instance['show_advanced_settings'] == true ) ) {
			$laboratory_slideshow->settings->init_fields();
			$advanced_fields = $laboratory_slideshow->settings->fields;

			if ( is_array( $advanced_fields ) && count( $advanced_fields ) > 0 ) {
				foreach ( $advanced_fields as $i => $j ) {
					switch ( $j['type'] ) {
						case 'checkbox':
							if ( isset( $new_instance[$i] ) && ( $new_instance[$i] == 1 ) ) {
								$instance[$i] = (bool)intval( $new_instance[$i] );
							} else {
								$instance[$i] = false;
							}
						break;

						case 'multicheck':
							$instance[$i] = array_map( 'esc_attr', $new_instance[$i] );
						break;

						case 'text':
						case 'select':
						case 'images':
						case 'range':
						default:
							$instance[$i] = esc_attr( $new_instance[$i] );
						break;
					}
				}
			}
		}

		/* Cater for new fields and preserve old fields if they aren't part of our current fields to be saved. */
		// Save fields for the current type.
		if ( isset( $fields[$this->slidetype] ) ) {
			foreach ( $fields[$this->slidetype] as $i => $j ) {
				switch ( $j['type'] ) {
					case 'checkbox':
						if ( isset( $new_instance[$i] ) && ( $new_instance[$i] == 1 ) ) {
							$instance[$i] = (bool)intval( $new_instance[$i] );
						} else {
							$instance[$i] = false;
						}
					break;

					case 'multicheck':
						$instance[$i] = array_map( 'esc_attr', $new_instance[$i] );
					break;

					case 'text':
					case 'select':
					case 'images':
					case 'range':
						$instance[$i] = esc_attr( $new_instance[$i] );
					break;
				}
			}
		}

		// Allow child themes/plugins to act here.
		$instance = apply_filters( $this->laboratory_widget_idbase . '_widget_save', $instance, $new_instance, $this );

		return $instance;
	} // End update()

   /**
    * form function.
    * 
    * @since  1.0.0
    * @access public
    * @param array $instance
    * @uses  global $laboratory_slideshow object
    * @return void
    */
   public function form ( $instance ) {
   		global $laboratory_slideshow;

		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = $this->defaults;
		
		// Allow child themes/plugins to filter here.
		$defaults = apply_filters( $this->laboratory_widget_idbase . '_widget_defaults', $defaults, $this );
		
		$defaults['show_advanced_settings'] = 0;

		$advanced_fields = array();

		$laboratory_slideshow->settings->init_fields();
		$advanced_fields = $laboratory_slideshow->settings->fields;

		if ( is_array( $advanced_fields ) && count( $advanced_fields ) > 0 ) {
			foreach ( $advanced_fields as $k => $v ) {
				if ( ! isset( $defaults[$k] ) ) {
					$defaults[$k] = $v['default'];
				}
			}
		}

		$has_fields = false;
		if ( isset( $this->slidetype ) && ( $this->slidetype != '' ) ) {
			$fields = $laboratory_slideshow->admin->generate_default_conditional_fields( array( $this->slidetype => $this->slidetype ) );
			$has_fields = true;

			if ( 0 < count( $fields ) ) {
				foreach ( $fields[$this->slidetype] as $k => $v ) {
					if ( isset( $v['args']['data']['default'] ) ) {
						$defaults[$k] = $v['args']['data']['default'];
					}
				}
			}
		}

		$instance = wp_parse_args( $instance, $defaults );
?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):', 'laboratory' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>"  value="<?php echo $instance['title']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" />
		</p>
		<?php
			if ( isset( $this->slidetype ) && ( $this->slidetype != '' ) ) {
				foreach ( $fields[$this->slidetype] as $k => $v ) {
		?>
		<p>
			<?php
				$field_label = '<label for="' . esc_attr( $k ) . '">' . $v['name'] . '</label>' . "\n";
				if ( $v['type'] != 'checkbox' ) { echo $field_label; } // Display the label first if the field isn't a checkbox.
				$this->generate_field_by_type( $v['type'], $v['args'], $instance );
				if ( $v['type'] == 'checkbox' ) { echo $field_label; } // Display the label last if the field is a checkbox.
			?>
		</p>
		<?php
				}
		?>
		<hr />
		<!-- Widget Advanced Fields: Text Input -->
		<p>
			<input type="checkbox" name="<?php echo $this->get_field_name( 'show_advanced_settings' ); ?>"  value="1" id="<?php echo $this->get_field_id( 'show_advanced_settings' ); ?>" <?php checked( '1', $instance['show_advanced_settings'] ); ?> />
			<label for="<?php echo $this->get_field_id( 'show_advanced_settings' ); ?>"><?php _e( 'Customise Advanced Settings', 'laboratory' ); ?></label>
		</p>
		<p><small><?php _e( 'Save the widget settings with this checkbox checked to customise slideshow settings.', 'laboratory' ); ?></small></p>
		<?php
				if ( isset( $instance['show_advanced_settings'] ) && ( $instance['show_advanced_settings'] == 1 ) ) {
					if ( is_array( $advanced_fields ) && count( $advanced_fields ) > 0 ) {
						foreach ( $advanced_fields as $k => $v ) {
							echo '<p>' . "\n";
							$label = $v['name'];
							if ( $label == '' ) { $label = $v['description']; }
							$field_label = '<label for="' . esc_attr( $k ) . '">' . $label . '</label>' . "\n";
							if ( $v['type'] != 'checkbox' ) { echo $field_label; } // Display the label first if the field isn't a checkbox.
							$this->generate_field_by_type( $v['type'], array( 'key' => $k, 'data' => $v ), $instance );
							if ( $v['type'] == 'checkbox' ) { echo $field_label; } // Display the label last if the field is a checkbox.
							echo '</p>' . "\n";
						}
					}
				} else {
					if ( is_array( $advanced_fields ) && count( $advanced_fields ) > 0 ) {
						foreach ( $advanced_fields as $k => $v ) {
							echo '<input type="hidden" name="' . esc_attr( $this->get_field_name( $k ) ) . '" id="' . esc_attr( $this->get_field_id( $k ) ) . '" value="' . esc_attr( $instance[$k] ) . '" />' . "\n";
						}
					}
				}
			}
		?>
<?php
		// Allow child themes/plugins to act here.
		do_action( $this->laboratory_widget_idbase . '_widget_settings', $instance, $this );

	} // End form()

	/**
	 * Generate a field from the settings API based on a provided field type.
	 * @since  1.0.0
	 * @param  string $type The type of field to generate.
	 * @param  array $args Arguments to be passed to the field.
	 * @param  array $instance The current widget's instance.
	 * @return void
	 */
	private function generate_field_by_type ( $type, $args, $instance ) {
		if ( is_array( $args ) && isset( $args['key'] ) && isset( $args['data'] ) ) {
      $val = (isset($instance[$args['key']])) ? $instance[$args['key']] : '';
      $html = '';
			switch ( $type ) {
				// Select fields.
				case 'select':
				case 'images':
				case 'range':
					$html = '<select name="' . esc_attr( $this->get_field_name( $args['key'] ) ) . '" id="' . esc_attr( $this->get_field_id( $args['key'] ) ) . '" class="widefat">' . "\n";
					foreach ( $args['data']['options'] as $k => $v ) {

						$html .= '<option value="' . esc_attr( $k ) . '"' . selected( $k, $val, false ) . '>' . $v . '</option>' . "\n";
					}
					$html .= '</select>' . "\n";

					echo $html;
				break;

				// Multiple checkboxes.
				case 'multicheck':
				if ( isset( $args['data']['options'] ) && ( count( (array)$args['data']['options'] ) > 0 ) ) {
					$html = '<div class="multicheck-container">' . "\n";
          $val = (isset($instance[$args['key']])) ? $instance[$args['key']] : array();
					foreach ( $args['data']['options'] as $k => $v ) {
						$checked = '';
						if ( in_array( $k, (array)$val ) ) { $checked = ' checked="checked"'; }
						$html .= '<input type="checkbox" name="' . esc_attr( $this->get_field_name( $args['key'] ) ) . '[]" class="multicheck multicheck-' . esc_attr( $args['key'] ) . '" value="' . esc_attr( $k ) . '"' . $checked . ' /> ' . $v . '<br />' . "\n";
					}
					$html .= '</div>' . "\n";
					echo $html;
				}

				break;

				// Single checkbox.
				case 'checkbox':
				if ( isset( $args['key'] ) && $args['key'] != '' ) {
					$html .= '<input type="checkbox" name="' . esc_attr( $this->get_field_name( $args['key'] ) ) . '" class="checkbox checkbox-' . esc_attr( $args['key'] ) . '" value="1"' . checked( '1', $val, false ) . ' /> ' . "\n";
					echo $html;
				}

				break;

				// Text input.
				case 'text':
				if ( isset( $args['key'] ) && $args['key'] != '' ) {
					$html .= '<input type="text" name="' . esc_attr( $this->get_field_name( $args['key'] ) ) . '" class="input-text input-text-' . esc_attr( $args['key'] ) . ' widefat" value="' . esc_attr( $val ) . '" /> ' . "\n";
					echo $html;
				}

				break;
			}
		}
	} // End generate_field_by_type()

	/**
	 * Generate the HTML for this slideshow.
	 * @since  1.0.0
	 * @return string The generated HTML.
	 */
	protected function generate_slideshow ( $instance ) {
		global $laboratory_slideshow;
		$settings = $laboratory_slideshow->settings->get_settings();
		$settings['slidetype'] = $this->slidetype;

		$extra_args = array();

		foreach ( $instance as $k => $v ) {
			if ( ! in_array( $k, array_keys( $settings ) ) ) {
				$extra_args[$k] = esc_attr( $v );
			}
		}

		// Make sure the various settings are applied.
		foreach ( $settings as $k => $v ) {
			if ( isset( $instance[$k] ) && ( $instance[$k] != $settings[$k] ) ) {
				$settings[$k] = esc_attr( $instance[$k] );
			}
		}

		$html = laboratory_slideshow( $settings, $extra_args, false );

		return $html;
	} // End generate_slideshow()
} // End Class
?>