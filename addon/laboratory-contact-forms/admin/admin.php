<?php

require_once COLABS7_PLUGIN_DIR . '/admin/admin-functions.php';

add_action( 'admin_menu', 'colabs7_admin_menu' );

function colabs7_admin_menu() {
	/* add_object_page( __( 'Colabs Contact Form', 'colabs7' ), __( 'Contact', 'colabs7' ),
		'colabs7_read_contact_forms', 'colabs7', 'colabs7_admin_management_page',
		colabs7_plugin_url( 'admin/images/menu-icon.png' ) );

	$contact_form_admin = add_submenu_page( 'laboratory',
		__( 'Contact Forms', 'colabs7' ), __( 'Edit', 'colabs7' ),
		'colabs7_read_contact_forms', 'colabs7', 'colabs7_admin_management_page' );

	add_action( 'load-' . $contact_form_admin, 'colabs7_load_contact_form_admin' );
	 */
	
	$contact_form_admin = add_submenu_page( 'laboratory', 
		__( 'Contact Forms', 'colabs7' ), __( 'Contact Forms', 'colabs7' ), 
		'colabs7_read_contact_forms', 
		'colabs7', 
		'colabs7_admin_management_page' );
	add_action( 'load-' . $contact_form_admin, 'colabs7_load_contact_form_admin' );
	
}

add_filter( 'set-screen-option', 'colabs7_set_screen_options', 10, 3 );

function colabs7_set_screen_options( $result, $option, $value ) {
	$colabs7_screens = array(
		'cfseven_contact_forms_per_page' );

	if ( in_array( $option, $colabs7_screens ) )
		$result = $value;

	return $result;
}

function colabs7_load_contact_form_admin() {
	global $colabs7_contact_form;

	$action = colabs7_current_action();

	if ( 'save' == $action ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'colabs7-save-contact-form_' . $id );

		if ( ! current_user_can( 'colabs7_edit_contact_form', $id ) )
			wp_die( __( 'You are not allowed to edit this item.', 'colabs7' ) );

		if ( ! $contact_form = colabs7_contact_form( $id ) ) {
			$contact_form = new COLABS7_ContactForm();
			$contact_form->initial = true;
		}

		$contact_form->title = trim( $_POST['colabs7-title'] );
		$contact_form->locale = trim( $_POST['colabs7-locale'] );

		$form = trim( $_POST['colabs7-form'] );

		$mail = array(
			'subject' => trim( $_POST['colabs7-mail-subject'] ),
			'sender' => trim( $_POST['colabs7-mail-sender'] ),
			'body' => trim( $_POST['colabs7-mail-body'] ),
			'recipient' => trim( $_POST['colabs7-mail-recipient'] ),
			'additional_headers' => trim( $_POST['colabs7-mail-additional-headers'] ),
			'attachments' => trim( $_POST['colabs7-mail-attachments'] ),
			'use_html' =>
				isset( $_POST['colabs7-mail-use-html'] ) && 1 == $_POST['colabs7-mail-use-html']
		);

		$mail_2 = array(
			'active' =>
				isset( $_POST['colabs7-mail-2-active'] ) && 1 == $_POST['colabs7-mail-2-active'],
			'subject' => trim( $_POST['colabs7-mail-2-subject'] ),
			'sender' => trim( $_POST['colabs7-mail-2-sender'] ),
			'body' => trim( $_POST['colabs7-mail-2-body'] ),
			'recipient' => trim( $_POST['colabs7-mail-2-recipient'] ),
			'additional_headers' => trim( $_POST['colabs7-mail-2-additional-headers'] ),
			'attachments' => trim( $_POST['colabs7-mail-2-attachments'] ),
			'use_html' =>
				isset( $_POST['colabs7-mail-2-use-html'] ) && 1 == $_POST['colabs7-mail-2-use-html']
		);

		$messages = isset( $contact_form->messages ) ? $contact_form->messages : array();

		foreach ( colabs7_messages() as $key => $arr ) {
			$field_name = 'colabs7-message-' . strtr( $key, '_', '-' );
			if ( isset( $_POST[$field_name] ) )
				$messages[$key] = trim( $_POST[$field_name] );
		}

		$additional_settings = trim( $_POST['colabs7-additional-settings'] );

		$props = apply_filters( 'colabs7_contact_form_admin_posted_properties',
			compact( 'form', 'mail', 'mail_2', 'messages', 'additional_settings' ) );

		foreach ( (array) $props as $key => $prop )
			$contact_form->{$key} = $prop;

		$query = array();
		$query['message'] = ( $contact_form->initial ) ? 'created' : 'saved';

		$contact_form->save();

		$query['post'] = $contact_form->id;

		$redirect_to = add_query_arg( $query, menu_page_url( 'colabs7', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'copy' == $action ) {
		$id = empty( $_POST['post_ID'] )
			? absint( $_REQUEST['post'] )
			: absint( $_POST['post_ID'] );

		check_admin_referer( 'colabs7-copy-contact-form_' . $id );

		if ( ! current_user_can( 'colabs7_edit_contact_form', $id ) )
			wp_die( __( 'You are not allowed to edit this item.', 'colabs7' ) );

		$query = array();

		if ( $contact_form = colabs7_contact_form( $id ) ) {
			$new_contact_form = $contact_form->copy();
			$new_contact_form->save();

			$query['post'] = $new_contact_form->id;
			$query['message'] = 'created';
		} else {
			$query['post'] = $contact_form->id;
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'colabs7', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete' == $action ) {
		if ( ! empty( $_POST['post_ID'] ) )
			check_admin_referer( 'colabs7-delete-contact-form_' . $_POST['post_ID'] );
		elseif ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'colabs7-delete-contact-form_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$posts = empty( $_POST['post_ID'] )
			? (array) $_REQUEST['post']
			: (array) $_POST['post_ID'];

		$deleted = 0;

		foreach ( $posts as $post ) {
			$post = new COLABS7_ContactForm( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'colabs7_delete_contact_form', $post->id ) )
				wp_die( __( 'You are not allowed to delete this item.', 'colabs7' ) );

			if ( ! $post->delete() )
				wp_die( __( 'Error in deleting.', 'colabs7' ) );

			$deleted += 1;
		}

		$query = array();

		if ( ! empty( $deleted ) )
			$query['message'] = 'deleted';

		$redirect_to = add_query_arg( $query, menu_page_url( 'colabs7', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	$_GET['post'] = isset( $_GET['post'] ) ? $_GET['post'] : '';

	$post = null;

	if ( 'new' == $_GET['post'] && current_user_can( 'colabs7_edit_contact_forms' ) )
		$post = colabs7_get_contact_form_default_pack(
			array( 'locale' => ( isset( $_GET['locale'] ) ? $_GET['locale'] : '' ) ) );
	elseif ( ! empty( $_GET['post'] ) )
		$post = colabs7_contact_form( $_GET['post'] );

	if ( $post && current_user_can( 'colabs7_edit_contact_form', $post->id ) ) {
		colabs7_add_meta_boxes( $post->id );

	} else {
		$current_screen = get_current_screen();

		if ( ! class_exists( 'COLABS7_Contact_Form_List_Table' ) )
			require_once COLABS7_PLUGIN_DIR . '/admin/includes/class-contact-forms-list-table.php';

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'COLABS7_Contact_Form_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'label' => __( 'Contact Forms', 'colabs7' ),
			'default' => 20,
			'option' => 'cfseven_contact_forms_per_page' ) );
	}

	$colabs7_contact_form = $post;
}

add_action( 'admin_enqueue_scripts', 'colabs7_admin_enqueue_scripts' );

function colabs7_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'colabs7' ) )
		return;

	wp_enqueue_style( 'colabs-contact-form-admin',
		colabs7_plugin_url( 'admin/css/styles.css' ),
		array( 'thickbox' ), COLABS7_VERSION, 'all' );

	if ( colabs7_is_rtl() ) {
		wp_enqueue_style( 'colabs-contact-form-admin-rtl',
			colabs7_plugin_url( 'admin/css/styles-rtl.css' ),
			array(), COLABS7_VERSION, 'all' );
	}

	wp_enqueue_script( 'colabs7-admin-taggenerator',
		colabs7_plugin_url( 'admin/js/taggenerator.js' ),
		array( 'jquery' ), COLABS7_VERSION, true );

	wp_enqueue_script( 'colabs7-admin',
		colabs7_plugin_url( 'admin/js/scripts.js' ),
		array( 'jquery', 'thickbox', 'postbox', 'colabs7-admin-taggenerator' ),
		COLABS7_VERSION, true );

	$current_screen = get_current_screen();

	wp_localize_script( 'colabs7-admin', '_colabs7', array(
		'screenId' => $current_screen->id,
		'generateTag' => __( 'Generate Tag', 'colabs7' ),
		'pluginUrl' => colabs7_plugin_url(),
		'tagGenerators' => colabs7_tag_generators() ) );
}

function colabs7_admin_management_page() {
	global $colabs7_contact_form;

	if ( $colabs7_contact_form ) {
		$post =& $colabs7_contact_form;
		$post_id = $post->initial ? -1 : $post->id;

		require_once COLABS7_PLUGIN_DIR . '/admin/includes/meta-boxes.php';
		require_once COLABS7_PLUGIN_DIR . '/admin/edit-contact-form.php';
		return;
	}

	$list_table = new COLABS7_Contact_Form_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">
<?php screen_icon(); ?>

<h2><?php
	echo esc_html( __( 'Colabs Contact Form', 'colabs7' ) );

	echo ' <a href="#TB_inline?height=300&width=400&inlineId=colabs7-lang-select-modal" class="add-new-h2 thickbox">' . esc_html( __( 'Add New', 'colabs7' ) ) . '</a>';

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'colabs7' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
?></h2>

<?php do_action( 'colabs7_admin_notices' ); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Contact Forms', 'colabs7' ), 'colabs7-contact' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
	colabs7_admin_lang_select_modal();
}

function colabs7_admin_lang_select_modal() {
	$available_locales = colabs7_l10n();
	$default_locale = get_locale();

	if ( ! isset( $available_locales[$default_locale] ) )
		$default_locale = 'en_US';

?>
<div id="colabs7-lang-select-modal" class="hidden">
<h4><?php echo esc_html( sprintf( __( 'Use the default language (%s)', 'colabs7' ), $available_locales[$default_locale] ) ); ?></h4>
<p><a href="<?php echo esc_url( add_query_arg( array( 'post' => 'new' ), menu_page_url( 'colabs7', false ) ) ); ?>" class="button" /><?php echo esc_html( __( 'Add New', 'colabs7' ) ); ?></a></p>

<?php unset( $available_locales[$default_locale] ); ?>
<h4><?php echo esc_html( __( 'Or', 'colabs7' ) ); ?></h4>
<form action="" method="get">
<input type="hidden" name="page" value="colabs7" />
<input type="hidden" name="post" value="new" />
<select name="locale">
<option value="" selected="selected"><?php echo esc_html( __( '(select language)', 'colabs7' ) ); ?></option>
<?php foreach ( $available_locales as $code => $locale ) : ?>
<option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $locale ); ?></option>
<?php endforeach; ?>
</select>
<input type="submit" class="button" value="<?php echo esc_attr( __( 'Add New', 'colabs7' ) ); ?>" />
</form>
</div>
<?php
}

function colabs7_add_meta_boxes( $post_id ) {
	add_meta_box( 'formdiv', __( 'Form', 'colabs7' ),
		'colabs7_form_meta_box', null, 'form', 'core' );

	add_meta_box( 'maildiv', __( 'Mail', 'colabs7' ),
		'colabs7_mail_meta_box', null, 'mail', 'core' );

	add_meta_box( 'mail2div', __( 'Mail (2)', 'colabs7' ),
		'colabs7_mail_meta_box', null, 'mail_2', 'core',
		array(
			'id' => 'colabs7-mail-2',
			'name' => 'mail_2',
			'use' => __( 'Use mail (2)', 'colabs7' ) ) );

	add_meta_box( 'messagesdiv', __( 'Messages', 'colabs7' ),
		'colabs7_messages_meta_box', null, 'messages', 'core' );

	add_meta_box( 'additionalsettingsdiv', __( 'Additional Settings', 'colabs7' ),
		'colabs7_additional_settings_meta_box', null, 'additional_settings', 'core' );

	do_action( 'colabs7_add_meta_boxes', $post_id );
}

/* Misc */

add_action( 'colabs7_admin_notices', 'colabs7_admin_before_subsubsub' );

function colabs7_admin_before_subsubsub() {
	// colabs7_admin_before_subsubsub is deprecated. Use colabs7_admin_notices instead.

	$current_screen = get_current_screen();

	if ( 'toplevel_page_colabs7' != $current_screen->id )
		return;

	if ( empty( $_GET['post'] ) || ! $contact_form = colabs7_contact_form( $_GET['post'] ) )
		return;

	do_action_ref_array( 'colabs7_admin_before_subsubsub', array( &$contact_form ) );
}

add_action( 'colabs7_admin_notices', 'colabs7_admin_updated_message' );

function colabs7_admin_updated_message() {
	if ( empty( $_REQUEST['message'] ) )
		return;

	if ( 'created' == $_REQUEST['message'] )
		$updated_message = esc_html( __( 'Contact form created.', 'colabs7' ) );
	elseif ( 'saved' == $_REQUEST['message'] )
		$updated_message = esc_html( __( 'Contact form saved.', 'colabs7' ) );
	elseif ( 'deleted' == $_REQUEST['message'] )
		$updated_message = esc_html( __( 'Contact form deleted.', 'colabs7' ) );

	if ( empty( $updated_message ) )
		return;

?>
<div id="message" class="updated"><p><?php echo $updated_message; ?></p></div>
<?php
}

add_filter( 'plugin_action_links', 'colabs7_plugin_action_links', 10, 2 );

function colabs7_plugin_action_links( $links, $file ) {
	if ( $file != COLABS7_PLUGIN_BASENAME )
		return $links;

	$settings_link = '<a href="' . menu_page_url( 'colabs7', false ) . '">'
		. esc_html( __( 'Settings', 'colabs7' ) ) . '</a>';

	array_unshift( $links, $settings_link );

	return $links;
}

?>