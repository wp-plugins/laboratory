<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Tweets Widget
 *
 * A bundled Laboratory Tweets stream widget.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Bundled
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $woo_widget_cssclass
 * var $woo_widget_description
 * var $woo_widget_idbase
 * var $woo_widget_title
 * 
 * var $transient_expire_time
 * 
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - enqueue_styles()
 */
class Laboratory_Widget_Tweets extends WP_Widget {

	/* Variable Declarations */
	var $laboratory_widget_cssclass;
	var $laboratory_widget_description;
	var $laboratory_widget_idbase;
	var $laboratory_widget_title;

	var $transient_expire_time;

	/**
	 * __construct function.
	 * 
	 * @access public
	 * @uses Laboratory
	 * @return void
	 */
	public function __construct () {
		/* Widget variable settings. */
		$this->laboratory_widget_cssclass = 'widget_laboratory_tweets';
		$this->laboratory_widget_description = __( 'This is a Laboratory bundled tweets widget.', 'laboratory' );
		$this->laboratory_widget_idbase = 'laboratory_tweets';
		$this->laboratory_widget_title = __('Laboratory - Tweets', 'laboratory' );
		
		$this->transient_expire_time = 60 * 60; // 1 hour.

		/* Widget settings. */
		$widget_ops = array( 'classname' => $this->laboratory_widget_cssclass, 'description' => $this->laboratory_widget_description );

		/* Widget control settings. */
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => $this->laboratory_widget_idbase );

		/* Create the widget. */
		$this->WP_Widget( $this->laboratory_widget_idbase, $this->laboratory_widget_title, $widget_ops, $control_ops );
	} // End Constructor

	/**
	 * widget function.
	 * 
	 * @access public
	 * @param array $args
	 * @param array $instance
	 * @return void
	 */
	public function widget( $args, $instance ) {
		// Twitter handle is required.
		if ( ! isset( $instance['twitter_handle'] ) || ( $instance['twitter_handle'] == '' ) ) { return; }

		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
		
			echo $before_title . $title . $after_title;
		
		} // End IF Statement
		
		/* Widget content. */
		
		// Add actions for plugins/themes to hook onto.
		do_action( $this->laboratory_widget_cssclass . '_top' );
		
		// Load widget content here.
		global $laboratory;
		require_once($laboratory->base->plugin_path.'classes/admin.class.php' );
		$laboratory_twit = new laboratory_twitter();
	  $user_timeline = $laboratory_twit->laboratory_get_user_timeline( $instance['twitter_handle'], $instance['limit'], $instance['include_retweets'], $instance['exclude_replies'] );
	  if( isset( $user_timeline['error'] ) ) : ?>
		<p><?php echo $user_timeline['error']; ?></p>
	  <?php 
	  else : 
		$laboratory_twit->laboratory_build_twitter_markup( $user_timeline );
	  endif;

		if ( $instance['include_follow_link'] != false ) {
			$html .= '<p class="follow-link"><a href="' . esc_url( 'http://twitter.com/' . urlencode( $instance['twitter_handle'] ) ) . '">' . sprintf( __( 'Follow %s on Twitter', 'laboratory' ), $instance['twitter_handle'] ) . '</a></p>';
		}

		echo $html; // If using the $html variable to store the output, you need this. ;)
		
		// Add actions for plugins/themes to hook onto.
		do_action( $this->laboratory_widget_cssclass . '_bottom' );

		/* After widget (defined by themes). */
		echo $after_widget;
	} // End widget()

	/**
	 * update function.
	 * 
	 * @access public
	 * @param array $new_instance
	 * @param array $old_instance
	 * @return array $instance
	 */
	public function update ( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );

		/* Strip tags for the Twitter username, and sanitize it as if it were a WordPress username. */
		$instance['twitter_handle'] = strip_tags( sanitize_user( $new_instance['twitter_handle'] ) );
		
		/* Escape the text string and convert to an integer. */
		$instance['limit'] = intval( strip_tags( $new_instance['limit'] ) );

		/* The checkbox is returning a Boolean (true/false), so we check for that. */
		$instance['include_retweets'] = (bool) esc_attr( $new_instance['include_retweets'] );
		$instance['exclude_replies'] = (bool) esc_attr( $new_instance['exclude_replies'] );
		$instance['include_follow_link'] = (bool) esc_attr( $new_instance['include_follow_link'] );
		
		// Allow child themes/plugins to act here.
		$instance = apply_filters( $this->laboratory_widget_idbase . '_widget_save', $instance, $new_instance, $this );
		
		// Clear the transient, forcing an update on next frontend page load.
		delete_transient( $this->id . '-tweets' );

		return $instance;
	} // End update()

   /**
    * form function.
    * 
    * @access public
    * @param array $instance
    * @return void
    */
   public function form ( $instance ) {
		/* Set up some default widget settings. */
		/* Make sure all keys are added here, even with empty string values. */
		$defaults = array(
						'title' => __( 'Tweets', 'laboratory' ), 
						'twitter_handle' => '', 
						'limit' => 5, 
						'include_retweets' => 0, 
						'exclude_replies' => 0, 
						'include_follow_link' => 1
					);
		
		// Allow child themes/plugins to filter here.
		$defaults = apply_filters( $this->laboratory_widget_idbase . '_widget_defaults', $defaults, $this );
		
		$instance = wp_parse_args( (array) $instance, $defaults );
?>
		<!-- Widget Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):', 'laboratory' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'title' ); ?>"  value="<?php echo $instance['title']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" />
		</p>
		<!-- Widget Twitter Handle: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'twitter_handle' ); ?>"><?php _e( 'Twitter Username (required):', 'laboratory' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'twitter_handle' ); ?>"  value="<?php echo $instance['twitter_handle']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'twitter_handle' ); ?>" />
		</p>
		<!-- Widget Limit: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit:', 'laboratory' ); ?></label>
			<input type="text" name="<?php echo $this->get_field_name( 'limit' ); ?>"  value="<?php echo $instance['limit']; ?>" class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" />
		</p>
		<!-- Widget Include Retweets: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'include_retweets' ); ?>" name="<?php echo $this->get_field_name( 'include_retweets' ); ?>" type="checkbox"<?php checked( $instance['include_retweets'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'include_retweets' ); ?>"><?php _e( 'Include Retweets', 'laboratory' ); ?></label>
		</p>
		<!-- Widget Exclude Replies: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'exclude_replies' ); ?>" name="<?php echo $this->get_field_name( 'exclude_replies' ); ?>" type="checkbox"<?php checked( $instance['exclude_replies'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'exclude_replies' ); ?>"><?php _e( 'Exclude Replies', 'laboratory' ); ?></label>
		</p>
		<!-- Widget Include Follow Link: Checkbox Input -->
		<p>
			<input id="<?php echo $this->get_field_id( 'include_follow_link' ); ?>" name="<?php echo $this->get_field_name( 'include_follow_link' ); ?>" type="checkbox"<?php checked( $instance['include_follow_link'], 1 ); ?> />
        	<label for="<?php echo $this->get_field_id( 'include_follow_link' ); ?>"><?php _e( 'Include Follow Link', 'laboratory' ); ?></label>
		</p>
<?php
		
		// Allow child themes/plugins to act here.
		do_action( $this->laboratory_widget_idbase . '_widget_settings', $instance, $this );

	} // End form()

	/**
	 * enqueue_styles function.
	 * 
	 * @access public
	 * @since 1.0.1
	 * @return void
	 */
	public function enqueue_styles () {
		wp_register_style( 'laboratory-social-widgets', $this->assets_url . 'css/style.css' );
		wp_enqueue_style( 'laboratory-social-widgets' );
	} // End enqueue_styles()
} // End Class
?>