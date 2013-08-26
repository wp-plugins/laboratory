<?php
/**
** A base module for [select] and [select*]
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_select', 5 );

function colabs7_add_shortcode_select() {
	colabs7_add_shortcode( array( 'select', 'select*' ),
		'colabs7_select_shortcode_handler', true );
}

function colabs7_select_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	if ( $tag->is_required() )
		$atts['aria-required'] = 'true';

	$defaults = array();

	if ( $matches = $tag->get_first_match_option( '/^default:([0-9_]+)$/' ) )
		$defaults = explode( '_', $matches[1] );

	$multiple = $tag->has_option( 'multiple' );
	$include_blank = $tag->has_option( 'include_blank' );
	$first_as_label = $tag->has_option( 'first_as_label' );

	$name = $tag->name;
	$values = $tag->values;
	$labels = $tag->labels;

	$empty_select = empty( $values );

	if ( $empty_select || $include_blank ) {
		array_unshift( $labels, '---' );
		array_unshift( $values, '' );
	} elseif ( $first_as_label ) {
		$values[0] = '';
	}

	$html = '';

	$posted = colabs7_is_posted();

	foreach ( $values as $key => $value ) {
		$selected = false;

		if ( $posted && ! empty( $_POST[$name] ) ) {
			if ( $multiple && in_array( esc_sql( $value ), (array) $_POST[$name] ) )
				$selected = true;
			if ( ! $multiple && $_POST[$name] == esc_sql( $value ) )
				$selected = true;
		} else {
			if ( ! $empty_select && in_array( $key + 1, (array) $defaults ) )
				$selected = true;
		}

		$item_atts = array(
			'value' => $value,
			'selected' => $selected ? 'selected' : '' );

		$item_atts = colabs7_format_atts( $item_atts );

		$label = isset( $labels[$key] ) ? $labels[$key] : $value;

		$html .= sprintf( '<option %1$s>%2$s</option>',
			$item_atts, esc_html( $label ) );
	}

	if ( $multiple )
		$atts['multiple'] = 'multiple';

	$atts['name'] = $tag->name . ( $multiple ? '[]' : '' );

	$atts = colabs7_format_atts( $atts );

	$html = sprintf(
		'<span class="colabs7-form-control-wrap %1$s"><select %2$s>%3$s</select>%4$s</span>',
		$tag->name, $atts, $html, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'colabs7_validate_select', 'colabs7_select_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_select*', 'colabs7_select_validation_filter', 10, 2 );

function colabs7_select_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$name = $tag->name;

	if ( isset( $_POST[$name] ) && is_array( $_POST[$name] ) ) {
		foreach ( $_POST[$name] as $key => $value ) {
			if ( '' === $value )
				unset( $_POST[$name][$key] );
		}
	}

	if ( $tag->is_required() ) {
		if ( ! isset( $_POST[$name] )
		|| empty( $_POST[$name] ) && '0' !== $_POST[$name] ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		}
	}

	return $result;
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_menu', 25 );

function colabs7_add_tag_generator_menu() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'menu', __( 'Drop-down menu', 'colabs7' ),
		'colabs7-tg-pane-menu', 'colabs7_tg_pane_menu' );
}

function colabs7_tg_pane_menu( &$contact_form ) {
?>
<div id="colabs7-tg-pane-menu" class="hidden">
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
<td><?php echo esc_html( __( 'Choices', 'colabs7' ) ); ?><br />
<textarea name="values"></textarea><br />
<span style="font-size: smaller"><?php echo esc_html( __( "* One choice per line.", 'colabs7' ) ); ?></span>
</td>

<td>
<br /><input type="checkbox" name="multiple" class="option" />&nbsp;<?php echo esc_html( __( 'Allow multiple selections?', 'colabs7' ) ); ?>
<br /><input type="checkbox" name="include_blank" class="option" />&nbsp;<?php echo esc_html( __( 'Insert a blank item as the first option?', 'colabs7' ) ); ?>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?><br /><input type="text" name="select" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the Mail fields below.", 'colabs7' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>