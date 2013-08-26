<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) )
	die( '-1' );

?><div class="wrap">

<?php screen_icon(); ?>

<h2><?php
	echo esc_html( __( 'Colabs Contact Form', 'colabs7' ) );

	if ( ! $post->initial ) {
		echo ' <a href="#TB_inline?height=300&width=400&inlineId=colabs7-lang-select-modal" class="add-new-h2 thickbox">' . esc_html( __( 'Add New', 'colabs7' ) ) . '</a>';
	}
?></h2>

<?php do_action( 'colabs7_admin_notices' ); ?>

<br class="clear" />

<?php
if ( $post ) :

	if ( current_user_can( 'colabs7_edit_contact_form', $post_id ) )
		$disabled = '';
	else
		$disabled = ' disabled="disabled"';
?>

<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( 'colabs7', false ) ) ); ?>" id="colabs7-admin-form-element"<?php do_action( 'colabs7_post_edit_form_tag' ); ?>>
	<?php if ( current_user_can( 'colabs7_edit_contact_form', $post_id ) )
		wp_nonce_field( 'colabs7-save-contact-form_' . $post_id ); ?>
	<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
	<input type="hidden" id="colabs7-id" name="colabs7-id" value="<?php echo (int) get_post_meta( $post->id, '_old_cf7_unit_id', true ); ?>" />
	<input type="hidden" id="colabs7-locale" name="colabs7-locale" value="<?php echo esc_attr( $post->locale ); ?>" />
	<input type="hidden" id="hiddenaction" name="action" value="save" />

	<div id="poststuff" class="metabox-holder">

	<div id="titlediv">
		<input type="text" id="colabs7-title" name="colabs7-title" size="40" value="<?php echo esc_attr( $post->title ); ?>"<?php echo $disabled; ?> />

		<?php if ( ! $post->initial ) : ?>
		<p class="tagcode">
			<?php echo esc_html( __( "Copy this code and paste it into your post, page or text widget content.", 'colabs7' ) ); ?><br />

			<input type="text" id="contact-form-anchor-text" onfocus="this.select();" readonly="readonly" />
		</p>

		<p class="tagcode" style="display: none;">
			<?php echo esc_html( __( "Old code is also available.", 'colabs7' ) ); ?><br />

			<input type="text" id="contact-form-anchor-text-old" onfocus="this.select();" readonly="readonly" />
		</p>
		<?php endif; ?>

		<?php if ( current_user_can( 'colabs7_edit_contact_form', $post_id ) ) : ?>
		<div class="save-contact-form">
			<input type="submit" class="button-primary" name="colabs7-save" value="<?php echo esc_attr( __( 'Save', 'colabs7' ) ); ?>" />
		</div>
		<?php endif; ?>

		<?php if ( current_user_can( 'colabs7_edit_contact_form', $post_id ) && ! $post->initial ) : ?>
		<div class="actions-link">
			<?php $copy_nonce = wp_create_nonce( 'colabs7-copy-contact-form_' . $post_id ); ?>
			<input type="submit" name="colabs7-copy" class="copy" value="<?php echo esc_attr( __( 'Copy', 'colabs7' ) ); ?>"
			<?php echo "onclick=\"this.form._wpnonce.value = '$copy_nonce'; this.form.action.value = 'copy'; return true;\""; ?> />
			|

			<?php $delete_nonce = wp_create_nonce( 'colabs7-delete-contact-form_' . $post_id ); ?>
			<input type="submit" name="colabs7-delete" class="delete" value="<?php echo esc_attr( __( 'Delete', 'colabs7' ) ); ?>"
			<?php echo "onclick=\"if (confirm('" .
				esc_js( __( "You are about to delete this contact form.\n  'Cancel' to stop, 'OK' to delete.", 'colabs7' ) ) .
				"')) {this.form._wpnonce.value = '$delete_nonce'; this.form.action.value = 'delete'; return true;} return false;\""; ?> />
		</div>
		<?php endif; ?>
	</div>

<?php

do_action_ref_array( 'colabs7_admin_after_general_settings', array( &$post ) );

do_meta_boxes( null, 'form', $post );

do_action_ref_array( 'colabs7_admin_after_form', array( &$post ) );

do_meta_boxes( null, 'mail', $post );

do_action_ref_array( 'colabs7_admin_after_mail', array( &$post ) );

do_meta_boxes( null, 'mail_2', $post );

do_action_ref_array( 'colabs7_admin_after_mail_2', array( &$post ) );

do_meta_boxes( null, 'messages', $post );

do_action_ref_array( 'colabs7_admin_after_messages', array( &$post ) );

do_meta_boxes( null, 'additional_settings', $post );

do_action_ref_array( 'colabs7_admin_after_additional_settings', array( &$post ) );

wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );

?>
	</div>

</form>

<?php endif; ?>

</div>

<?php colabs7_admin_lang_select_modal(); ?>

<?php do_action_ref_array( 'colabs7_admin_footer', array( &$post ) ); ?>
