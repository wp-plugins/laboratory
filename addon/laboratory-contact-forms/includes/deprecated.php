<?php
/*
 * Deprecated functions come here to die.
 */

function colabs7_admin_url( $args = array() ) {
	colabs7_deprecated_function( __FUNCTION__, '3.2', 'admin_url()' );

	$defaults = array( 'page' => 'colabs7' );
	$args = wp_parse_args( $args, $defaults );

	$url = menu_page_url( $args['page'], false );
	unset( $args['page'] );

	$url = add_query_arg( $args, $url );

	return esc_url_raw( $url );
}

function colabs7_contact_form_default_pack( $locale = null ) {
	colabs7_deprecated_function( __FUNCTION__, '3.0', 'colabs7_get_contact_form_default_pack()' );

	return colabs7_get_contact_form_default_pack( array( 'locale' => $locale ) );
}

?>