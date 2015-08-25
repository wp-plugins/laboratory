<?php
/**
** A base module for the following types of tags:
** 	[text] and [text*]		# Single-line text
** 	[email] and [email*]	# Email address
** 	[url] and [url*]		# URL
** 	[tel] and [tel*]		# Telephone number
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_text', 5 );

function colabs7_add_shortcode_text() {
	colabs7_add_shortcode(
		array( 'text', 'text*', 'email', 'email*', 'url', 'url*', 'tel', 'tel*' ),
		'colabs7_text_shortcode_handler', true );
}

function colabs7_text_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type, 'colabs7-text' );

	if ( in_array( $tag->basetype, array( 'email', 'url', 'tel' ) ) )
		$class .= ' colabs7-validates-as-' . $tag->basetype;

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	if ( $tag->has_option( 'readonly' ) )
		$atts['readonly'] = 'readonly';

	if ( $tag->is_required() )
		$atts['aria-required'] = 'true';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
	} elseif ( empty( $value ) && is_user_logged_in() ) {
		$user = wp_get_current_user();

		$user_options = array(
			'default:user_login' => 'user_login',
			'default:user_email' => 'user_email',
			'default:user_url' => 'user_url',
			'default:user_first_name' => 'first_name',
			'default:user_last_name' => 'last_name',
			'default:user_nickname' => 'nickname',
			'default:user_display_name' => 'display_name' );

		foreach ( $user_options as $option => $prop ) {
			if ( $tag->has_option( $option ) ) {
				$value = $user->{$prop};
				break;
			}
		}
	}

	if ( colabs7_is_posted() && isset( $_POST[$tag->name] ) )
		$value = stripslashes_deep( $_POST[$tag->name] );

	$atts['value'] = $value;

	if ( colabs7_support_html5() ) {
		$atts['type'] = $tag->basetype;
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;

	$atts = colabs7_format_atts( $atts );

	$html = sprintf(
		'<span class="colabs7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		$tag->name, $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'colabs7_validate_text', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_text*', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_email', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_email*', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_url', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_url*', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_tel', 'colabs7_text_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_tel*', 'colabs7_text_validation_filter', 10, 2 );

function colabs7_text_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( strtr( (string) $_POST[$name], "\n", " " ) )
		: '';

	if ( 'text*' == $tag->type ) {
		if ( '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		}
	}

	if ( 'email' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		} elseif ( '' != $value && ! colabs7_is_email( $value ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_email' );
		}
	}

	if ( 'url' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		} elseif ( '' != $value && ! colabs7_is_url( $value ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_url' );
		}
	}

	if ( 'tel' == $tag->basetype ) {
		if ( $tag->is_required() && '' == $value ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		} elseif ( '' != $value && ! colabs7_is_tel( $value ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_tel' );
		}
	}

	return $result;
}


/* Messages */

add_filter( 'colabs7_messages', 'colabs7_text_messages' );

function colabs7_text_messages( $messages ) {
	return array_merge( $messages, array(
		'invalid_email' => array(
			'description' => __( "Email address that the sender entered is invalid", 'colabs7' ),
			'default' => __( 'Email address seems invalid.', 'colabs7' )
		),

		'invalid_url' => array(
			'description' => __( "URL that the sender entered is invalid", 'colabs7' ),
			'default' => __( 'URL seems invalid.', 'colabs7' )
		),

		'invalid_tel' => array(
			'description' => __( "Telephone number that the sender entered is invalid", 'colabs7' ),
			'default' => __( 'Telephone number seems invalid.', 'colabs7' )
		) ) );
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_text', 15 );

function colabs7_add_tag_generator_text() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'text', __( 'Text field', 'colabs7' ),
		'colabs7-tg-pane-text', 'colabs7_tg_pane_text' );

	colabs7_add_tag_generator( 'email', __( 'Email', 'colabs7' ),
		'colabs7-tg-pane-email', 'colabs7_tg_pane_email' );

	colabs7_add_tag_generator( 'url', __( 'URL', 'colabs7' ),
		'colabs7-tg-pane-url', 'colabs7_tg_pane_url' );

	colabs7_add_tag_generator( 'tel', __( 'Telephone number', 'colabs7' ),
		'colabs7-tg-pane-tel', 'colabs7_tg_pane_tel' );
}

function colabs7_tg_pane_text( &$contact_form ) {
	colabs7_tg_pane_text_and_relatives( 'text' );
}

function colabs7_tg_pane_email( &$contact_form ) {
	colabs7_tg_pane_text_and_relatives( 'email' );
}

function colabs7_tg_pane_url( &$contact_form ) {
	colabs7_tg_pane_text_and_relatives( 'url' );
}

function colabs7_tg_pane_tel( &$contact_form ) {
	colabs7_tg_pane_text_and_relatives( 'tel' );
}

function colabs7_tg_pane_text_and_relatives( $type = 'text' ) {
	if ( ! in_array( $type, array( 'email', 'url', 'tel' ) ) )
		$type = 'text';

?>
<div id="colabs7-tg-pane-<?php echo $type; ?>" class="hidden">
<form action="">
<table>
<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'colabs7' ) ); ?></td></tr>
<tr><td><?php echo esc_html( __( 'Name', 'colabs7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
</table>

<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><code>size</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="size" class="numeric oneline option" min="1" /></td>

<td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="maxlength" class="numeric oneline option" min="1" /></td>
</tr>

<?php if ( in_array( $type, array( 'text', 'email', 'url' ) ) ) : ?>
<tr>
<td colspan="2"><?php echo esc_html( __( 'Akismet', 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<?php if ( 'text' == $type ) : ?>
<input type="checkbox" name="akismet:author" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's name", 'colabs7' ) ); ?><br />
<?php elseif ( 'email' == $type ) : ?>
<input type="checkbox" name="akismet:author_email" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's email address", 'colabs7' ) ); ?>
<?php elseif ( 'url' == $type ) : ?>
<input type="checkbox" name="akismet:author_url" class="option" />&nbsp;<?php echo esc_html( __( "This field requires author's URL", 'colabs7' ) ); ?>
<?php endif; ?>
</td>
</tr>
<?php endif; ?>

<tr>
<td><?php echo esc_html( __( 'Default value', 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br /><input type="text" name="values" class="oneline" /></td>

<td>
<br /><input type="checkbox" name="placeholder" class="option" />&nbsp;<?php echo esc_html( __( 'Use this text as placeholder?', 'colabs7' ) ); ?>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?><br /><input type="text" name="<?php echo $type; ?>" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'colabs7' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>