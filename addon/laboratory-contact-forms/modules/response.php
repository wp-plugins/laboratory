<?php
/**
** A base module for [response]
**/

/* Shortcode handler */

colabs7_add_shortcode( 'response', 'colabs7_response_shortcode_handler' );

function colabs7_response_shortcode_handler( $tag ) {
	if ( $contact_form = colabs7_get_current_contact_form() ) {
		$contact_form->responses_count += 1;
		return $contact_form->form_response_output();
	}
}

?>