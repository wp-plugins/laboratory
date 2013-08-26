<?php

require_once CRM_PLUGIN_DIR . '/admin/admin-functions.php';

add_action( 'admin_menu', 'crm_admin_menu' );

function crm_admin_menu() {
	
	$contact_admin = add_submenu_page( 'laboratory',
		__( 'Crm Address Book', 'crm' ), __( 'Address Book', 'crm' ),
		'crm_edit_contacts', 'crm', 'crm_contact_admin_page' );

	add_action( 'load-' . $contact_admin, 'crm_load_contact_admin' );

	$inbound_admin = add_submenu_page( 'laboratory',
		__( 'Crm Inbound Messages', 'crm' ), __( 'Inbound Messages', 'crm' ),
		'crm_edit_inbound_messages', 'crm_inbound', 'crm_inbound_admin_page' );

	add_action( 'load-' . $inbound_admin, 'crm_load_inbound_admin' );
	
}

add_filter( 'set-screen-option', 'crm_set_screen_options', 10, 3 );

function crm_set_screen_options( $result, $option, $value ) {
	$crm_screens = array(
		'toplevel_page_crm_per_page',
		'crm_page_crm_inbound_per_page' );

	if ( in_array( $option, $crm_screens ) )
		$result = $value;

	return $result;
}

add_action( 'admin_enqueue_scripts', 'crm_admin_enqueue_scripts' );

function crm_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'crm' ) )
		return;

	wp_enqueue_style( 'crm-admin',
		crm_plugin_url( 'admin/style.css' ),
		array(), CRM_VERSION, 'all' );

	wp_enqueue_script( 'crm-admin',
		crm_plugin_url( 'admin/script.js' ),
		array( 'postbox' ), CRM_VERSION, true );

	$current_screen = get_current_screen();

	wp_localize_script( 'crm-admin', '_crm', array(
		'screenId' => $current_screen->id ) );
}

/* Updated Message */

add_action( 'crm_admin_updated_message', 'crm_admin_updated_message' );

function crm_admin_updated_message() {
	if ( ! empty( $_REQUEST['message'] ) ) {
		if ( 'contactupdated' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Contact updated.', 'crm' ) );
		elseif ( 'contactdeleted' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Contact deleted.', 'crm' ) );
		elseif ( 'inboundtrashed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages trashed.', 'crm' ) );
		elseif ( 'inbounduntrashed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages restored.', 'crm' ) );
		elseif ( 'inbounddeleted' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages deleted.', 'crm' ) );
		elseif ( 'inboundspammed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages got marked as spam.', 'crm' ) );
		elseif ( 'inboundunspammed' == $_REQUEST['message'] )
			$updated_message = esc_html( __( 'Messages got marked as not spam.', 'crm' ) );
		else
			return;
	} else {
		return;
	}

	if ( empty( $updated_message ) )
		return;

?>
<div id="message" class="updated"><p><?php echo $updated_message; ?></p></div>
<?php
}

/* Contact */

function crm_load_contact_admin() {
	$action = crm_current_action();

	$redirect_to = admin_url( 'admin.php?page=crm' );

	if ( 'save' == $action && ! empty( $_REQUEST['post'] ) ) {
		$post = new Crm_Contact( $_REQUEST['post'] );

		if ( ! empty( $post ) ) {
			if ( ! current_user_can( 'crm_edit_contact', $post->id ) )
				wp_die( __( 'You are not allowed to edit this item.', 'crm' ) );

			check_admin_referer( 'crm-update-contact_' . $post->id );

			$post->props = (array) $_POST['contact'];

			$post->name = trim( $_POST['contact']['name'] );

			$post->tags = ! empty( $_POST['tax_input'][Crm_Contact::contact_tag_taxonomy] )
				? explode( ',', $_POST['tax_input'][Crm_Contact::contact_tag_taxonomy] )
				: array();

			$post->save();

			$redirect_to = add_query_arg( array(
				'action' => 'edit',
				'post' => $post->id,
				'message' => 'contactupdated' ), $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'crm-delete-contact_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$deleted = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new Crm_Contact( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'crm_delete_contact', $post->id ) )
				wp_die( __( 'You are not allowed to delete this item.', 'crm' ) );

			if ( ! $post->delete() )
				wp_die( __( 'Error in deleting.', 'crm' ) );

			$deleted += 1;
		}

		if ( ! empty( $deleted ) )
			$redirect_to = add_query_arg( array( 'message' => 'contactdeleted' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( ! empty( $_GET['export'] ) ) {
		$sitename = sanitize_key( get_bloginfo( 'name' ) );

		$filename = ( empty( $sitename ) ? '' : $sitename . '-' )
			. sprintf( 'crm-contact-%s.csv', date( 'Y-m-d' ) );

		header( 'Content-Description: File Transfer' );
		header( "Content-Disposition: attachment; filename=$filename" );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		$labels = array(
			__( 'Email', 'crm' ), __( 'Full name', 'crm' ),
			__( 'First name', 'crm' ), __( 'Last name', 'crm' ) );

		echo crm_csv_row( $labels );

		$args = array(
			'posts_per_page' => -1,
			'orderby' => 'meta_value',
			'order' => 'ASC',
			'meta_key' => '_email' );

		if ( ! empty( $_GET['s'] ) )
			$args['s'] = $_GET['s'];

		if ( ! empty( $_GET['orderby'] ) ) {
			if ( 'email' == $_GET['orderby'] )
				$args['meta_key'] = '_email';
			elseif ( 'name' == $_GET['orderby'] )
				$args['meta_key'] = '_name';
		}

		if ( ! empty( $_GET['order'] ) && 'asc' == strtolower( $_GET['order'] ) )
			$args['order'] = 'ASC';

		if ( ! empty( $_GET['contact_tag_id'] ) )
			$args['contact_tag_id'] = explode( ',', $_GET['contact_tag_id'] );

		$items = Crm_Contact::find( $args );

		foreach ( $items as $item ) {
			$row = array(
				$item->email,
				$item->get_prop( 'name' ),
				$item->get_prop( 'first_name' ),
				$item->get_prop( 'last_name' ) );

			echo "\r\n" . crm_csv_row( $row );
		}

		exit();
	}

	$post_id = ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';

	if ( Crm_Contact::post_type == get_post_type( $post_id ) ) {
		add_meta_box( 'submitdiv', __( 'Save', 'crm' ),
			'crm_contact_submit_meta_box', null, 'side', 'core' );

		add_meta_box( 'contacttagsdiv', __( 'Tags', 'crm' ),
			'crm_contact_tags_meta_box', null, 'side', 'core' );

		add_meta_box( 'contactnamediv', __( 'Name', 'crm' ),
			'crm_contact_name_meta_box', null, 'normal', 'core' );

	} else {
		if ( ! class_exists( 'Crm_Contacts_List_Table' ) )
			require_once CRM_PLUGIN_DIR . '/admin/includes/class-contacts-list-table.php';

		$current_screen = get_current_screen();

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'Crm_Contacts_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'label' => __( 'Contacts', 'crm' ),
			'default' => 20 ) );
	}
}

function crm_contact_admin_page() {
	$action = crm_current_action();
	$post_id = ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';

	if ( 'edit' == $action && Crm_Contact::post_type == get_post_type( $post_id ) ) {
		crm_contact_edit_page();
		return;
	}

	$list_table = new Crm_Contacts_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">
<?php screen_icon(); ?>

<h2><?php
	echo esc_html( __( 'Crm Address Book', 'crm' ) );

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'crm' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
?></h2>

<?php do_action( 'crm_admin_updated_message' ); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Contacts', 'crm' ), 'crm-contact' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}

function crm_contact_edit_page() {
	$post = new Crm_Contact( $_REQUEST['post'] );

	if ( empty( $post ) )
		return;

	require_once CRM_PLUGIN_DIR . '/admin/includes/meta-boxes.php';

	include CRM_PLUGIN_DIR . '/admin/edit-contact-form.php';
}

/* Inbound Message */

function crm_load_inbound_admin() {
	$action = crm_current_action();

	$redirect_to = admin_url( 'admin.php?page=crm_inbound' );

	if ( 'trash' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'crm-trash-inbound-message_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$trashed = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new Crm_Inbound_Message( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'crm_delete_inbound_message', $post->id ) )
				wp_die( __( 'You are not allowed to move this item to the Trash.', 'crm' ) );

			if ( ! $post->trash() )
				wp_die( __( 'Error in moving to Trash.', 'crm' ) );

			$trashed += 1;
		}

		if ( ! empty( $trashed ) )
			$redirect_to = add_query_arg( array( 'message' => 'inboundtrashed' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'untrash' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'crm-untrash-inbound-message_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$untrashed = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new Crm_Inbound_Message( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'crm_delete_inbound_message', $post->id ) )
				wp_die( __( 'You are not allowed to restore this item from the Trash.', 'crm' ) );

			if ( ! $post->untrash() )
				wp_die( __( 'Error in restoring from Trash.', 'crm' ) );

			$untrashed += 1;
		}

		if ( ! empty( $untrashed ) )
			$redirect_to = add_query_arg( array( 'message' => 'inbounduntrashed' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete_all' == $action ) {
		$_REQUEST['post'] = crm_get_all_ids_in_trash(
			Crm_Inbound_Message::post_type );

		$action = 'delete';
	}

	if ( 'delete' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'crm-delete-inbound-message_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$deleted = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new Crm_Inbound_Message( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'crm_delete_inbound_message', $post->id ) )
				wp_die( __( 'You are not allowed to delete this item.', 'crm' ) );

			if ( ! $post->delete() )
				wp_die( __( 'Error in deleting.', 'crm' ) );

			$deleted += 1;
		}

		if ( ! empty( $deleted ) )
			$redirect_to = add_query_arg( array( 'message' => 'inbounddeleted' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'spam' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'crm-spam-inbound-message_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$submitted = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new Crm_Inbound_Message( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'crm_spam_inbound_message', $post->id ) )
				wp_die( __( 'You are not allowed to spam this item.', 'crm' ) );

			if ( $post->spam() )
				$submitted += 1;
		}

		if ( ! empty( $submitted ) )
			$redirect_to = add_query_arg( array( 'message' => 'inboundspammed' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'unspam' == $action && ! empty( $_REQUEST['post'] ) ) {
		if ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'crm-unspam-inbound-message_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$submitted = 0;

		foreach ( (array) $_REQUEST['post'] as $post ) {
			$post = new Crm_Inbound_Message( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'crm_unspam_inbound_message', $post->id ) )
				wp_die( __( 'You are not allowed to unspam this item.', 'crm' ) );

			if ( $post->unspam() )
				$submitted += 1;
		}

		if ( ! empty( $submitted ) )
			$redirect_to = add_query_arg( array( 'message' => 'inboundunspammed' ), $redirect_to );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	$post_id = ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';

	if ( Crm_Inbound_Message::post_type == get_post_type( $post_id ) ) {
		add_meta_box( 'submitdiv', __( 'Save', 'crm' ),
			'crm_inbound_submit_meta_box', null, 'side', 'core' );

		add_meta_box( 'inboundfieldsdiv', __( 'Fields', 'crm' ),
			'crm_inbound_fields_meta_box', null, 'normal', 'core' );

	} else {
		if ( ! class_exists( 'Crm_Inbound_Messages_List_Table' ) )
			require_once CRM_PLUGIN_DIR . '/admin/includes/class-inbound-messages-list-table.php';

		$current_screen = get_current_screen();

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'Crm_Inbound_Messages_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'label' => __( 'Messages', 'crm' ),
			'default' => 20 ) );
	}
}

function crm_inbound_admin_page() {
	$action = crm_current_action();
	$post_id = ! empty( $_REQUEST['post'] ) ? $_REQUEST['post'] : '';

	if ( 'edit' == $action && Crm_Inbound_Message::post_type == get_post_type( $post_id ) ) {
		crm_inbound_edit_page();
		return;
	}

	$list_table = new Crm_Inbound_Messages_List_Table();
	$list_table->prepare_items();

?>
<div class="wrap">
<?php screen_icon(); ?>

<h2><?php
	echo esc_html( __( 'Inbound Messages', 'crm' ) );

	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'crm' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
?></h2>

<?php do_action( 'crm_admin_updated_message' ); ?>

<?php $list_table->views(); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Messages', 'crm' ), 'crm-inbound' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}

function crm_inbound_edit_page() {
	$post = new Crm_Inbound_Message( $_REQUEST['post'] );

	if ( empty( $post ) )
		return;

	require_once CRM_PLUGIN_DIR . '/admin/includes/meta-boxes.php';

	include CRM_PLUGIN_DIR . '/admin/edit-inbound-form.php';

}

?>