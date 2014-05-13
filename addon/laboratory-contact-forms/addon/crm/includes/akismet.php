<?php

function crm_akismet_submit_spam( $comment ) {
	return crm_akismet_submit( $comment, 'spam' );
}

function crm_akismet_submit_ham( $comment ) {
	return crm_akismet_submit( $comment, 'ham' );
}

function crm_akismet_submit( $comment, $as = 'spam' ) {
	global $akismet_api_host, $akismet_api_port;

	if ( ! crm_akismet_is_active() )
		return false;

	if ( ! in_array( $as, array( 'spam', 'ham' ) ) )
		return false;

	$query_string = '';

	foreach ( (array) $comment as $key => $data )
		$query_string .= $key . '=' . urlencode( stripslashes( (string) $data ) ) . '&';

	$response = akismet_http_post( $query_string,
		$akismet_api_host, '/1.1/submit-' . $as, $akismet_api_port );

	return (bool) $response[1];
}

function crm_akismet_is_active() {
	return function_exists( 'akismet_get_key' ) && akismet_get_key();
}

?>