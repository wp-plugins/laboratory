<?php

add_filter( 'map_meta_cap', 'crm_map_meta_cap', 10, 4 );

function crm_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'crm_edit_contacts' => 'edit_users',
		'crm_edit_contact' => 'edit_users',
		'crm_delete_contact' => 'edit_users',
		'crm_edit_inbound_messages' => 'edit_users',
		'crm_delete_inbound_message' => 'edit_users',
		'crm_delete_inbound_messages' => 'edit_users',
		'crm_spam_inbound_message' => 'edit_users',
		'crm_unspam_inbound_message' => 'edit_users' );

	$meta_caps = apply_filters( 'crm_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) )
		$caps[] = $meta_caps[$cap];

	return $caps;
}

?>