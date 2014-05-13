<?php
/**
** A base module for [submit]
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_submit', 5 );

function colabs7_add_shortcode_submit() {
	colabs7_add_shortcode( 'submit', 'colabs7_submit_shortcode_handler' );
}

function colabs7_submit_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$class = colabs7_form_controls_class( $tag->type );

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	$value = isset( $tag->values[0] ) ? $tag->values[0] : '';

	if ( empty( $value ) )
		$value = __( 'Send', 'colabs7' );

	$atts['type'] = 'submit';
	$atts['value'] = $value;

	$atts = colabs7_format_atts( $atts );

	$html = sprintf( '<input %1$s />', $atts );

	return $html;
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_submit', 55 );

function colabs7_add_tag_generator_submit() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'submit', __( 'Submit button', 'colabs7' ),
		'colabs7-tg-pane-submit', 'colabs7_tg_pane_submit', array( 'nameless' => 1 ) );
}

function colabs7_tg_pane_submit( &$contact_form ) {
?>
<div id="colabs7-tg-pane-submit" class="hidden">
<form action="">
<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Label', 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="values" class="oneline" /></td>

<td></td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?><br /><input type="text" name="submit" class="tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>