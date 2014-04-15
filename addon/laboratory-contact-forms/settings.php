<?php

require_once COLABS7_PLUGIN_DIR . '/includes/functions.php';
require_once COLABS7_PLUGIN_DIR . '/includes/deprecated.php';
require_once COLABS7_PLUGIN_DIR . '/includes/formatting.php';
require_once COLABS7_PLUGIN_DIR . '/includes/pipe.php';
require_once COLABS7_PLUGIN_DIR . '/includes/shortcodes.php';
require_once COLABS7_PLUGIN_DIR . '/includes/capabilities.php';
require_once COLABS7_PLUGIN_DIR . '/includes/classes.php';

if ( is_admin() )
	require_once COLABS7_PLUGIN_DIR . '/admin/admin.php';
else
	require_once COLABS7_PLUGIN_DIR . '/includes/controller.php';

add_action( 'plugins_loaded', 'colabs7_init_shortcode_manager', 1 );

function colabs7_init_shortcode_manager() {
	global $colabs7_shortcode_manager;

	$colabs7_shortcode_manager = new COLABS7_ShortcodeManager();
}

/* Loading modules */

add_action( 'plugins_loaded', 'colabs7_load_modules', 1 );

function colabs7_load_modules() {
	$dir = COLABS7_PLUGIN_MODULES_DIR;

	if ( ! ( is_dir( $dir ) && $dh = opendir( $dir ) ) )
		return false;

	while ( ( $module = readdir( $dh ) ) !== false ) {
		if ( substr( $module, -4 ) == '.php' && substr( $module, 0, 1 ) != '.' )
			include_once $dir . '/' . $module;
	}
}

add_action( 'plugins_loaded', 'colabs7_set_request_uri', 9 );

function colabs7_set_request_uri() {
	global $colabs7_request_uri;

	$colabs7_request_uri = add_query_arg( array() );
}

function colabs7_get_request_uri() {
	global $colabs7_request_uri;

	return (string) $colabs7_request_uri;
}

add_action( 'init', 'colabs7_init' );

function colabs7_init() {
	colabs7();

	// L10N
	colabs7_load_plugin_textdomain();

	// Custom Post Type
	colabs7_register_post_types();

	do_action( 'colabs7_init' );
}

function colabs7() {
	global $colabs7;

	if ( is_object( $colabs7 ) )
		return;

	$colabs7 = (object) array(
		'processing_within' => '',
		'widget_count' => 0,
		'unit_count' => 0,
		'global_unit_count' => 0,
		'result' => array() );
}

function colabs7_load_plugin_textdomain() {
	load_plugin_textdomain( 'colabs7', false, 'colabs-contact-form/languages' );
}

function colabs7_register_post_types() {
	COLABS7_ContactForm::register_post_type();
}

/* Upgrading */

add_action( 'admin_init', 'colabs7_upgrade' );

function colabs7_upgrade() {
	$opt = get_option( 'colabs7' );

	if ( ! is_array( $opt ) )
		$opt = array();

	$old_ver = isset( $opt['version'] ) ? (string) $opt['version'] : '0';
	$new_ver = COLABS7_VERSION;

	if ( $old_ver == $new_ver )
		return;

	do_action( 'colabs7_upgrade', $new_ver, $old_ver );

	$opt['version'] = $new_ver;

	update_option( 'colabs7', $opt );
}

add_action( 'colabs7_upgrade', 'colabs7_convert_to_cpt', 10, 2 );

function colabs7_convert_to_cpt( $new_ver, $old_ver ) {
	global $wpdb;

	if ( ! version_compare( $old_ver, '3.0-dev', '<' ) )
		return;

	$old_rows = array();

	$table_name = $wpdb->prefix . "contact_form_7";

	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) ) {
		$old_rows = $wpdb->get_results( "SELECT * FROM $table_name" );
	} elseif ( ( $opt = get_option( 'colabs7' ) ) && ! empty( $opt['contact_forms'] ) ) {
		foreach ( (array) $opt['contact_forms'] as $key => $value ) {
			$old_rows[] = (object) array_merge( $value, array( 'cf7_unit_id' => $key ) );
		}
	}

	foreach ( (array) $old_rows as $row ) {
		$q = "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_old_cf7_unit_id'"
			. $wpdb->prepare( " AND meta_value = %d", $row->cf7_unit_id );

		if ( $wpdb->get_var( $q ) )
			continue;

		$postarr = array(
			'post_type' => 'colabs7_contact_form',
			'post_status' => 'publish',
			'post_title' => maybe_unserialize( $row->title ) );

		$post_id = wp_insert_post( $postarr );

		if ( $post_id ) {
			update_post_meta( $post_id, '_old_cf7_unit_id', $row->cf7_unit_id );

			$metas = array( 'form', 'mail', 'mail_2', 'messages', 'additional_settings' );

			foreach ( $metas as $meta ) {
				update_post_meta( $post_id, '_' . $meta,
					colabs7_normalize_newline_deep( maybe_unserialize( $row->{$meta} ) ) );
			}
		}
	}
}

add_action( 'colabs7_upgrade', 'colabs7_prepend_underscore', 10, 2 );

function colabs7_prepend_underscore( $new_ver, $old_ver ) {
	if ( version_compare( $old_ver, '3.0-dev', '<' ) )
		return;

	if ( ! version_compare( $old_ver, '3.3-dev', '<' ) )
		return;

	$posts = COLABS7_ContactForm::find( array(
		'post_status' => 'any',
		'posts_per_page' => -1 ) );

	foreach ( $posts as $post ) {
		$props = $post->get_properties();

		foreach ( $props as $prop => $value ) {
			if ( metadata_exists( 'post', $post->id, '_' . $prop ) )
				continue;

			update_post_meta( $post->id, '_' . $prop, $value );
			delete_post_meta( $post->id, $prop );
		}
	}
}

/* Install and default settings */

add_action( 'activate_' . COLABS7_PLUGIN_BASENAME, 'colabs7_install' );

function colabs7_install() {
	if ( $opt = get_option( 'colabs7' ) )
		return;

	colabs7_load_plugin_textdomain();
	colabs7_register_post_types();
	colabs7_upgrade();

	if ( get_posts( array( 'post_type' => 'colabs7_contact_form' ) ) )
		return;

	$contact_form = colabs7_get_contact_form_default_pack(
		array( 'title' => sprintf( __( 'Contact form %d', 'colabs7' ), 1 ) ) );

	$contact_form->save();
}

?>