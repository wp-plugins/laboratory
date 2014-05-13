<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Pinterest Widget
 *
 * A bundled Laboratory Pinterest stream widget.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Bundled
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * var $laboratory_widget_cssclass
 * var $laboratory_widget_description
 * var $laboratory_widget_idbase
 * var $laboratory_widget_title
 * 
 * var $transient_expire_time
 * 
 * - __construct()
 * - widget()
 * - update()
 * - form()
 * - get_stored_data()
 * - request_tweets()
 * - enqueue_styles()
 */
class Laboratory_Widget_Pinterest extends WP_Widget {

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
		$this->laboratory_widget_cssclass = 'widget_laboratory_pinterest';
		$this->laboratory_widget_description = __( 'This is a Laboratory bundled pinterest widget.', 'laboratory' );
		$this->laboratory_widget_idbase = 'laboratory_pinterest';
		$this->laboratory_widget_title = __('Laboratory - Pinterest', 'laboratory' );
		
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

		extract( $args, EXTR_SKIP );
		
		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'], $instance, $this->id_base );
		$title = ( $title ) ? $title : 'Latest Pins on Pinterest';
		
		if( !empty( $instance['pinterest_username'] ) ) {		
		//determine how many pins they want to display and pull from rss feed
		if ( !empty( $instance['number_of_pins_to_show'] )  && is_int( $instance['number_of_pins_to_show'] ) ) {
			$number_of_pins_to_show = esc_attr ( $instance['number_of_pins_to_show'] );
		} else {
			$number_of_pins_to_show = 3;
		}
		if( !empty( $instance['specific_board'] ) ) {	
			$feed_url = 'http://pinterest.com/'.$instance['pinterest_username'].'/'.$instance['specific_board'].'/rss';
		} else {
			$feed_url = 'http://pinterest.com/'.$instance['pinterest_username'].'/feed.rss';	
		}
		
		//fetch rss
		$latest_pins = $this->laboratory_pinterest_get_rss_feed( $instance['pinterest_username'], $instance['number_of_pins_to_show'], $feed_url );
		}
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
		
			echo $before_title . $title . $after_title;
		
		} // End IF Statement
		
		/* Widget content. */
		?>
		<style>
		ul#laboratory-pinterest-widget{
			list-style-type: none;
		}	
		
		ul#laboratory-pinterest-widget li.laboratory-pinterest div.laboratory-pinterest-image img{
			width: 100%;
			display: block;
		}		
		
		ul#laboratory-pinterest-widget li.laboratory-pinterest span{
			display: block;
			background-color: #F2F0F0;
		}
		
		ul#laboratory-pinterest-widget li.laboratory-pinterest span p{
			padding: 4px;
			text-align: center;
		}
		</style>		
		<ul id="laboratory-pinterest-widget">			
		<?php 
			if(!empty( $latest_pins ) ){
				foreach ( $latest_pins as $item ):
					$rss_pin_description = $item->get_description();			
					preg_match('/<img[^>]+>/i', $rss_pin_description, $pin_image); 
					$pin_caption = $this->trim_text( strip_tags( $rss_pin_description ), 400 );
					?>
				<li class="laboratory-pinterest">
					<div class="laboratory-pinterest-image">
						<a href="<?php echo esc_url( $item->get_permalink() ); ?>" title="<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>"><?php echo $pin_image[0];?></a>
						<?php if ( $instance['show_pinterest_caption'] ){?>
						<span><p><?php echo strip_tags( $pin_caption ); ?></p></span>
						<?php }?>
					</div>
				</li>
				<?php endforeach; 
			}
			if( $instance['show_follow_button'] ){
			?>
			<li class="laboratory-pinterest-follow-me"><a href="http://pinterest.com/<?php echo $instance['pinterest_username'];?>/" target="_blank"><strong><?php _e('Follow me on','colabsthemes');?></strong>&nbsp;<img src="<?php echo get_stylesheet_directory_uri().'/images/pinterest.png'; ?>" width="80" height="20" alt="Follow Me on Pinterest" /></a></li>
			<?php
			}
			?>		
		</ul>				
		<?php		
		
		
		// Add actions for plugins/themes to hook onto.
		do_action( $this->laboratory_widget_cssclass . '_top' );
		
		// Load widget content here.
		
		
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
		$instance = wp_parse_args( $old_instance, $new_instance );
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['number_of_pins_to_show'] = strip_tags($new_instance['number_of_pins_to_show']);
		$instance['pinterest_username'] = strip_tags($new_instance['pinterest_username']);
		$instance['specific_board'] = strip_tags($new_instance['specific_board']);
		$instance['show_pinterest_caption'] = strip_tags($new_instance['show_pinterest_caption']);
		$instance['show_follow_button'] = strip_tags($new_instance['show_follow_button']);
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
		$instance = wp_parse_args( (array) $instance, array( 'title' => 'Latest Pins on Pinterest', 'pinterest_username' => '', 'number_of_pins_to_show' => '3', 'show_pinterest_caption' => '1', 'show_follow_button' => '1', 'specific_board' => '') );
		if ( $instance ) {
			$title = esc_attr( $instance[ 'title' ] );
			$number_of_pins_to_show = esc_attr( $instance[ 'number_of_pins_to_show' ] );
			$pinterest_username = esc_attr( $instance[ 'pinterest_username' ] );
			$specific_board = esc_attr( $instance[ 'specific_board' ] );	
			$show_pinterest_caption = esc_attr( $instance[ 'show_pinterest_caption' ] );
			$show_follow_button = esc_attr( $instance[ 'show_follow_button' ] );			
		}		
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('pinterest_username'); ?>"><?php _e('Pinterest Username:'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('pinterest_username'); ?>" name="<?php echo $this->get_field_name('pinterest_username'); ?>" type="text" value="<?php echo $pinterest_username; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('specific_board'); ?>"><?php _e('Specific Board (optional):'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('specific_board'); ?>" name="<?php echo $this->get_field_name('specific_board'); ?>" type="text" value="<?php echo $specific_board; ?>" />
		</p>		
		<p>
		<label for="<?php echo $this->get_field_id('number_of_pins_to_show'); ?>"><?php _e('Number of Pins To Show:'); ?></label>		
		<select name="<?php echo $this->get_field_name( 'number_of_pins_to_show' );?>">
		<?php 
		for ( $i = 1; $i <= 25; ++$i ){?>
			<option value="<?php echo $i;?>" <?php selected( $number_of_pins_to_show, $i );?>><?php echo $i;?></option>
		<?php
		}
		?>		
		</select>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('show_pinterest_caption'); ?>"><?php _e('Show Caption?:'); ?></label>
		<input type="checkbox" name="<?php echo $this->get_field_name('show_pinterest_caption')?>" value="1" <?php checked( $show_pinterest_caption, 1 ); ?> />	
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('show_follow_button'); ?>"><?php _e('Show "Follow Me" Button?:'); ?></label>
		<input type="checkbox" name="<?php echo $this->get_field_name('show_follow_button')?>" value="1" <?php checked( $show_follow_button, 1 ); ?> />	
		</p>
		
		<?php
		
		// Allow child themes/plugins to act here.
		do_action( $this->laboratory_widget_idbase . '_widget_settings', $instance, $this );

	} // End form()
	/**
	 * Retrieve stored data, or query for new data.
	 * @param  array $args
	 * @return array
	 */
	public function laboratory_pinterest_get_rss_feed( $pinterest_username, $number_of_pins_to_show, $feed_url ){				
		// Get a SimplePie feed object from the specified feed source.		
		$rss = fetch_feed( $feed_url );
		if (!is_wp_error( $rss ) ) : 
			// Figure out how many total items there are, but limit it to number specified
			$maxitems = $rss->get_item_quantity( $number_of_pins_to_show ); 
			$rss_items = $rss->get_items( 0, $maxitems ); 
		endif;		
		return $rss_items;
	}

	/**
	 * Retrieve tweets for a specified username.
	 * @param  array $args
	 * @return array
	 */
	public function trim_text( $text, $length ) {
		//strip html
		$text = strip_tags( $text );	  
		//no need to trim if its shorter than length
		if (strlen($text) <= $length) {
			return $text;
		}		
		$last_space = strrpos( substr( $text, 0, $length ), ' ');
		$trimmed_text = substr( $text, 0, $last_space );		
		$trimmed_text .= '...';	  
		return $trimmed_text;
	}

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