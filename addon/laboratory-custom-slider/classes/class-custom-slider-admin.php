<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * CustomSlider Administration Class
 *
 * All functionality pertaining to the administration sections of CustomSlider.
 *
 * @package WordPress
 * @subpackage CustomSlider
 * @category Administration
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - __construct()
 * - admin_styles_global()
 * - add_media_tab()
 * - media_tab_handle()
 * - media_tab_process()
 * - media_tab_js()
 * - popup_fields()
 * - add_default_conditional_fields()
 * - conditional_fields_attachments()
 * - conditional_fields_posts()
 * - conditional_fields_slides()
 * - generate_field_by_type()
 * - generate_default_conditional_fields()
 * - generate_conditional_fields_slides()
 * - generate_conditional_fields_posts()
 */
class CustomSlider_Admin extends Laboratory_Admin {
	/**
	 * Constructor.
	 * @since  1.0.0
	 * @return  void
	 */
	public function __construct () {
		global $pagenow;

		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_styles_global' ) );
		add_filter( 'media_upload_tabs', array( &$this, 'add_media_tab' ) );
		add_action( 'media_upload_laboratory_slideshow', array( &$this, 'media_tab_handle' ) );

		if ( $pagenow == 'media-upload.php' ) {
			add_action( 'admin_print_scripts', array( &$this, 'media_tab_js' ) );
			add_action( 'laboratory_slideshow_popup_conditional_fields', array( &$this, 'add_default_conditional_fields' ) );
		}

	} // End __construct()

	/**
	 * Load the global admin styles for the menu icon and the relevant page icon.
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_styles_global () {
		global $laboratory_slideshow;
		wp_enqueue_style( $laboratory_slideshow->token . '-global' );
	} // End admin_styles_global()

	/**
	 * Filter the "Add Media" popup's tabs, to add our own.
	 * @since  1.0.0
	 * @param array $tabs The existing array of tabs.
	 */
	public function add_media_tab ( $tabs ) {
		$tabs['laboratory_slideshow'] = __( 'Laboratory Slide', 'laboratory' );
		return $tabs;
	} // End add_media_tab()

	/**
	 * Display the tab content in a WordPress iframe.
	 * @since  1.0.0
	 * @return void
	 */
	public function media_tab_handle () {
		wp_iframe( array( &$this, 'media_tab_process' ) );
	} // End media_tab_handle()

	/**
	 * Create the tab content to be displayed.
	 * @since  1.0.0
	 * @uses  global $laboratory_slideshow Global $laboratory_slideshow object
	 * @return void
	 */
	public function media_tab_process () {
		global $laboratory_slideshow;
		media_upload_header();
		$laboratory_slideshow->post_types->setup_slide_pages_taxonomy();
?>
<form action="media-new.php" method="post" id="laboratory_slideshow-insert">
	<?php submit_button( __( 'Insert Slideshow', 'laboratory' ) ); ?>
	<?php $this->popup_fields(); ?>
	<p class="hide-if-no-js"><a href="#advanced-settings" class="advanced-settings button"><?php _e( 'Advanced Settings', 'laboratory' ); ?></a></p>
	<div id="laboratory_slideshow-advanced-settings">
		<div class="updated fade"><p><?php _e( 'Optionally override the default slideshow settings using the fields below.', 'laboratory' ); ?></p></div>
		<?php settings_fields( $laboratory_slideshow->settings->token ); ?>
		<?php do_settings_sections( $laboratory_slideshow->settings->token ); ?>
	</div><!--/#laboratory_slideshow-advanced-settings-->
	<?php submit_button( __( 'Insert Slideshow', 'laboratory' ) ); ?>
</form>
<?php
	} // End media_tab_process()

	/**
	 * Load the JavaScript to handle the media tab in the "Add Media" popup.
	 * @since  1.0.0
	 * @return void
	 */
	public function media_tab_js () {
		global $laboratory_slideshow;

		$laboratory_slideshow->settings->enqueue_field_styles();

		$laboratory_slideshow->settings->enqueue_scripts();

		wp_enqueue_script( 'laboratory_slideshow-settings-ranges' );
		wp_enqueue_script( 'laboratory_slideshow-settings-imageselectors' );

		wp_enqueue_style( 'laboratory_slideshow-settings-ranges' );
		wp_enqueue_style( 'laboratory_slideshow-settings-imageselectors' );

		wp_register_script( $laboratory_slideshow->token . '-media-tab', esc_url( $laboratory_slideshow->plugin_url . 'assets/js/shortcode-creator.js' ), '1.0.2', '', true );
		wp_enqueue_script( $laboratory_slideshow->token . '-media-tab' );

		$settings = $laboratory_slideshow->settings->get_settings();

		// Allow themes/plugins to filter here.
		$settings['category'] = '';
		$settings['tag'] = '';
		$settings['slide_page'] = '';
		$settings['slidetype'] = '';
		$settings['layout'] = '';
		$settings['overlay'] = '';
		$settings['limit'] = '5';
		$settings['thumbnails'] = '';
		$settings['link_title'] = '';
		$settings['display_excerpt'] = '1';
		$settings = (array)apply_filters( 'laboratory_slideshow_popup_settings', $settings );

		wp_localize_script( $laboratory_slideshow->token . '-media-tab', $laboratory_slideshow->token . '_settings', $settings );
	} // End media_tab_js()

	/**
	 * Fields specific to the "Add Media" popup.
	 * @since  1.0.0
	 * @return void
	 */
	public function popup_fields () {
		$types = CustomSlider_Utils::get_slider_types();

	    $slider_types = array();
	    foreach ( (array)$types as $k => $v ) {
	    	$slider_types[$k] = $v['name'];
	    }
?>
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><?php _e( 'Slideshow Type', 'laboratory' ); ?></th>
				<td><select id="slidetype" name="laboratory_slideshow-settings[slidetype]">
					<?php
						foreach ( (array)$slider_types as $k => $v ) {
							echo '<option value="' . esc_attr( $k ) . '">' . $v . '</option>' . "\n";
						}
					?>
					</select>
					<p><span class="description"><?php _e( 'The type of slideshow to insert', 'laboratory' ); ?></span></p>
				</td>
			</tr>
		</tbody>
	</table>
<?php
		// Allow themes/plugins to act here.
		do_action( 'laboratory_slideshow_popup_conditional_fields', $types );
	} // End popup_fields()

	/**
	 * Setup the conditional fields for the default slider types.
	 * @since  1.0.0
	 * @param  array $types The supported slideshow types.
	 * @return void
	 */
	public function add_default_conditional_fields ( $types ) {
		foreach ( (array)$types as $k => $v ) {
			if ( method_exists( $this, 'conditional_fields_' . $k ) ) {
				echo '<div class="conditional conditional-' . esc_attr( $k ) . '">' . "\n";
				$this->{'conditional_fields_' . $k}();
				echo '</div>' . "\n";
			}
		}
	} // End add_default_conditional_fields()

	/**
	 * Conditional fields, displayed only for the "attachments" slideshow type.
	 * @since  1.0.0
	 * @return void
	 */
	private function conditional_fields_attachments () {
		global $laboratory_slideshow;

		$fields = $this->generate_conditional_fields_attachments();
?>
	<table class="form-table">
		<tbody>
<?php foreach ( $fields as $k => $v ) { ?>
			<tr valign="top">
				<th scope="row"><?php echo $v['name']; ?></th>
				<td>
					<?php $this->generate_field_by_type( $v['type'], $v['args'] ); ?>
					<?php if ( $v['description'] != '' ) { ?><p><span class="description"><?php echo $v['description']; ?></span></p><?php } ?>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
<?php
	} // End conditional_fields_attachments()

	/**
	 * Conditional fields, displayed only for the "posts" slideshow type.
	 * @since  1.0.0
	 * @return void
	 */
	private function conditional_fields_posts () {
		$fields = $this->generate_conditional_fields_posts();
?>
	<table class="form-table">
		<tbody>
<?php foreach ( $fields as $k => $v ) { ?>
			<tr valign="top">
				<th scope="row"><?php echo $v['name']; ?></th>
				<td>
					<?php $this->generate_field_by_type( $v['type'], $v['args'] ); ?>
					<?php if ( $v['description'] != '' ) { ?><p><span class="description"><?php echo $v['description']; ?></span></p><?php } ?>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
<?php
	} // End conditional_fields_posts()

	/**
	 * Conditional fields, displayed only for the "slides" slideshow type.
	 * @since  1.0.0
	 * @return void
	 */
	private function conditional_fields_slides () {
		global $laboratory_slideshow;

		$fields = $this->generate_conditional_fields_slides();
?>
	<table class="form-table">
		<tbody>
<?php foreach ( $fields as $k => $v ) { ?>
			<tr valign="top">
				<th scope="row"><?php echo $v['name']; ?></th>
				<td>
					<?php $this->generate_field_by_type( $v['type'], $v['args'] ); ?>
					<?php if ( $v['description'] != '' ) { ?><p><span class="description"><?php echo $v['description']; ?></span></p><?php } ?>
				</td>
			</tr>
<?php } ?>
		</tbody>
	</table>
<?php
	} // End conditional_fields_slides()

	/**
	 * Generate a field from the settings API based on a provided field type.
	 * @since  1.0.0
	 * @param  string $type The type of field to generate.
	 * @param  array $args Arguments to be passed to the field.
	 * @return void
	 */
	public function generate_field_by_type ( $type, $args ) {
		if ( is_array( $args ) && isset( $args['key'] ) && isset( $args['data'] ) ) {
			global $laboratory_slideshow;
			$default = '';
			if ( isset( $args['data']['default'] ) ) { $default = $args['data']['default']; }

			switch ( $type ) {
				// Text fields.
				case 'text':
					$html = '<input type="text" name="' . esc_attr( $args['key'] ) . '" id="' . esc_attr( $args['key'] ) . '" value="' . esc_attr( $default ) . '" />' . "\n";

					echo $html;
				break;

				// Select fields.
				case 'select':
					$html = '<select name="' . esc_attr( $args['key'] ) . '" id="' . esc_attr( $args['key'] ) . '">' . "\n";
					foreach ( $args['data']['options'] as $k => $v ) {
						$html .= '<option value="' . esc_attr( $k ) . '"' . selected( $k, $default, false ) . '>' . $v . '</option>' . "\n";
					}
					$html .= '</select>' . "\n";

					echo $html;
				break;

				// Single checkbox.
				case 'checkbox':
					$checked = checked($default, 'true', false) ;
					$html = '<input type="checkbox" id="' . $args['key'] . '" name="' . $args['key'] . '" class="checkbox checkbox-' . esc_attr( $args['key'] ) . '" value="true"' . $checked . ' /> ' . "\n";
					echo $html;

				break;

				// Multiple checkboxes.
				case 'multicheck':
				if ( isset( $args['data']['options'] ) && ( count( (array)$args['data']['options'] ) > 0 ) ) {
					$html = '<div class="multicheck-container" style="height: 100px; overflow-y: auto;">' . "\n";
					foreach ( $args['data']['options'] as $k => $v ) {
						$checked = '';
						$html .= '<input type="checkbox" name="' . $args['key'] . '[]" class="multicheck multicheck-' . esc_attr( $args['key'] ) . '" value="' . esc_attr( $k ) . '"' . $checked . ' /> ' . $v . '<br />' . "\n";
					}
					$html .= '</div>' . "\n";
					echo $html;
				}

				break;

				// Image selectors.
				case 'images':
				if ( isset( $args['data']['options'] ) && ( count( (array)$args['data']['options'] ) > 0 ) ) {
					$html = '';
					foreach ( $args['data']['options'] as $k => $v ) {
						$image_url = $laboratory_slideshow->plugin_url . '/assets/images/default.png';
						if ( isset( $args['data']['images'][$k] ) ) {
							$image_url = $args['data']['images'][$k];
						}
						$image = '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $v ) . '" title="' . esc_attr( $v ) . '" class="radio-image-thumb" />';
						$html .= '<input type="radio" name="' . $args['key'] . '" value="' . esc_attr( $k ) . '" class="radio-images" /> ' . $image . "\n";
					}
					echo $html;
				}
				break;
			}
		}
	} // End generate_field_by_type()

	/**
	 * Generate an array of the conditional fields for the default slider types.
	 * @since  1.0.0
	 * @param  array $types The supported slideshow types.
	 * @return array $fields.
	 */
	public function generate_default_conditional_fields ( $types ) {
		$fields = array();
		foreach ( (array)$types as $k => $v ) {
			if ( method_exists( $this, 'generate_conditional_fields_' . $k ) ) {
				$fields[$k] = (array)$this->{'generate_conditional_fields_' . $k}();
			}
		}

		return $fields;
	} // End generate_default_conditional_fields()

	/**
	 * Generate conditional fields for the "attachments" slideshow type.
	 * @since  1.0.0
	 * @return array $fields An array of fields.
	 */
	private function generate_conditional_fields_attachments () {
		$fields = array();

		$limit_options = array();
		for ( $i = 1; $i <= 20; $i++ ) {
			$limit_options[$i] = $i;
		}
		$limit_args = array( 'key' => 'limit', 'data' => array( 'options' => $limit_options, 'default' => 5 ) );
		$thumbnails_args = array( 'key' => 'thumbnails', 'data' => array() );

		// Create final array.
		$fields['limit'] = array( 'name' => __( 'Number of Images', 'laboratory' ), 'type' => 'select', 'args' => $limit_args, 'description' => __( 'The maximum number of images to display', 'laboratory' ) );
		$fields['thumbnails'] = array( 'name' => __( 'Use thumbnails for Pagination', 'laboratory' ), 'type' => 'checkbox', 'args' => $thumbnails_args, 'description' => __( 'Use thumbnails for pagination, instead of "dot" indicators', 'laboratory' ) );

		return $fields;
	} // End generate_conditional_fields_attachments()

	/**
	 * Generate conditional fields for the "slides" slideshow type.
	 * @since  1.0.0
	 * @return array $fields An array of fields.
	 */
	private function generate_conditional_fields_slides () {
		$fields = array();

		// Categories.
		$terms = get_terms( 'slide-page' );
		$terms_options = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $k => $v ) {
				$terms_options[$v->slug] = $v->name;
			}
		}

		$categories_args = array( 'key' => 'slide_page', 'data' => array( 'options' => $terms_options ) );

		$limit_options = array();
		for ( $i = 1; $i <= 20; $i++ ) {
			$limit_options[$i] = $i;
		}
		$limit_args = array( 'key' => 'limit', 'data' => array( 'options' => $limit_options, 'default' => 5 ) );
		$thumbnails_args = array( 'key' => 'thumbnails', 'data' => array() );
		$display_featured_image_args = array( 'key' => 'display_featured_image', 'data' => array() );

		// Create final array.
		$fields['limit'] = array( 'name' => __( 'Number of Slides', 'laboratory' ), 'type' => 'select', 'args' => $limit_args, 'description' => __( 'The maximum number of slides to display', 'laboratory' ) );
		$fields['slide_page'] = array( 'name' => __( 'Slide Pages', 'laboratory' ), 'type' => 'multicheck', 'args' => $categories_args, 'description' => __( 'The slide pages from which to display slides', 'laboratory' ) );
		$fields['thumbnails'] = array( 'name' => __( 'Use thumbnails for Pagination', 'laboratory' ), 'type' => 'checkbox', 'args' => $thumbnails_args, 'description' => __( 'Use thumbnails for pagination, instead of "dot" indicators (uses featured image)', 'laboratory' ) );

		return $fields;
	} // End generate_conditional_fields_slides()

	/**
	 * Generate conditional fields for the "posts" slideshow type.
	 * @since  1.0.0
	 * @return array $fields An array of fields.
	 */
	private function generate_conditional_fields_posts () {
		global $laboratory_slideshow;

		$images_url = $laboratory_slideshow->plugin_url . '/assets/images/';
		$fields = array();

		// Categories.
		$terms = get_categories();
		$terms_options = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $k => $v ) {
				$terms_options[$v->slug] = $v->name;
			}
		}

		$categories_args = array( 'key' => 'category', 'data' => array( 'options' => $terms_options ) );

		// Tags.
		$terms = get_tags();
		$terms_options = array();
		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $k => $v ) {
				$terms_options[$v->slug] = $v->name;
			}
		}

		$tags_args = array( 'key' => 'tag', 'data' => array( 'options' => $terms_options ) );

		$layout_types = CustomSlider_Utils::get_posts_layout_types();
		$layout_options = array();

		foreach ( (array)$layout_types as $k => $v ) {
			$layout_options[$k] = $v['name'];
		}

		$layout_images = array(
								'text-left' => esc_url( $images_url . 'text-left.png' ), 
								'text-right' => esc_url( $images_url . 'text-right.png' ), 
								'text-top' => esc_url( $images_url . 'text-top.png' ), 
								'text-bottom' => esc_url( $images_url . 'text-bottom.png' )
							);
		$layouts_args = array( 'key' => 'layout', 'data' => array( 'options' => $layout_options, 'images' => $layout_images ) );

		$overlay_images = array(
								'none' => esc_url( $images_url . 'default.png' ), 
								'full' => esc_url( $images_url . 'text-bottom.png' ), 
								'natural' => esc_url( $images_url . 'overlay-natural.png' )
							);

		$overlay_options = array( 'none' => __( 'None', 'laboratory' ), 'full' => __( 'Full', 'laboratory' ), 'natural' => __( 'Natural', 'laboratory' ) );

		$overlay_args = array( 'key' => 'overlay', 'data' => array( 'options' => $overlay_options, 'images' => $overlay_images ) );

		$limit_options = array();
		for ( $i = 1; $i <= 20; $i++ ) {
			$limit_options[$i] = $i;
		}
		$limit_args = array( 'key' => 'limit', 'data' => array( 'options' => $limit_options, 'default' => 5 ) );
		$thumbnails_args = array( 'key' => 'thumbnails', 'data' => array() );
		$link_title_args = array( 'key' => 'link_title', 'data' => array() );
		$display_excerpt_args = array( 'key' => 'display_excerpt', 'data' => array('default' => '1') );

		// Create final array.
		$fields['limit'] = array( 'name' => __( 'Number of Posts', 'laboratory' ), 'type' => 'select', 'args' => $limit_args, 'description' => __( 'The maximum number of posts to display', 'laboratory' ) );
		$fields['thumbnails'] = array( 'name' => __( 'Use thumbnails for Pagination', 'laboratory' ), 'type' => 'checkbox', 'args' => $thumbnails_args, 'description' => __( 'Use thumbnails for pagination, instead of "dot" indicators (uses featured image)', 'laboratory' ) );
		$fields['link_title'] = array( 'name' => __( 'Link the post title to it\'s post', 'laboratory' ), 'type' => 'checkbox', 'args' => $link_title_args, 'description' => __( 'Link the post title to it\'s single post screen', 'laboratory' ) );
		$fields['display_excerpt'] = array( 'name' => __( 'Display the post\'s excerpt', 'laboratory' ), 'type' => 'checkbox', 'args' => $display_excerpt_args, 'description' => __( 'Display the post\'s excerpt on each slide', 'laboratory' ) );
		$fields['layout'] = array( 'name' => __( 'Layout', 'laboratory' ), 'type' => 'images', 'args' => $layouts_args, 'description' => __( 'The layout to use when displaying posts', 'laboratory' ) );
		$fields['overlay'] = array( 'name' => __( 'Overlay', 'laboratory' ), 'type' => 'images', 'args' => $overlay_args, 'description' => __( 'The type of overlay to use when displaying the post text', 'laboratory' ) );
		$fields['category'] = array( 'name' => __( 'Categories', 'laboratory' ), 'type' => 'multicheck', 'args' => $categories_args, 'description' => __( 'The categories from which to display posts', 'laboratory' ) );
		$fields['tag'] = array( 'name' => __( 'Tags', 'laboratory' ), 'type' => 'multicheck', 'args' => $tags_args, 'description' => __( 'The tags from which to display posts', 'laboratory' ) );

		return $fields;
	} // End generate_conditional_fields_posts()
} // End Class
?>