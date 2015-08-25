<?php
/**
** A base module for [file] and [file*]
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_file', 5 );

function colabs7_add_shortcode_file() {
	colabs7_add_shortcode( array( 'file', 'file*' ),
		'colabs7_file_shortcode_handler', true );
}

function colabs7_file_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

	if ( $tag->is_required() )
		$atts['aria-required'] = 'true';

	$atts['type'] = 'file';
	$atts['name'] = $tag->name;
	$atts['value'] = '1';

	$atts = colabs7_format_atts( $atts );

	$html = sprintf(
		'<span class="colabs7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		$tag->name, $atts, $validation_error );

	return $html;
}


/* Encode type filter */

add_filter( 'colabs7_form_enctype', 'colabs7_file_form_enctype_filter' );

function colabs7_file_form_enctype_filter( $enctype ) {
	$multipart = (bool) colabs7_scan_shortcode( array( 'type' => array( 'file', 'file*' ) ) );

	if ( $multipart )
		$enctype = ' enctype="multipart/form-data"';

	return $enctype;
}


/* Validation + upload handling filter */

add_filter( 'colabs7_validate_file', 'colabs7_file_validation_filter', 10, 2 );
add_filter( 'colabs7_validate_file*', 'colabs7_file_validation_filter', 10, 2 );

function colabs7_file_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$name = $tag->name;

	$file = isset( $_FILES[$name] ) ? $_FILES[$name] : null;

	if ( $file['error'] && UPLOAD_ERR_NO_FILE != $file['error'] ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'upload_failed_php_error' );
		return $result;
	}

	if ( empty( $file['tmp_name'] ) && $tag->is_required() ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'invalid_required' );
		return $result;
	}

	if ( ! is_uploaded_file( $file['tmp_name'] ) )
		return $result;

	$allowed_file_types = array();

	if ( $file_types_a = $tag->get_option( 'filetypes' ) ) {
		foreach ( $file_types_a as $file_types ) {
			$file_types = explode( '|', $file_types );

			foreach ( $file_types as $file_type ) {
				$file_type = trim( $file_type, '.' );
				$file_type = str_replace( array( '.', '+', '*', '?' ),
					array( '\.', '\+', '\*', '\?' ), $file_type );
				$allowed_file_types[] = $file_type;
			}
		}
	}

	$allowed_file_types = array_unique( $allowed_file_types );
	$file_type_pattern = implode( '|', $allowed_file_types );

	$allowed_size = 1048576; // default size 1 MB

	if ( $file_size_a = $tag->get_option( 'limit' ) ) {
		$limit_pattern = '/^([1-9][0-9]*)([kKmM]?[bB])?$/';

		foreach ( $file_size_a as $file_size ) {
			if ( preg_match( $limit_pattern, $file_size, $matches ) ) {
				$allowed_size = (int) $matches[1];

				if ( ! empty( $matches[2] ) ) {
					$kbmb = strtolower( $matches[2] );

					if ( 'kb' == $kbmb )
						$allowed_size *= 1024;
					elseif ( 'mb' == $kbmb )
						$allowed_size *= 1024 * 1024;
				}

				break;
			}
		}
	}

	/* File type validation */

	// Default file-type restriction
	if ( '' == $file_type_pattern )
		$file_type_pattern = 'jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv';

	$file_type_pattern = trim( $file_type_pattern, '|' );
	$file_type_pattern = '(' . $file_type_pattern . ')';
	$file_type_pattern = '/\.' . $file_type_pattern . '$/i';

	if ( ! preg_match( $file_type_pattern, $file['name'] ) ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'upload_file_type_invalid' );
		return $result;
	}

	/* File size validation */

	if ( $file['size'] > $allowed_size ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'upload_file_too_large' );
		return $result;
	}

	$uploads_dir = colabs7_upload_tmp_dir();
	colabs7_init_uploads(); // Confirm upload dir

	$filename = $file['name'];

	// If you get script file, it's a danger. Make it TXT file.
	if ( preg_match( '/\.(php|pl|py|rb|cgi)\d?$/', $filename ) )
		$filename .= '.txt';

	$filename = wp_unique_filename( $uploads_dir, $filename );

	$new_file = trailingslashit( $uploads_dir ) . $filename;

	if ( false === @move_uploaded_file( $file['tmp_name'], $new_file ) ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'upload_failed' );
		return $result;
	}

	// Make sure the uploaded file is only readable for the owner process
	@chmod( $new_file, 0400 );

	if ( $contact_form = colabs7_get_current_contact_form() ) {
		$contact_form->uploaded_files[$name] = $new_file;

		if ( empty( $contact_form->posted_data[$name] ) )
			$contact_form->posted_data[$name] = $filename;
	}

	return $result;
}


/* Messages */

add_filter( 'colabs7_messages', 'colabs7_file_messages' );

function colabs7_file_messages( $messages ) {
	return array_merge( $messages, array(
		'upload_failed' => array(
			'description' => __( "Uploading a file fails for any reason", 'colabs7' ),
			'default' => __( 'Failed to upload file.', 'colabs7' )
		),

		'upload_file_type_invalid' => array(
			'description' => __( "Uploaded file is not allowed file type", 'colabs7' ),
			'default' => __( 'This file type is not allowed.', 'colabs7' )
		),

		'upload_file_too_large' => array(
			'description' => __( "Uploaded file is too large", 'colabs7' ),
			'default' => __( 'This file is too large.', 'colabs7' )
		),

		'upload_failed_php_error' => array(
			'description' => __( "Uploading a file fails for PHP error", 'colabs7' ),
			'default' => __( 'Failed to upload file. Error occurred.', 'colabs7' )
		)
	) );
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_file', 50 );

function colabs7_add_tag_generator_file() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'file', __( 'File upload', 'colabs7' ),
		'colabs7-tg-pane-file', 'colabs7_tg_pane_file' );
}

function colabs7_tg_pane_file( &$contact_form ) {
?>
<div id="colabs7-tg-pane-file" class="hidden">
<form action="">
<table>
<tr><td><input type="checkbox" name="required" />&nbsp;<?php echo esc_html( __( 'Required field?', 'colabs7' ) ); ?></td></tr>
<tr><td><?php echo esc_html( __( 'Name', 'colabs7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
</table>

<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( "File size limit", 'colabs7' ) ); ?> (<?php echo esc_html( __( 'bytes', 'colabs7' ) ); ?>) (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="limit" class="filesize oneline option" /></td>

<td><?php echo esc_html( __( "Acceptable file types", 'colabs7' ) ); ?> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="filetypes" class="filetype oneline option" /></td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?><br /><input type="text" name="file" class="tag" readonly="readonly" onfocus="this.select()" /></div>

<div class="tg-mail-tag"><?php echo esc_html( __( "And, put this code into the File Attachments field below.", 'colabs7' ) ); ?><br /><span class="arrow">&#11015;</span>&nbsp;<input type="text" class="mail-tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}


/* Warning message */

add_action( 'colabs7_admin_notices', 'colabs7_file_display_warning_message' );

function colabs7_file_display_warning_message() {
	if ( empty( $_GET['post'] ) || ! $contact_form = colabs7_contact_form( $_GET['post'] ) )
		return;

	$has_tags = (bool) $contact_form->form_scan_shortcode(
		array( 'type' => array( 'file', 'file*' ) ) );

	if ( ! $has_tags )
		return;

	$uploads_dir = colabs7_upload_tmp_dir();
	colabs7_init_uploads();

	if ( ! is_dir( $uploads_dir ) || ! wp_is_writable( $uploads_dir ) ) {
		$message = sprintf( __( 'This contact form contains file uploading fields, but the temporary folder for the files (%s) does not exist or is not writable. You can create the folder or change its permission manually.', 'colabs7' ), $uploads_dir );

		echo '<div class="error"><p><strong>' . esc_html( $message ) . '</strong></p></div>';
	}
}


/* File uploading functions */

function colabs7_init_uploads() {
	$dir = colabs7_upload_tmp_dir();
	wp_mkdir_p( trailingslashit( $dir ) );
	@chmod( $dir, 0733 );

	$htaccess_file = trailingslashit( $dir ) . '.htaccess';
	if ( file_exists( $htaccess_file ) )
		return;

	if ( $handle = @fopen( $htaccess_file, 'w' ) ) {
		fwrite( $handle, "Deny from all\n" );
		fclose( $handle );
	}
}

function colabs7_upload_tmp_dir() {
	if ( defined( 'COLABS7_UPLOADS_TMP_DIR' ) )
		return COLABS7_UPLOADS_TMP_DIR;
	else
		return colabs7_upload_dir( 'dir' ) . '/colabs7_uploads';
}

function colabs7_cleanup_upload_files() {
	$dir = trailingslashit( colabs7_upload_tmp_dir() );

	if ( ! is_dir( $dir ) )
		return false;
	if ( ! is_readable( $dir ) )
		return false;
	if ( ! wp_is_writable( $dir ) )
		return false;

	if ( $handle = @opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( $file == "." || $file == ".." || $file == ".htaccess" )
				continue;

			$stat = stat( $dir . $file );
			if ( $stat['mtime'] + 60 < time() ) // 60 secs
				@unlink( $dir . $file );
		}
		closedir( $handle );
	}
}

if ( ! is_admin() && 'GET' == $_SERVER['REQUEST_METHOD'] )
	colabs7_cleanup_upload_files();

?>