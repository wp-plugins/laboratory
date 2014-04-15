<?php

add_filter( 'map_meta_cap', 'colabs7_map_meta_cap', 10, 4 );

function colabs7_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'colabs7_edit_contact_form' => COLABS7_ADMIN_READ_WRITE_CAPABILITY,
		'colabs7_edit_contact_forms' => COLABS7_ADMIN_READ_WRITE_CAPABILITY,
		'colabs7_read_contact_forms' => COLABS7_ADMIN_READ_CAPABILITY,
		'colabs7_delete_contact_form' => COLABS7_ADMIN_READ_WRITE_CAPABILITY );

	$meta_caps = apply_filters( 'colabs7_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) )
		$caps[] = $meta_caps[$cap];

	return $caps;
}

?>