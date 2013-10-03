<?php

/* Form */

function colabs7_form_meta_box( $post ) {
?>
<div class="half-left"><textarea id="colabs7-form" name="colabs7-form" cols="100" rows="24"><?php echo esc_textarea( $post->form ); ?></textarea></div>

<div class="half-right"><div id="taggenerator"></div></div>

<br class="clear" />
<?php
}

/* Mail */

function colabs7_mail_meta_box( $post, $box ) {
	$defaults = array( 'id' => 'colabs7-mail', 'name' => 'mail', 'use' => null );

	if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) )
		$args = array();
	else
		$args = $box['args'];

	extract( wp_parse_args( $args, $defaults ), EXTR_SKIP );

	$id = esc_attr( $id );
	$mail = $post->{$name};

	if ( ! empty( $use ) ) :
?>
<div class="mail-field">
<input type="checkbox" id="<?php echo $id; ?>-active" name="<?php echo $id; ?>-active" class="check-if-these-fields-are-active" value="1"<?php echo ( $mail['active'] ) ? ' checked="checked"' : ''; ?> />
<label for="<?php echo $id; ?>-active"><?php echo esc_html( $use ); ?></label>
<div class="pseudo-hr"></div>
</div>

<br class="clear" />
<?php endif; ?>

<div class="mail-fields">
<div class="half-left">
	<div class="mail-field">
	<label for="<?php echo $id; ?>-recipient"><?php echo esc_html( __( 'To:', 'colabs7' ) ); ?></label><br />
	<input type="text" id="<?php echo $id; ?>-recipient" name="<?php echo $id; ?>-recipient" class="wide" size="70" value="<?php echo esc_attr( $mail['recipient'] ); ?>" />
	</div>

	<div class="mail-field">
	<label for="<?php echo $id; ?>-sender"><?php echo esc_html( __( 'From:', 'colabs7' ) ); ?></label><br />
	<input type="text" id="<?php echo $id; ?>-sender" name="<?php echo $id; ?>-sender" class="wide" size="70" value="<?php echo esc_attr( $mail['sender'] ); ?>" />
	</div>

	<div class="mail-field">
	<label for="<?php echo $id; ?>-subject"><?php echo esc_html( __( 'Subject:', 'colabs7' ) ); ?></label><br />
	<input type="text" id="<?php echo $id; ?>-subject" name="<?php echo $id; ?>-subject" class="wide" size="70" value="<?php echo esc_attr( $mail['subject'] ); ?>" />
	</div>

	<div class="pseudo-hr"></div>

	<div class="mail-field">
	<label for="<?php echo $id; ?>-additional-headers"><?php echo esc_html( __( 'Additional headers:', 'colabs7' ) ); ?></label><br />
	<textarea id="<?php echo $id; ?>-additional-headers" name="<?php echo $id; ?>-additional-headers" cols="100" rows="2"><?php echo esc_textarea( $mail['additional_headers'] ); ?></textarea>
	</div>

	<div class="mail-field">
	<label for="<?php echo $id; ?>-attachments"><?php echo esc_html( __( 'File attachments:', 'colabs7' ) ); ?></label><br />
	<textarea id="<?php echo $id; ?>-attachments" name="<?php echo $id; ?>-attachments" cols="100" rows="2"><?php echo esc_textarea( $mail['attachments'] ); ?></textarea>
	</div>

	<div class="pseudo-hr"></div>

	<div class="mail-field">
	<input type="checkbox" id="<?php echo $id; ?>-use-html" name="<?php echo $id; ?>-use-html" value="1"<?php echo ( $mail['use_html'] ) ? ' checked="checked"' : ''; ?> />
	<label for="<?php echo $id; ?>-use-html"><?php echo esc_html( __( 'Use HTML content type', 'colabs7' ) ); ?></label>
	</div>
</div>

<div class="half-right">
	<div class="mail-field">
	<label for="<?php echo $id; ?>-body"><?php echo esc_html( __( 'Message body:', 'colabs7' ) ); ?></label><br />
	<textarea id="<?php echo $id; ?>-body" name="<?php echo $id; ?>-body" cols="100" rows="20"><?php echo esc_textarea( $mail['body'] ); ?></textarea>
	</div>
</div>

<br class="clear" />
</div>
<?php
}

function colabs7_messages_meta_box( $post ) {
	foreach ( colabs7_messages() as $key => $arr ) :
		$field_name = 'colabs7-message-' . strtr( $key, '_', '-' );

?>
<div class="message-field">
<label for="<?php echo $field_name; ?>"><em># <?php echo esc_html( $arr['description'] ); ?></em></label><br />
<input type="text" id="<?php echo $field_name; ?>" name="<?php echo $field_name; ?>" class="wide" size="70" value="<?php echo esc_attr( $post->messages[$key] ); ?>" />
</div>
<?php
	endforeach;
}

function colabs7_additional_settings_meta_box( $post ) {
?>
<textarea id="colabs7-additional-settings" name="colabs7-additional-settings" cols="100" rows="8"><?php echo esc_textarea( $post->additional_settings ); ?></textarea>
<?php
}

?>