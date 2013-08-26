<?php
/**
** A base module for [captchac] and [captchar]
**/

/* Shortcode handler */

	add_action( 'init', 'colabs7_add_shortcode_captcha', 5 );

function colabs7_add_shortcode_captcha() {
	colabs7_add_shortcode( array( 'captchac', 'captchar' ),
		'colabs7_captcha_shortcode_handler', true );
}

function colabs7_captcha_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( 'captchac' == $tag->type && ! class_exists( 'ColabsSimpleCaptcha' ))
		return '';

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	if ( 'captchac' == $tag->type ) { // CAPTCHA-Challenge (image)
		$class .= ' colabs7-captcha-' . $tag->name;

		$atts = array();

		$atts['class'] = $tag->get_class_option( $class );
		$atts['id'] = $tag->get_option( 'id', 'id', true );

		$op = array( // Default
			'img_size' => array( 72, 24 ),
			'base' => array( 6, 18 ),
			'font_size' => 14,
			'font_char_width' => 15 );

		$op = array_merge( $op, colabs7_captchac_options( $tag->options ) );

		if ( ! $filename = colabs7_generate_captcha( $op ) )
			return '';

		if ( ! empty( $op['img_size'] ) ) {
			if ( isset( $op['img_size'][0] ) )
				$atts['width'] = $op['img_size'][0];

			if ( isset( $op['img_size'][1] ) )
				$atts['height'] = $op['img_size'][1];
		}

		$atts['alt'] = 'captcha';
		$atts['src'] = trailingslashit( colabs7_captcha_tmp_url() ) . $filename;

		$atts = colabs7_format_atts( $atts );

		$prefix = substr( $filename, 0, strrpos( $filename, '.' ) );

		$html = sprintf(
			'<input type="hidden" name="_colabs7_captcha_challenge_%1$s" value="%2$s" /><img %3$s />',
			$tag->name, $prefix, $atts );

		return $html;

	} elseif ( 'captchar' == $tag->type ) { // CAPTCHA-Response (input)
		if ( $validation_error )
			$class .= ' colabs7-not-valid';

		$atts = array();

		$atts['size'] = $tag->get_size_option( '40' );
		$atts['maxlength'] = $tag->get_maxlength_option();
		$atts['class'] = $tag->get_class_option( $class );
		$atts['id'] = $tag->get_option( 'id', 'id', true );
		$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

		$value = (string) reset( $tag->values );

		if ( colabs7_is_posted() )
			$value = '';

		if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
			$atts['placeholder'] = $value;
			$value = '';
		}

		$atts['value'] = $value;
		$atts['type'] = 'text';
		$atts['name'] = $tag->name;

		$atts = colabs7_format_atts( $atts );

		$html = sprintf(
			'<span class="colabs7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
			$tag->name, $atts, $validation_error );

		return $html;
	}
}


/* Validation filter */

add_filter( 'colabs7_validate_captchar', 'colabs7_captcha_validation_filter', 10, 2 );

function colabs7_captcha_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$type = $tag->type;
	$name = $tag->name;

	$captchac = '_colabs7_captcha_challenge_' . $name;

	$prefix = isset( $_POST[$captchac] ) ? (string) $_POST[$captchac] : '';
	$response = isset( $_POST[$name] ) ? (string) $_POST[$name] : '';

	if ( $prefix ) {
		if ( ! colabs7_check_captcha( $prefix, $response ) ) {
			$result['valid'] = false;
			$result['reason'][$name] = colabs7_get_message( 'captcha_not_match' );
		}

		colabs7_remove_captcha( $prefix );
	}

	return $result;
}


/* Ajax echo filter */

add_filter( 'colabs7_ajax_onload', 'colabs7_captcha_ajax_refill' );
add_filter( 'colabs7_ajax_json_echo', 'colabs7_captcha_ajax_refill' );

function colabs7_captcha_ajax_refill( $items ) {
	if ( ! is_array( $items ) )
		return $items;

	$fes = colabs7_scan_shortcode( array( 'type' => 'captchac' ) );

	if ( empty( $fes ) )
		return $items;

	$refill = array();

	foreach ( $fes as $fe ) {
		$name = $fe['name'];
		$options = $fe['options'];

		if ( empty( $name ) )
			continue;

		$op = colabs7_captchac_options( $options );
		if ( $filename = colabs7_generate_captcha( $op ) ) {
			$captcha_url = trailingslashit( colabs7_captcha_tmp_url() ) . $filename;
			$refill[$name] = $captcha_url;
		}
	}

	if ( ! empty( $refill ) )
		$items['captcha'] = $refill;

	return $items;
}


/* Messages */

add_filter( 'colabs7_messages', 'colabs7_captcha_messages' );

function colabs7_captcha_messages( $messages ) {
	return array_merge( $messages, array( 'captcha_not_match' => array(
		'description' => __( "The code that sender entered does not match the CAPTCHA", 'colabs7' ),
		'default' => __( 'Your entered code is incorrect.', 'colabs7' )
	) ) );
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_captcha', 45 );

function colabs7_add_tag_generator_captcha() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'captcha', __( 'CAPTCHA', 'colabs7' ),
		'colabs7-tg-pane-captcha', 'colabs7_tg_pane_captcha' );
}

function colabs7_tg_pane_captcha( &$contact_form ) {
?>
<div id="colabs7-tg-pane-captcha" class="hidden">
<form action="">
<table>

<?php if ( ! class_exists( 'ColabsSimpleCaptcha' ) ) : ?>
<tr><td colspan="2"><strong style="color: #e6255b"><?php echo esc_html( __( "Note: To use CAPTCHA, you need Colabs Simple CAPTCHA plugin installed.", 'colabs7' ) ); ?></strong><br /><a href="http://wordpress.org/extend/plugins/really-simple-captcha/">http://wordpress.org/extend/plugins/really-simple-captcha/</a></td></tr>
<?php endif; ?>

<tr><td><?php echo esc_html( __( 'Name', 'colabs7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
</table>

<table class="scope captchac">
<caption><?php echo esc_html( __( "Image settings", 'colabs7' ) ); ?></caption>

<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( "Foreground color", 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="fg" class="color oneline option" /></td>

<td><?php echo esc_html( __( "Background color", 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="bg" class="color oneline option" /></td>
</tr>

<tr><td colspan="2"><?php echo esc_html( __( "Image size", 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="checkbox" name="size:s" class="exclusive option" />&nbsp;<?php echo esc_html( __( "Small", 'colabs7' ) ); ?>&emsp;
<input type="checkbox" name="size:m" class="exclusive option" />&nbsp;<?php echo esc_html( __( "Medium", 'colabs7' ) ); ?>&emsp;
<input type="checkbox" name="size:l" class="exclusive option" />&nbsp;<?php echo esc_html( __( "Large", 'colabs7' ) ); ?>
</td></tr>
</table>

<table class="scope captchar">
<caption><?php echo esc_html( __( "Input field settings", 'colabs7' ) ); ?></caption>

<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><code>size</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="size" class="numeric oneline option" min="1" /></td>

<td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="maxlength" class="numeric oneline option" min="1" /></td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?>
<br />1) <?php echo esc_html( __( "For image", 'colabs7' ) ); ?>
<input type="text" name="captchac" class="tag" readonly="readonly" onfocus="this.select()" />
<br />2) <?php echo esc_html( __( "For input field", 'colabs7' ) ); ?>
<input type="text" name="captchar" class="tag" readonly="readonly" onfocus="this.select()" />
</div>
</form>
</div>
<?php
}


/* Warning message */

add_action( 'colabs7_admin_notices', 'colabs7_captcha_display_warning_message' );

function colabs7_captcha_display_warning_message() {
	if ( empty( $_GET['post'] ) || ! $contact_form = colabs7_contact_form( $_GET['post'] ) )
		return;

	$has_tags = (bool) $contact_form->form_scan_shortcode(
		array( 'type' => array( 'captchac' ) ) );

	if ( ! $has_tags )
		return;

	if ( ! class_exists( 'ColabsSimpleCaptcha' ) )
		return;

	$uploads_dir = colabs7_captcha_tmp_dir();
	colabs7_init_captcha();

	if ( ! is_dir( $uploads_dir ) || ! wp_is_writable( $uploads_dir ) ) {
		$message = sprintf( __( 'This contact form contains CAPTCHA fields, but the temporary folder for the files (%s) does not exist or is not writable. You can create the folder or change its permission manually.', 'colabs7' ), $uploads_dir );

		echo '<div class="error"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
	}

	if ( ! function_exists( 'imagecreatetruecolor' ) || ! function_exists( 'imagettftext' ) ) {
		$message = __( 'This contact form contains CAPTCHA fields, but the necessary libraries (GD and FreeType) are not available on your server.', 'colabs7' );

		echo '<div class="error"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
	}
}


/* CAPTCHA functions */

function colabs7_init_captcha() {
	global $colabs7_captcha;

	if ( ! class_exists( 'ColabsSimpleCaptcha' ) )
		return false;

	if ( ! is_object( $colabs7_captcha ) )
		$colabs7_captcha = new ColabsSimpleCaptcha();

	$dir = trailingslashit( colabs7_captcha_tmp_dir() );

	$colabs7_captcha->tmp_dir = $dir;

	if ( is_callable( array( $colabs7_captcha, 'make_tmp_dir' ) ) )
		return $colabs7_captcha->make_tmp_dir();

	if ( ! wp_mkdir_p( $dir ) )
		return false;

	$htaccess_file = $dir . '.htaccess';

	if ( file_exists( $htaccess_file ) )
		return true;

	if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
		fwrite( $handle, 'Order deny,allow' . "\n" );
		fwrite( $handle, 'Deny from all' . "\n" );
		fwrite( $handle, '<Files ~ "^[0-9A-Za-z]+\\.(jpeg|gif|png)$">' . "\n" );
		fwrite( $handle, '    Allow from all' . "\n" );
		fwrite( $handle, '</Files>' . "\n" );
		fclose( $handle );
	}

	return true;
}

function colabs7_captcha_tmp_dir() {
	if ( defined( 'COLABS7_CAPTCHA_TMP_DIR' ) )
		return COLABS7_CAPTCHA_TMP_DIR;
	else
		return colabs7_upload_dir( 'dir' ) . '/colabs7_captcha';
}

function colabs7_captcha_tmp_url() {
	if ( defined( 'COLABS7_CAPTCHA_TMP_URL' ) )
		return COLABS7_CAPTCHA_TMP_URL;
	else
		return colabs7_upload_dir( 'url' ) . '/colabs7_captcha';
}

function colabs7_generate_captcha( $options = null ) {
	global $colabs7_captcha;

	if ( ! colabs7_init_captcha() )
		return false;

	if ( ! is_dir( $colabs7_captcha->tmp_dir ) || ! wp_is_writable( $colabs7_captcha->tmp_dir ) )
		return false;

	$img_type = imagetypes();
	if ( $img_type & IMG_PNG )
		$colabs7_captcha->img_type = 'png';
	elseif ( $img_type & IMG_GIF )
		$colabs7_captcha->img_type = 'gif';
	elseif ( $img_type & IMG_JPG )
		$colabs7_captcha->img_type = 'jpeg';
	else
		return false;

	if ( is_array( $options ) ) {
		if ( isset( $options['img_size'] ) )
			$colabs7_captcha->img_size = $options['img_size'];
		if ( isset( $options['base'] ) )
			$colabs7_captcha->base = $options['base'];
		if ( isset( $options['font_size'] ) )
			$colabs7_captcha->font_size = $options['font_size'];
		if ( isset( $options['font_char_width'] ) )
			$colabs7_captcha->font_char_width = $options['font_char_width'];
		if ( isset( $options['fg'] ) )
			$colabs7_captcha->fg = $options['fg'];
		if ( isset( $options['bg'] ) )
			$colabs7_captcha->bg = $options['bg'];
	}

	$prefix = mt_rand();
	$captcha_word = $colabs7_captcha->generate_random_word();
	return $colabs7_captcha->generate_image( $prefix, $captcha_word );
}

function colabs7_check_captcha( $prefix, $response ) {
	global $colabs7_captcha;

	if ( ! colabs7_init_captcha() )
		return false;

	return $colabs7_captcha->check( $prefix, $response );
}

function colabs7_remove_captcha( $prefix ) {
	global $colabs7_captcha;

	if ( ! colabs7_init_captcha() )
		return false;

	if ( preg_match( '/[^0-9]/', $prefix ) ) // Colabs Contact Form generates $prefix with mt_rand()
		return false;

	$colabs7_captcha->remove( $prefix );
}

function colabs7_cleanup_captcha_files() {
	global $colabs7_captcha;

	if ( ! colabs7_init_captcha() )
		return false;

	if ( is_callable( array( $colabs7_captcha, 'cleanup' ) ) )
		return $colabs7_captcha->cleanup();

	$dir = trailingslashit( colabs7_captcha_tmp_dir() );

	if ( ! is_dir( $dir ) || ! is_readable( $dir ) || ! wp_is_writable( $dir ) )
		return false;

	if ( $handle = @opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( ! preg_match( '/^[0-9]+\.(php|txt|png|gif|jpeg)$/', $file ) )
				continue;

			$stat = @stat( $dir . $file );
			if ( $stat['mtime'] + 3600 < time() ) // 3600 secs == 1 hour
				@unlink( $dir . $file );
		}
		closedir( $handle );
	}
}

if ( ! is_admin() && 'GET' == $_SERVER['REQUEST_METHOD'] )
	colabs7_cleanup_captcha_files();

function colabs7_captchac_options( $options ) {
	if ( ! is_array( $options ) )
		return array();

	$op = array();
	$image_size_array = preg_grep( '%^size:[smlSML]$%', $options );

	if ( $image_size = array_shift( $image_size_array ) ) {
		preg_match( '%^size:([smlSML])$%', $image_size, $is_matches );
		switch ( strtolower( $is_matches[1] ) ) {
			case 's':
				$op['img_size'] = array( 60, 20 );
				$op['base'] = array( 6, 15 );
				$op['font_size'] = 11;
				$op['font_char_width'] = 13;
				break;
			case 'l':
				$op['img_size'] = array( 84, 28 );
				$op['base'] = array( 6, 20 );
				$op['font_size'] = 17;
				$op['font_char_width'] = 19;
				break;
			case 'm':
			default:
				$op['img_size'] = array( 72, 24 );
				$op['base'] = array( 6, 18 );
				$op['font_size'] = 14;
				$op['font_char_width'] = 15;
		}
	}

	$fg_color_array = preg_grep( '%^fg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%', $options );
	if ( $fg_color = array_shift( $fg_color_array ) ) {
		preg_match( '%^fg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%', $fg_color, $fc_matches );
		if ( 3 == strlen( $fc_matches[1] ) ) {
			$r = substr( $fc_matches[1], 0, 1 );
			$g = substr( $fc_matches[1], 1, 1 );
			$b = substr( $fc_matches[1], 2, 1 );
			$op['fg'] = array( hexdec( $r . $r ), hexdec( $g . $g ), hexdec( $b . $b ) );
		} elseif ( 6 == strlen( $fc_matches[1] ) ) {
			$r = substr( $fc_matches[1], 0, 2 );
			$g = substr( $fc_matches[1], 2, 2 );
			$b = substr( $fc_matches[1], 4, 2 );
			$op['fg'] = array( hexdec( $r ), hexdec( $g ), hexdec( $b ) );
		}
	}

	$bg_color_array = preg_grep( '%^bg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%', $options );
	if ( $bg_color = array_shift( $bg_color_array ) ) {
		preg_match( '%^bg:#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$%', $bg_color, $bc_matches );
		if ( 3 == strlen( $bc_matches[1] ) ) {
			$r = substr( $bc_matches[1], 0, 1 );
			$g = substr( $bc_matches[1], 1, 1 );
			$b = substr( $bc_matches[1], 2, 1 );
			$op['bg'] = array( hexdec( $r . $r ), hexdec( $g . $g ), hexdec( $b . $b ) );
		} elseif ( 6 == strlen( $bc_matches[1] ) ) {
			$r = substr( $bc_matches[1], 0, 2 );
			$g = substr( $bc_matches[1], 2, 2 );
			$b = substr( $bc_matches[1], 4, 2 );
			$op['bg'] = array( hexdec( $r ), hexdec( $g ), hexdec( $b ) );
		}
	}

	return $op;
}

$colabs7_captcha = null;

?>