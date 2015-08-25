<?php
/**
** A base module for [checkbox], [checkbox*], and [radio]
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_checkbox', 5 );

function colabs7_add_shortcode_checkbox() {
	colabs7_add_shortcode( array( 'checkbox', 'checkbox*', 'radio' ), 
		'colabs7_checkbox_shortcode_handler', true );
}

function colabs7_checkbox_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	$label_first = $tag->has_option( 'label_first' );
	$use_label_element = $tag->has_option( 'use_label_element' );
	$exclusive = $tag->has_option( 'exclusive' );
	$multiple = false;

	if ( 'checkbox' == $tag->basetype )
		$multiple = ! $exclusive;
	else // radio
		$exclusive = false;

	if ( $exclusive )
		$class .= ' colabs7-exclusive-checkbox';

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );

	$tabindex = $tag->get_option( 'tabindex', 'int', true );

	if ( false !== $tabindex )
		$tabindex = absint( $tabindex );

	$defaults = array();

	if ( $matches = $tag->get_first_match_option( '/^default:([0-9_]+)$/' ) )
		$defaults = explode( '_', $matches[1] );

	if ( isset( $_POST[$tag->name] ) )
		$post = $_POST[$tag->name];
	else
		$post = $multiple ? array() : '';

	$is_posted = colabs7_is_posted();

	$html = '';

	foreach ( (array) $tag->values as $key => $value ) {
		$checked = false;

		if ( $is_posted && ! empty( $post ) ) {
			if ( $multiple && in_array( esc_sql( $value ), (array) $post ) )
				$checked = true;
			if ( ! $multiple && $post == esc_sql( $value ) )
				$checked = true;
		} else {
			if ( in_array( $key + 1, (array) $defaults ) )
				$checked = true;
		}

		if ( isset( $tag->labels[$key] ) )
			$label = $tag->labels[$key];
		else
			$label = $value;

		$item_atts = array(
			'type' => $tag->basetype,
			'name' => $tag->name . ( $multiple ? '[]' : '' ),
			'value' => $value,
			'checked' => $checked ? 'checked' : '',
			'tabindex' => $tabindex ? $tabindex : '' );

		$item_atts = colabs7_format_atts( $item_atts );

		if ( $label_first ) { // put label first, input last
			$item = sprintf(
				'<span class="colabs7-list-item-label">%1$s</span>&nbsp;<input %2$s />',
				esc_html( $label ), $item_atts );
		} else {
			$item = sprintf(
				'<input %2$s />&nbsp;<span class="colabs7-list-item-label">%1$s</span>',
				esc_html( $label ), $item_atts );
		}

		if ( $use_label_element )
			$item = '<label>' . $item . '</label>';

		$item = '<span class="colabs7-list-item">' . $item . '</span>';
		$html .= $item;

		if ( false !== $tabindex )
			$tabindex += 1;
	}

	$atts = colabs7_format_atts( $atts );

	$html = sprintf(
		'<span class="colabs7-form-control-wrap %1$s"><span %2$s>%3$s</span>%4$s</span>',
		$tag->name, $atts, $html, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'colabs7_validate_checkbox', 'colabs7_checkbox_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_checkbox*', 'colabs7_checkbox_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_radio', 'colabs7_checkbox_validation_filter', 10, 2 );

function colabs7_checkbox_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$type = $tag->type;
	$name = $tag->name;

	$value = isset( $_POST[$name] ) ? (array) $_POST[$name] : array();

	if ( 'checkbox*' == $type ) {
		if ( empty( $value ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_checkbox_and_radio', 30 );

function colabs7_add_tag_generator_checkbox_and_radio() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'checkbox', __( 'Checkboxes', 'colabs7' ),
		'colabs7-tg-pane-checkbox', 'colabs7_tg_pane_checkbox' );

	colabs7_add_tag_generator( 'radio', __( 'Radio buttons', 'colabs7' ),
		'colabs7-tg-pane-radio', 'colabs7_tg_pane_radio' );
}

function colabs7_tg_pane_checkbox( &$contact_form ) {
	colabs7_tg_pane_checkbox_and_radio( 'checkbox' );
}

function colabs7_tg_pane_radio( &$contact_form ) {
	colabs7_tg_pane_checkbox_and_radio( 'radio' );
}

function colabs7_tg_pane_checkbox_and_radio( $type = 'checkbox' ) {
	if ( 'radio' != $type )
		$type = 'checkbox';

?>
<div id="colabs7-tg-pane-<?php echo $type; ?>" class="hidden">
<form action="">
<table>
<?php if ( 'checkbox' == $type ) : ?>
<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'colabs7' ) ); ?></td></tr>
<?php endif; ?>

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
<td><?php echo esc_html( __( 'Choices', 'colabs7' ) ); ?><br />
<textarea name="values"></textarea><br />
<span style="font-size: smaller"><?php echo esc_html( __( "* One choice per line.", 'colabs7' ) ); ?></span>
</td>

<td>
<br /><input type="checkbox" name="label_first" class="option" />&nbsp;<?php echo esc_html( __( 'Put a label first, a checkbox last?', 'colabs7' ) ); ?>
<br /><input type="checkbox" name="use_label_element" class="option" />&nbsp;<?php echo esc_html( __( 'Wrap each item with <label> tag?', 'colabs7' ) ); ?>
<?php if ( 'checkbox' == $type ) : ?>
<br /><input type="checkbox" name="exclusive" class="option" />&nbsp;<?php echo esc_html( __( 'Make checkboxes exclusive?', 'colabs7' ) ); ?>
<?php endif; ?>
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