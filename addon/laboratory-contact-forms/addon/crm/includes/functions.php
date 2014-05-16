<?php

function crm_plugin_url( $path = '' ) {
	$url = untrailingslashit( CRM_PLUGIN_URL );

	if ( ! empty( $path ) && is_string( $path ) && false === strpos( $path, '..' ) )
		$url .= '/' . ltrim( $path, '/' );

	return $url;
}

function crm_array_flatten( $input ) {
	if ( ! is_array( $input ) )
		return array( $input );

	$output = array();

	foreach ( $input as $value )
		$output = array_merge( $output, crm_array_flatten( $value ) );

	return $output;
}

?>