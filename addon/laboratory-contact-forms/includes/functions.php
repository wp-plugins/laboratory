<?php

function colabs7_plugin_path( $path = '' ) {
	return path_join( COLABS7_PLUGIN_DIR, trim( $path, '/' ) );
}

function colabs7_plugin_url( $path = '' ) {
	$url = untrailingslashit( COLABS7_PLUGIN_URL );

	if ( ! empty( $path ) && is_string( $path ) && false === strpos( $path, '..' ) )
		$url .= '/' . ltrim( $path, '/' );

	return $url;
}

function colabs7_deprecated_function( $function, $version, $replacement = null ) {
	do_action( 'colabs7_deprecated_function_run', $function, $replacement, $version );

	if ( WP_DEBUG && apply_filters( 'colabs7_deprecated_function_trigger_error', true ) ) {
		if ( ! is_null( $replacement ) )
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Colabs Contact Form version %2$s! Use %3$s instead.', 'colabs7' ), $function, $version, $replacement ) );
		else
			trigger_error( sprintf( __( '%1$s is <strong>deprecated</strong> since Colabs Contact Form version %2$s with no alternative available.', 'colabs7' ), $function, $version ) );
	}
}

function colabs7_messages() {
	$messages = array(
		'mail_sent_ok' => array(
			'description' => __( "Sender's message was sent successfully", 'colabs7' ),
			'default' => __( 'Your message was sent successfully. Thanks.', 'colabs7' )
		),

		'mail_sent_ng' => array(
			'description' => __( "Sender's message was failed to send", 'colabs7' ),
			'default' => __( 'Failed to send your message. Please try later or contact the administrator by another method.', 'colabs7' )
		),

		'validation_error' => array(
			'description' => __( "Validation errors occurred", 'colabs7' ),
			'default' => __( 'Validation errors occurred. Please confirm the fields and submit it again.', 'colabs7' )
		),

		'spam' => array(
			'description' => __( "Submission was referred to as spam", 'colabs7' ),
			'default' => __( 'Failed to send your message. Please try later or contact the administrator by another method.', 'colabs7' )
		),

		'accept_terms' => array(
			'description' => __( "There are terms that the sender must accept", 'colabs7' ),
			'default' => __( 'Please accept the terms to proceed.', 'colabs7' )
		),

		'invalid_required' => array(
			'description' => __( "There is a field that the sender must fill in", 'colabs7' ),
			'default' => __( 'Please fill the required field.', 'colabs7' )
		)
	);

	return apply_filters( 'colabs7_messages', $messages );
}

function colabs7_get_default_template( $prop = 'form' ) {
	if ( 'form' == $prop )
		$template = colabs7_default_form_template();
	elseif ( 'mail' == $prop )
		$template = colabs7_default_mail_template();
	elseif ( 'mail_2' == $prop )
		$template = colabs7_default_mail_2_template();
	elseif ( 'messages' == $prop )
		$template = colabs7_default_messages_template();
	else
		$template = null;

	return apply_filters( 'colabs7_default_template', $template, $prop );
}

function colabs7_default_form_template() {
	$template =
		'<p>' . __( 'Your Name', 'colabs7' ) . ' ' . __( '(required)', 'colabs7' ) . '<br />' . "\n"
		. '    [text* your-name] </p>' . "\n\n"
		. '<p>' . __( 'Your Email', 'colabs7' ) . ' ' . __( '(required)', 'colabs7' ) . '<br />' . "\n"
		. '    [email* your-email] </p>' . "\n\n"
		. '<p>' . __( 'Subject', 'colabs7' ) . '<br />' . "\n"
		. '    [text your-subject] </p>' . "\n\n"
		. '<p>' . __( 'Your Message', 'colabs7' ) . '<br />' . "\n"
		. '    [textarea your-message] </p>' . "\n\n"
		. '<p>[submit "' . __( 'Send', 'colabs7' ) . '"]</p>';

	return $template;
}

function colabs7_default_mail_template() {
	$subject = '[your-subject]';
	$sender = '[your-name] <[your-email]>';
	$body = sprintf( __( 'From: %s', 'colabs7' ), '[your-name] <[your-email]>' ) . "\n"
		. sprintf( __( 'Subject: %s', 'colabs7' ), '[your-subject]' ) . "\n\n"
		. __( 'Message Body:', 'colabs7' ) . "\n" . '[your-message]' . "\n\n" . '--' . "\n"
		. sprintf( __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'colabs7' ),
			get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	$recipient = get_option( 'admin_email' );
	$additional_headers = '';
	$attachments = '';
	$use_html = 0;
	return compact( 'subject', 'sender', 'body', 'recipient', 'additional_headers', 'attachments', 'use_html' );
}

function colabs7_default_mail_2_template() {
	$active = false;
	$subject = '[your-subject]';
	$sender = '[your-name] <[your-email]>';
	$body = __( 'Message Body:', 'colabs7' ) . "\n" . '[your-message]' . "\n\n" . '--' . "\n"
		. sprintf( __( 'This e-mail was sent from a contact form on %1$s (%2$s)', 'colabs7' ),
			get_bloginfo( 'name' ), get_bloginfo( 'url' ) );
	$recipient = '[your-email]';
	$additional_headers = '';
	$attachments = '';
	$use_html = 0;
	return compact( 'active', 'subject', 'sender', 'body', 'recipient', 'additional_headers', 'attachments', 'use_html' );
}

function colabs7_default_messages_template() {
	$messages = array();

	foreach ( colabs7_messages() as $key => $arr ) {
		$messages[$key] = $arr['default'];
	}

	return $messages;
}

function colabs7_upload_dir( $type = false ) {
	$uploads = wp_upload_dir();

	$uploads = apply_filters( 'colabs7_upload_dir', array(
		'dir' => $uploads['basedir'],
		'url' => $uploads['baseurl'] ) );

	if ( 'dir' == $type )
		return $uploads['dir'];
	if ( 'url' == $type )
		return $uploads['url'];

	return $uploads;
}

if ( ! function_exists( 'wp_is_writable' ) ) {
/*
 * wp_is_writable exists in WordPress 3.6+
 * http://core.trac.wordpress.org/browser/tags/3.6/wp-includes/functions.php#L1437
 * We will be able to remove this function definition
 * after moving required WordPress version up to 3.6.
 */
function wp_is_writable( $path ) {
	if ( 'WIN' === strtoupper( substr( PHP_OS, 0, 3 ) ) )
		return win_is_writable( $path );
	else
		return @is_writable( $path );
}
}

function colabs7_l10n() {
	$l10n = array(
		'af' => __( 'Afrikaans', 'colabs7' ),
		'sq' => __( 'Albanian', 'colabs7' ),
		'ar' => __( 'Arabic', 'colabs7' ),
		'hy_AM' => __( 'Armenian', 'colabs7' ),
		'az_AZ' => __( 'Azerbaijani', 'colabs7' ),
		'bn_BD' => __( 'Bangla', 'colabs7' ),
		'eu' => __( 'Basque', 'colabs7' ),
		'be_BY' => __( 'Belarusian', 'colabs7' ),
		'bs' => __( 'Bosnian', 'colabs7' ),
		'pt_BR' => __( 'Brazilian Portuguese', 'colabs7' ),
		'bg_BG' => __( 'Bulgarian', 'colabs7' ),
		'ca' => __( 'Catalan', 'colabs7' ),
		'zh_CN' => __( 'Chinese (Simplified)', 'colabs7' ),
		'zh_TW' => __( 'Chinese (Traditional)', 'colabs7' ),
		'hr' => __( 'Croatian', 'colabs7' ),
		'cs_CZ' => __( 'Czech', 'colabs7' ),
		'da_DK' => __( 'Danish', 'colabs7' ),
		'nl_NL' => __( 'Dutch', 'colabs7' ),
		'en_US' => __( 'English', 'colabs7' ),
		'eo_EO' => __( 'Esperanto', 'colabs7' ),
		'et' => __( 'Estonian', 'colabs7' ),
		'fi' => __( 'Finnish', 'colabs7' ),
		'fr_FR' => __( 'French', 'colabs7' ),
		'gl_ES' => __( 'Galician', 'colabs7' ),
		'gu_IN' => __( 'Gujarati', 'colabs7' ),
		'ka_GE' => __( 'Georgian', 'colabs7' ),
		'de_DE' => __( 'German', 'colabs7' ),
		'el' => __( 'Greek', 'colabs7' ),
		'he_IL' => __( 'Hebrew', 'colabs7' ),
		'hi_IN' => __( 'Hindi', 'colabs7' ),
		'hu_HU' => __( 'Hungarian', 'colabs7' ),
		'bn_IN' => __( 'Indian Bengali', 'colabs7' ),
		'id_ID' => __( 'Indonesian', 'colabs7' ),
		'ga_IE' => __( 'Irish', 'colabs7' ),
		'it_IT' => __( 'Italian', 'colabs7' ),
		'ja' => __( 'Japanese', 'colabs7' ),
		'ko_KR' => __( 'Korean', 'colabs7' ),
		'lv' => __( 'Latvian', 'colabs7' ),
		'lt_LT' => __( 'Lithuanian', 'colabs7' ),
		'mk_MK' => __( 'Macedonian', 'colabs7' ),
		'ms_MY' => __( 'Malay', 'colabs7' ),
		'ml_IN' => __( 'Malayalam', 'colabs7' ),
		'mt_MT' => __( 'Maltese', 'colabs7' ),
		'nb_NO' => __( 'Norwegian', 'colabs7' ),
		'fa_IR' => __( 'Persian', 'colabs7' ),
		'pl_PL' => __( 'Polish', 'colabs7' ),
		'pt_PT' => __( 'Portuguese', 'colabs7' ),
		'ru_RU' => __( 'Russian', 'colabs7' ),
		'ro_RO' => __( 'Romanian', 'colabs7' ),
		'sr_RS' => __( 'Serbian', 'colabs7' ),
		'si_LK' => __( 'Sinhala', 'colabs7' ),
		'sk_SK' => __( 'Slovak', 'colabs7' ),
		'sl_SI' => __( 'Slovene', 'colabs7' ),
		'es_ES' => __( 'Spanish', 'colabs7' ),
		'sv_SE' => __( 'Swedish', 'colabs7' ),
		'ta' => __( 'Tamil', 'colabs7' ),
		'th' => __( 'Thai', 'colabs7' ),
		'tl' => __( 'Tagalog', 'colabs7' ),
		'tr_TR' => __( 'Turkish', 'colabs7' ),
		'uk' => __( 'Ukrainian', 'colabs7' ),
		'vi' => __( 'Vietnamese', 'colabs7' )
	);

	return $l10n;
}

function colabs7_is_rtl() {
	if ( function_exists( 'is_rtl' ) )
		return is_rtl();

	return false;
}

function colabs7_ajax_loader() {
	$url = colabs7_plugin_url( 'images/ajax-loader.gif' );

	if ( is_ssl() && 'http:' == substr( $url, 0, 5 ) )
		$url = 'https:' . substr( $url, 5 );

	return apply_filters( 'colabs7_ajax_loader', $url );
}

function colabs7_verify_nonce( $nonce, $action = -1 ) {
	if ( substr( wp_hash( $action, 'nonce' ), -12, 10 ) == $nonce )
		return true;

	return false;
}

function colabs7_create_nonce( $action = -1 ) {
	return substr( wp_hash( $action, 'nonce' ), -12, 10 );
}

function colabs7_blacklist_check( $target ) {
	$mod_keys = trim( get_option( 'blacklist_keys' ) );

	if ( empty( $mod_keys ) )
		return false;

	$words = explode( "\n", $mod_keys );

	foreach ( (array) $words as $word ) {
		$word = trim( $word );

		if ( empty( $word ) )
			continue;

		if ( preg_match( '#' . preg_quote( $word, '#' ) . '#', $target ) )
			return true;
	}

	return false;
}

function colabs7_array_flatten( $input ) {
	if ( ! is_array( $input ) )
		return array( $input );

	$output = array();

	foreach ( $input as $value )
		$output = array_merge( $output, colabs7_array_flatten( $value ) );

	return $output;
}

function colabs7_flat_join( $input ) {
	$input = colabs7_array_flatten( $input );
	$output = array();

	foreach ( (array) $input as $value )
		$output[] = trim( (string) $value );

	return implode( ', ', $output );
}

function colabs7_support_html5() {
	return (bool) apply_filters( 'colabs7_support_html5', true );
}

function colabs7_support_html5_fallback() {
	return (bool) apply_filters( 'colabs7_support_html5_fallback', false );
}

function colabs7_format_atts( $atts ) {
	$html = '';

	$prioritized_atts = array( 'type', 'name', 'value' );

	foreach ( $prioritized_atts as $att ) {
		if ( isset( $atts[$att] ) ) {
			$value = trim( $atts[$att] );
			$html .= sprintf( ' %s="%s"', $att, esc_attr( $value ) );
			unset( $atts[$att] );
		}
	}

	foreach ( $atts as $key => $value ) {
		$value = trim( $value );

		if ( '' !== $value )
			$html .= sprintf( ' %s="%s"', $key, esc_attr( $value ) );
	}

	$html = trim( $html );

	return $html;
}

?>