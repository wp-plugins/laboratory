<?php

add_action( 'init', 'colabs7_control_init', 11 );

function colabs7_control_init() {
	colabs7_ajax_onload();
	colabs7_ajax_json_echo();
	colabs7_submit_nonajax();
}

function colabs7_ajax_onload() {
	global $colabs7_contact_form;

	if ( 'GET' != $_SERVER['REQUEST_METHOD'] || ! isset( $_GET['_colabs7_is_ajax_call'] ) )
		return;

	$echo = '';

	if ( isset( $_GET['_colabs7'] ) ) {
		$id = (int) $_GET['_colabs7'];

		if ( $colabs7_contact_form = colabs7_contact_form( $id ) ) {
			$items = apply_filters( 'colabs7_ajax_onload', array() );
			$colabs7_contact_form = null;
		}
	}

	$echo = json_encode( $items );

	if ( colabs7_is_xhr() ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo $echo;
	}

	exit();
}

function colabs7_ajax_json_echo() {
	global $colabs7_contact_form;

	if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['_colabs7_is_ajax_call'] ) )
		return;

	$echo = '';

	if ( isset( $_POST['_colabs7'] ) ) {
		$id = (int) $_POST['_colabs7'];
		$unit_tag = colabs7_sanitize_unit_tag( $_POST['_colabs7_unit_tag'] );

		if ( $colabs7_contact_form = colabs7_contact_form( $id ) ) {

			$items = array(
				'mailSent' => false,
				'into' => '#' . $unit_tag,
				'captcha' => null );

			$result = $colabs7_contact_form->submit( true );

			if ( ! empty( $result['message'] ) )
				$items['message'] = $result['message'];

			if ( $result['mail_sent'] )
				$items['mailSent'] = true;

			if ( ! $result['valid'] ) {
				$invalids = array();

				foreach ( $result['invalid_reasons'] as $name => $reason ) {
					$invalids[] = array(
						'into' => 'span.colabs7-form-control-wrap.' . $name,
						'message' => $reason );
				}

				$items['invalids'] = $invalids;
			}

			if ( $result['spam'] )
				$items['spam'] = true;

			if ( ! empty( $result['scripts_on_sent_ok'] ) )
				$items['onSentOk'] = $result['scripts_on_sent_ok'];

			if ( ! empty( $result['scripts_on_submit'] ) )
				$items['onSubmit'] = $result['scripts_on_submit'];

			$items = apply_filters( 'colabs7_ajax_json_echo', $items, $result );

			$colabs7_contact_form = null;
		}
	}

	$echo = json_encode( $items );

	if ( colabs7_is_xhr() ) {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo $echo;
	} else {
		@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
		echo '<textarea>' . $echo . '</textarea>';
	}

	exit();
}

function colabs7_is_xhr() {
	if ( ! isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) )
		return false;

	return $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}

function colabs7_submit_nonajax() {
	global $colabs7, $colabs7_contact_form;

	if ( ! isset( $_POST['_colabs7'] ) )
		return;

	$id = (int) $_POST['_colabs7'];

	if ( $colabs7_contact_form = colabs7_contact_form( $id ) )
		$colabs7->result = $colabs7_contact_form->submit();

	$colabs7_contact_form = null;
}

add_action( 'the_post', 'colabs7_the_post' );

function colabs7_the_post() {
	global $colabs7;

	$colabs7->processing_within = 'p' . get_the_ID();
	$colabs7->unit_count = 0;
}

add_action( 'loop_end', 'colabs7_loop_end' );

function colabs7_loop_end() {
	global $colabs7;

	$colabs7->processing_within = '';
}

add_filter( 'widget_text', 'colabs7_widget_text_filter', 9 );

function colabs7_widget_text_filter( $content ) {
	global $colabs7;

	if ( ! preg_match( '/\[[\r\n\t ]*contact-form(-7)?[\r\n\t ].*?\]/', $content ) )
		return $content;

	$colabs7->widget_count += 1;
	$colabs7->processing_within = 'w' . $colabs7->widget_count;
	$colabs7->unit_count = 0;

	$content = do_shortcode( $content );

	$colabs7->processing_within = '';

	return $content;
}

/* Shortcodes */

add_action( 'plugins_loaded', 'colabs7_add_shortcodes', 1 );

function colabs7_add_shortcodes() {
	add_shortcode( 'colabs-contact-form', 'colabs7_contact_form_tag_func' );
	add_shortcode( 'contact-form', 'colabs7_contact_form_tag_func' );
}

function colabs7_contact_form_tag_func( $atts, $content = null, $code = '' ) {
	global $colabs7, $colabs7_contact_form;

	if ( is_feed() )
		return '[colabs-contact-form]';

	if ( 'colabs-contact-form' == $code ) {
		$atts = shortcode_atts( array( 'id' => 0, 'title' => '' ), $atts );

		$id = (int) $atts['id'];
		$title = trim( $atts['title'] );

		if ( ! $colabs7_contact_form = colabs7_contact_form( $id ) )
			$colabs7_contact_form = colabs7_get_contact_form_by_title( $title );

	} else {
		if ( is_string( $atts ) )
			$atts = explode( ' ', $atts, 2 );

		$id = (int) array_shift( $atts );
		$colabs7_contact_form = colabs7_get_contact_form_by_old_id( $id );
	}

	if ( ! $colabs7_contact_form )
		return '[colabs-contact-form 404 "Not Found"]';

	if ( $colabs7->processing_within ) { // Inside post content or text widget
		$colabs7->unit_count += 1;
		$unit_count = $colabs7->unit_count;
		$processing_within = $colabs7->processing_within;

	} else { // Inside template

		if ( ! isset( $colabs7->global_unit_count ) )
			$colabs7->global_unit_count = 0;

		$colabs7->global_unit_count += 1;
		$unit_count = 1;
		$processing_within = 't' . $colabs7->global_unit_count;
	}

	$unit_tag = 'colabs7-f' . $colabs7_contact_form->id . '-' . $processing_within . '-o' . $unit_count;
	$colabs7_contact_form->unit_tag = $unit_tag;

	$form = $colabs7_contact_form->form_html();

	$colabs7_contact_form = null;

	return $form;
}

if ( COLABS7_LOAD_JS )
	add_action( 'wp_enqueue_scripts', 'colabs7_enqueue_scripts' );

function colabs7_enqueue_scripts() {
	// jquery.form.js originally bundled with WordPress is out of date and deprecated
	// so we need to deregister it and re-register the latest one
	wp_deregister_script( 'jquery-form' );
	wp_register_script( 'jquery-form',
		colabs7_plugin_url( 'includes/js/jquery.form.min.js' ),
		array( 'jquery' ), '3.39.0-2013.07.31', true );

	$in_footer = true;
	if ( 'header' === COLABS7_LOAD_JS )
		$in_footer = false;

	wp_enqueue_script( 'colabs-contact-form',
		colabs7_plugin_url( 'includes/js/scripts.js' ),
		array( 'jquery', 'jquery-form' ), COLABS7_VERSION, $in_footer );

	$_colabs7 = array(
		'loaderUrl' => colabs7_ajax_loader(),
		'sending' => __( 'Sending ...', 'colabs7' ) );

	if ( defined( 'WP_CACHE' ) && WP_CACHE )
		$_colabs7['cached'] = 1;

	if ( colabs7_support_html5_fallback() )
		$_colabs7['jqueryUi'] = 1;

	wp_localize_script( 'colabs-contact-form', '_colabs7', $_colabs7 );

	do_action( 'colabs7_enqueue_scripts' );
}

function colabs7_script_is() {
	return wp_script_is( 'colabs-contact-form' );
}

if ( COLABS7_LOAD_CSS )
	add_action( 'wp_enqueue_scripts', 'colabs7_enqueue_styles' );

function colabs7_enqueue_styles() {
	wp_enqueue_style( 'colabs-contact-form',
		colabs7_plugin_url( 'includes/css/styles.css' ),
		array(), COLABS7_VERSION, 'all' );

	if ( colabs7_is_rtl() ) {
		wp_enqueue_style( 'colabs-contact-form-rtl',
			colabs7_plugin_url( 'includes/css/styles-rtl.css' ),
			array(), COLABS7_VERSION, 'all' );
	}

	do_action( 'colabs7_enqueue_styles' );
}

function colabs7_style_is() {
	return wp_style_is( 'colabs-contact-form' );
}

/* HTML5 Fallback */

add_action( 'wp_enqueue_scripts', 'colabs7_html5_fallback', 11 );

function colabs7_html5_fallback() {
	if ( ! colabs7_support_html5_fallback() )
		return;

	if ( COLABS7_LOAD_JS ) {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-spinner' );
	}

	if ( COLABS7_LOAD_CSS ) {
		wp_enqueue_style( 'jquery-ui-smoothness',
			colabs7_plugin_url( 'includes/js/jquery-ui/themes/smoothness/jquery-ui.min.css' ), array(), '1.10.3', 'screen' );
	}
}

?>