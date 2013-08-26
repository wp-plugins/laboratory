<?php
/**
** A base module for the following types of tags:
** 	[number] and [number*]		# Number
** 	[range] and [range*]		# Range
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_number', 5 );

function colabs7_add_shortcode_number() {
	colabs7_add_shortcode( array( 'number', 'number*', 'range', 'range*' ),
		'colabs7_number_shortcode_handler', true );
}

function colabs7_number_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	$class .= ' colabs7-validates-as-number';

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
	$atts['min'] = $tag->get_option( 'min', 'signed_int', true );
	$atts['max'] = $tag->get_option( 'max', 'signed_int', true );
	$atts['step'] = $tag->get_option( 'step', 'int', true );

	if ( $tag->has_option( 'readonly' ) )
		$atts['readonly'] = 'readonly';

	if ( $tag->is_required() )
		$atts['aria-required'] = 'true';

	$value = (string) reset( $tag->values );

	if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
		$atts['placeholder'] = $value;
		$value = '';
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

add_filter( 'colabs7_validate_number', 'colabs7_number_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_number*', 'colabs7_number_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_range', 'colabs7_number_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_range*', 'colabs7_number_validation_filter', 10, 2 );

function colabs7_number_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( strtr( (string) $_POST[$name], "\n", " " ) )
		: '';

	$min = $tag->get_option( 'min', 'signed_int', true );
	$max = $tag->get_option( 'max', 'signed_int', true );

	if ( $tag->is_required() && '' == $value ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
	} elseif ( '' != $value && ! colabs7_is_number( $value ) ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'invalid_number' );
	} elseif ( '' != $value && '' != $min && (float) $value < (float) $min ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'number_too_small' );
	} elseif ( '' != $value && '' != $max && (float) $max < (float) $value ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'number_too_large' );
	}

	return $result;
}


/* Messages */

add_filter( 'colabs7_messages', 'colabs7_number_messages' );

function colabs7_number_messages( $messages ) {
	return array_merge( $messages, array(
		'invalid_number' => array(
			'description' => __( "Number format that the sender entered is invalid", 'colabs7' ),
			'default' => __( 'Number format seems invalid.', 'colabs7' )
		),

		'number_too_small' => array(
			'description' => __( "Number is smaller than minimum limit", 'colabs7' ),
			'default' => __( 'This number is too small.', 'colabs7' )
		),

		'number_too_large' => array(
			'description' => __( "Number is larger than maximum limit", 'colabs7' ),
			'default' => __( 'This number is too large.', 'colabs7' )
		) ) );
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_number', 18 );

function colabs7_add_tag_generator_number() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'number', __( 'Number (spinbox)', 'colabs7' ),
		'colabs7-tg-pane-number', 'colabs7_tg_pane_number' );

	colabs7_add_tag_generator( 'range', __( 'Number (slider)', 'colabs7' ),
		'colabs7-tg-pane-range', 'colabs7_tg_pane_range' );
}

function colabs7_tg_pane_number( &$contact_form ) {
	colabs7_tg_pane_number_and_relatives( 'number' );
}

function colabs7_tg_pane_range( &$contact_form ) {
	colabs7_tg_pane_number_and_relatives( 'range' );
}

function colabs7_tg_pane_number_and_relatives( $type = 'number' ) {
	if ( ! in_array( $type, array( 'range' ) ) )
		$type = 'number';

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
<td><code>min</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="min" class="numeric oneline option" /></td>

<td><code>max</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="max" class="numeric oneline option" /></td>
</tr>

<tr>
<td><code>step</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="step" class="numeric oneline option" min="1" /></td>
</tr>

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