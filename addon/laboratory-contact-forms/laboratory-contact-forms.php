<?php
/**
 * Addon Name: Contact Forms
 * Addon Description: The popular for custom contact forms.
 * Addon Version: 1.0.0
 *
 * @package Laboratory
 * @subpackage Addon
 * @author colabs
 * @since 1.0.1
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

if ( ! defined( '_COLABS_PLUGIN_DIR' ) )
	define( '_COLABS_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	
require_once (_COLABS_PLUGIN_DIR . '/colabs-contact-form.php');
require_once (_COLABS_PLUGIN_DIR . '/addon/crm/crm.php');
require_once (_COLABS_PLUGIN_DIR . '/addon/colabs-simple-captcha/colabs-simple-captcha.php');


	
add_action( "media_buttons", "laboratory_media_button", 29 );
function laboratory_media_button() {
	if( in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) {
	  echo '<a href="'. '#TB_inline?width=400&inlineId=popup_contactform' .'" class="button thickbox add-contactform" id="add-contactform" title="' . esc_attr__( 'Insert contact form', 'laboratory' ) . '" onclick="return false;">'. __('Insert Contact Form', 'laboratory') .'</a>';
	}
}
add_action('admin_footer', 'add_inline_popup_contactform');
function add_inline_popup_contactform() {
	if( !in_array( basename( $_SERVER['PHP_SELF'] ), array( 'post-new.php', 'page-new.php', 'post.php', 'page.php' ) ) ) return;
	?>
	<div id="popup_contactform" style="display:none;">
		<?php
		$args = array(
			'post_type' => 'colabs7_contact_form'
		);
		$the_query = new WP_Query( $args ); 
		// The Loop
		if ( $the_query->have_posts() ) {
		?>
		<table class="widefat fixed posts" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="title" class="manage-column column-title sortable asc" style="">
						<?php _e("Title","colabsthemes"); ?>
					</th>
					<th scope="col" id="shortcode" class="manage-column column-shortcode" style="">
						<?php _e("Shortcode","colabsthemes"); ?>
					</th>	
				</tr>
			</thead>

			<tbody id="the-list" data-wp-lists="list:post">
				
		<?php
			while ( $the_query->have_posts() ) {
				$the_query->the_post();
				echo '
				<tr class="alternate">
					<td class="title column-title">
						<strong>'. get_the_title() . '</strong> 
					</td>
					<td class="shortcode column-shortcode">
						<input type="text" id="contact_form'.get_the_ID().'" onfocus="this.select();" readonly="readonly" value="[colabs-contact-form id=&quot;'.get_the_ID().'&quot; title=&quot;'.get_the_title().'&quot;]" class="shortcode-in-list-table">
					</td>
				</tr>
				<script type="text/javascript">
				jQuery(document).ready(function(){
				   jQuery("#contact_form'.get_the_ID().'").click(function() {
								  send_to_editor(jQuery("#contact_form'.get_the_ID().'").val());
								  return false;
						});
				});
				</script>
				';
			}
		?>			
			</tbody>
		</table>
		
		<?php			
		} else {
			// no posts found
		}
		/* Restore original Post Data */
		wp_reset_postdata();
		?>
	</div>
	<?php
}

?>