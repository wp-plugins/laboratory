<?php
/**
** A base module for [acceptance]
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_acceptance', 5 );

function colabs7_add_shortcode_acceptance() {
	colabs7_add_shortcode( 'acceptance',
		'colabs7_acceptance_shortcode_handler', true );
}

function colabs7_acceptance_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	if ( $tag->has_option( 'invert' ) )
		$class .= ' colabs7-invert';

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	if ( $tag->has_option( 'default:on' ) )
		$atts['checked'] = 'checked';

	$atts['type'] = 'checkbox';
	$atts['name'] = $tag->name;
	$atts['value'] = '1';

	$atts = colabs7_format_atts( $atts );

	$html = sprintf(
		'<span class="colabs7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		$tag->name, $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'colabs7_validate_acceptance', 'colabs7_acceptance_validation_filter', 10, 2 );

function colabs7_acceptance_validation_filter( $result, $tag ) {
	if ( ! colabs7_acceptance_as_validation() )
		return $result;

	$tag = new COLABS7_Shortcode( $tag );

	$name = $tag->name;
	$value = ( ! empty( $_POST[$name] ) ? 1 : 0 );

	$invert = $tag->has_option( 'invert' );

	if ( $invert && $value || ! $invert && ! $value ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'accept_terms' );
	}

	return $result;
}


/* Acceptance filter */

add_filter( 'colabs7_acceptance', 'colabs7_acceptance_filter' );

function colabs7_acceptance_filter( $accepted ) {
	if ( ! $accepted )
		return $accepted;

	$fes = colabs7_scan_shortcode( array( 'type' => 'acceptance' ) );

	foreach ( $fes as $fe ) {
		$name = $fe['name'];
		$options = (array) $fe['options'];

		if ( empty( $name ) )
			continue;

		$value = ( ! empty( $_POST[$name] ) ? 1 : 0 );

		$invert = (bool) preg_grep( '%^invert$%', $options );

		if ( $invert && $value || ! $invert && ! $value )
			$accepted = false;
	}

	return $accepted;
}

add_filter( 'colabs7_form_class_attr', 'colabs7_acceptance_form_class_attr' );

function colabs7_acceptance_form_class_attr( $class ) {
	if ( colabs7_acceptance_as_validation() )
		return $class . ' colabs7-acceptance-as-validation';

	return $class;
}

function colabs7_acceptance_as_validation() {
	if ( ! $contact_form = colabs7_get_current_contact_form() )
		return false;

	$settings = $contact_form->additional_setting( 'acceptance_as_validation', false );

	foreach ( $settings as $setting ) {
		if ( in_array( $setting, array( 'on', 'true', '1' ) ) )
			return true;
	}

	return false;
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_acceptance', 35 );

function colabs7_add_tag_generator_acceptance() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'acceptance', __( 'Acceptance', 'colabs7' ),
		'colabs7-tg-pane-acceptance', 'colabs7_tg_pane_acceptance' );
}

function colabs7_tg_pane_acceptance( &$contact_form ) {
?>
<div id="colabs7-tg-pane-acceptance" class="hidden">
<form action="">
<table>
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
<td colspan="2">
<br /><input type="checkbox" name="default:on" class="option" />&nbsp;<?php echo esc_html( __( "Make this checkbox checked by default?", 'colabs7' ) ); ?>
<br /><input type="checkbox" name="invert" class="option" />&nbsp;<?php echo esc_html( __( "Make this checkbox work inversely?", 'colabs7' ) ); ?>
<br /><span style="font-size: smaller;"><?php echo esc_html( __( "* That means visitor who accepts the term unchecks it.", 'colabs7' ) ); ?></span>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?><br /><input type="text" name="acceptance" class="tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>