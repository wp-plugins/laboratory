<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function colabs7_delete_plugin() {
	global $wpdb;

	delete_option( 'colabs7' );

	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type' => 'colabs7_contact_form',
		'post_status' => 'any' ) );

	foreach ( $posts as $post )
		wp_delete_post( $post->ID, true );

	$table_name = $wpdb->prefix . "contact_form_7";

	$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
}

colabs7_delete_plugin();

?>