<?php
/**
 * Addon Name: Contact Forms
 * Addon Description: The popular for custom contact forms.
 * Addon Version: 1.0.1
 *
 * @package Laboratory
 * @subpackage Addon
 * @author colabs
 * @since 1.0.1
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

if ( ! defined( 'COLABS_CONTACT_DIR' ) )
	define( 'COLABS_CONTACT_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	
if ( ! defined( 'COLABS_CONTACT_URL' ) )
	define( 'COLABS_CONTACT_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );
	
if ( ! defined( 'COLABS_CONTACT_AUTOP' ) )
	define( 'COLABS_CONTACT_AUTOP', true );

if ( ! defined( 'COLABS_CONTACT_USE_PIPE' ) )
	define( 'COLABS_CONTACT_USE_PIPE', true );

if ( ! defined( 'COLABS_CONTACT_ADMIN_READ_CAPABILITY' ) )
	define( 'COLABS_CONTACT_ADMIN_READ_CAPABILITY', 'edit_posts' );

if ( ! defined( 'COLABS_CONTACT_ADMIN_READ_WRITE_CAPABILITY' ) )
	define( 'COLABS_CONTACT_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

if ( ! defined( 'COLABS_CONTACT_VERIFY_NONCE' ) )
	define( 'COLABS_CONTACT_VERIFY_NONCE', true );

if ( ! defined( 'COLABS_CONTACT_LOAD_JS' ) )
	define( 'COLABS_CONTACT_LOAD_JS', true );
	
$includes_path = COLABS_CONTACT_DIR . '/contact-form/';
require_once ($includes_path . 'classes.php');
require_once ($includes_path . 'functions.php');
require_once ($includes_path . 'shortcodes.php');

function colabs_contact_plugin_path( $path = '' ) {
	return path_join( COLABS_CONTACT_DIR, trim( $path, '/' ) );
}

add_action( 'admin_menu', 'colabs_contact_form_admin_menu');

function colabs_contact_form_admin_menu() {
	$contact_form_admin = add_submenu_page( 'laboratory', __( 'Contact Forms', 'colabsthemes' ), __( 'Contact Forms', 'colabsthemes' ), 'manage_options', 'colabs_contact_form', 'colabs_contact_admin_management_page' );
	add_action( 'load-' . $contact_form_admin, 'colabs_contact_load_contact_form_admin' );
}

add_action( 'admin_enqueue_scripts', 'colabs_contact_admin_enqueue_scripts' );

function colabs_contact_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'colabs_contact_form' ) )
		return;

	wp_enqueue_style( 'contact-form-admin', COLABS_CONTACT_URL.'/contact-form/css/admin-style.css',array( 'thickbox' ), '1.0', 'all' );
	wp_enqueue_script( 'contact-admin-taggenerator',COLABS_CONTACT_URL.'/contact-form/js/admin-taggenerator.js',array( 'jquery' ), '1.0', true );
	wp_enqueue_script( 'contact-admin',COLABS_CONTACT_URL.'/contact-form/js/admin-scripts.js',array( 'jquery', 'thickbox', 'postbox','contact-admin-taggenerator' ),'1.0', true );
	
	$current_screen = get_current_screen();
	wp_localize_script( 'contact-admin', '_colabs_contact', array(
		'screenId' => $current_screen->id,
		'generateTag' => __( 'Generate Tag', 'colabsthemes' ),
		'ContactUrl' => COLABS_CONTACT_URL.'/contact-form/',
		'tagGenerators' => colabs_contact_tag_generators()
	) );
}

function colabs_contact_admin_management_page() {
	global $colabs_contact_form;

	if ( $colabs_contact_form ) {
		$post =& $colabs_contact_form;
		$post_id = $post->initial ? -1 : $post->id;

		require_once COLABS_CONTACT_DIR . '/contact-form/meta-boxes.php';
		require_once COLABS_CONTACT_DIR . '/contact-form/edit-contact-form.php';
		return;
	}

	$list_table = new Colabs_Contact_Form_List_Table();
	$list_table->prepare_items();
	$items = $list_table->items;

?>
<div class="wrap">
<?php screen_icon(); ?>

<h2>
	<?php echo esc_html( __( 'Contact Form', 'colabsthemes' ) );?>

	<a href="<?php echo esc_url( add_query_arg( array( 'post' => 'new' ), menu_page_url( 'colabs_contact_form', false ) ) ); ?>" class="add-new-h2"><?php echo esc_html( __( 'Add New', 'colabsthemes' ) );?></a>
	<?php
	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'colabsthemes' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
	?>
</h2>

<?php do_action( 'colabs_admin_notices' ); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box( __( 'Search Contact Forms', 'colabsthemes' ), 'colabs-contact' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
<?php
}

function colabs_contact_load_contact_form_admin() {
	global $colabs_contact_form;

	$action = colabs_contact_current_action();
	
	if ( 'save' == $action ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'colabs_contact-save-contact-form_' . $id );

		if ( ! current_user_can( 'colabs_contact_edit_contact_form', $id ) )
			wp_die( __( 'You are not allowed to edit this item.', 'colabsthemes' ) );
		
		if ( ! $colabs_contact_form = colabs_contact_form( $id ) ) {
			$colabs_contact_form = new Colabs_ContactForm();
			$colabs_contact_form->initial = true;
		}

		$colabs_contact_form->title = trim( $_POST['colabs_contact-title'] );

		$form = trim( $_POST['colabs_contact-form'] );

		$mail = array(
			'subject' => trim( $_POST['colabs_contact-mail-subject'] ),
			'sender' => trim( $_POST['colabs_contact-mail-sender'] ),
			'body' => trim( $_POST['colabs_contact-mail-body'] ),
			'recipient' => trim( $_POST['colabs_contact-mail-recipient'] ),
			'additional_headers' => trim( $_POST['colabs_contact-mail-additional-headers'] ),
			'attachments' => trim( $_POST['colabs_contact-mail-attachments'] ),
			'use_html' =>
				isset( $_POST['colabs_contact-mail-use-html'] ) && 1 == $_POST['colabs_contact-mail-use-html']
		);

		$messages = isset( $colabs_contact_form->messages ) ? $colabs_contact_form->messages : array();
		
		foreach ( colabs_contact_messages() as $key => $arr ) {
			$field_name = 'colabs_contact-message-' . strtr( $key, '_', '-' );
			if ( isset( $_POST[$field_name] ) )
				$messages[$key] = trim( $_POST[$field_name] );
		}

		$props = apply_filters( 'colabs_contact_contact_form_admin_posted_properties',
			compact( 'form', 'mail', 'messages' ) );

		foreach ( (array) $props as $key => $prop )
			$colabs_contact_form->{$key} = $prop;

		$query = array();
		$query['message'] = ( $colabs_contact_form->initial ) ? 'created' : 'saved';
		
		$ddd = $colabs_contact_form->save();
		
		$query['post'] = $colabs_contact_form->id;

		$redirect_to = add_query_arg( $query, menu_page_url( 'colabs_contact_form', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'copy' == $action ) {
		$id = empty( $_POST['post_ID'] )
			? absint( $_REQUEST['post'] )
			: absint( $_POST['post_ID'] );

		check_admin_referer( 'colabs_contact-copy-contact-form_' . $id );

		if ( ! current_user_can( 'colabs_contact_edit_contact_form', $id ) )
			wp_die( __( 'You are not allowed to edit this item.', 'colabsthemes' ) );

		$query = array();

		if ( $colabs_contact_form = colabs_contact_form( $id ) ) {
			$new_contact_form = $colabs_contact_form->copy();
			$new_contact_form->save();

			$query['post'] = $new_contact_form->id;
			$query['message'] = 'created';
		} else {
			$query['post'] = $colabs_contact_form->id;
		}

		$redirect_to = add_query_arg( $query, menu_page_url( 'colabs_contact_form', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'delete' == $action ) {
		if ( ! empty( $_POST['post_ID'] ) )
			check_admin_referer( 'colabs_contact-delete-contact-form_' . $_POST['post_ID'] );
		elseif ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'colabs_contact-delete-contact-form_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$posts = empty( $_POST['post_ID'] )
			? (array) $_REQUEST['post']
			: (array) $_POST['post_ID'];

		$deleted = 0;

		foreach ( $posts as $post ) {
			$post = new Colabs_ContactForm( $post );

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'colabs_contact_delete_contact_form', $post->id ) )
				wp_die( __( 'You are not allowed to delete this item.', 'colabsthemes' ) );

			if ( ! $post->delete() )
				wp_die( __( 'Error in deleting.', 'colabsthemes' ) );

			$deleted += 1;
		}

		$query = array();

		if ( ! empty( $deleted ) )
			$query['message'] = 'deleted';

		$redirect_to = add_query_arg( $query, menu_page_url( 'colabs_contact_form', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	$_GET['post'] = isset( $_GET['post'] ) ? $_GET['post'] : '';

	$post = null;

	if ( 'new' == $_GET['post'] && current_user_can( 'colabs_contact_edit_contact_forms' ) ){
		$post = colabs_contact_get_contact_form_default_pack(
			array( 'locale' => ( isset( $_GET['locale'] ) ? $_GET['locale'] : '' ) ) );			
	}elseif ( ! empty( $_GET['post'] ) ){
		$post = colabs_contact_form( $_GET['post'] );
	}
	if ( $post && current_user_can( 'colabs_contact_edit_contact_form', $post->id ) ) {
		colabs_contact_add_meta_boxes( $post->id );

	} else {
		$current_screen = get_current_screen();

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'Colabs_Contact_Form_List_Table', 'define_columns' ) );

		add_screen_option( 'per_page', array(
			'label' => __( 'Contact Forms', 'colabsthemes' ),
			'default' => 20,
			'option' => 'colabs_contact_forms_per_page' ) );
	}

	$colabs_contact_form = $post;
}

add_filter( 'map_meta_cap', 'colabs_contact_map_meta_cap', 10, 4 );

function colabs_contact_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'colabs_contact_edit_contact_form' => COLABS_CONTACT_ADMIN_READ_WRITE_CAPABILITY,
		'colabs_contact_edit_contact_forms' => COLABS_CONTACT_ADMIN_READ_WRITE_CAPABILITY,
		'colabs_contact_read_contact_forms' => COLABS_CONTACT_ADMIN_READ_CAPABILITY,
		'colabs_contact_delete_contact_form' => COLABS_CONTACT_ADMIN_READ_WRITE_CAPABILITY );

	$meta_caps = apply_filters( 'colabs_contact_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) )
		$caps[] = $meta_caps[$cap];

	return $caps;
}

function colabs_contact_add_meta_boxes( $post_id ) {
	add_meta_box( 'formdiv', __( 'Form', 'colabsthemes' ),
		'colabs_contact_form_meta_box', null, 'form', 'core' );

	add_meta_box( 'maildiv', __( 'Mail', 'colabsthemes' ),
		'colabs_contact_mail_meta_box', null, 'mail', 'core' );

	add_meta_box( 'messagesdiv', __( 'Messages', 'colabsthemes' ),
		'colabs_contact_messages_meta_box', null, 'messages', 'core' );

	do_action( 'colabs_contact_add_meta_boxes', $post_id );
}

if ( COLABS_CONTACT_LOAD_JS )
	add_action( 'wp_enqueue_scripts', 'colabs_contact_enqueue_scripts' );

function colabs_contact_enqueue_scripts() {
	// jquery.form.js originally bundled with WordPress is out of date and deprecated
	// so we need to deregister it and re-register the latest one
	wp_deregister_script( 'jquery-form' );
	wp_register_script( 'jquery-form',COLABS_CONTACT_URL.'/contact-form/js/jquery.form.min.js',array( 'jquery' ), '3.32.0-2013.04.03', true );

	$in_footer = true;
	if ( 'header' === COLABS_CONTACT_LOAD_JS )
		$in_footer = false;

	wp_enqueue_script( 'colabs-contact-form', COLABS_CONTACT_URL.'/contact-form/js/colabs-contact-scripts.js',array( 'jquery', 'jquery-form' ), '1.0', $in_footer );

	$_colabs_contact = array(
		'loaderUrl' => colabs_contact_ajax_loader(),
		'sending' => __( 'Sending ...', 'colabsthemes' ) );

	if ( defined( 'WP_CACHE' ) && WP_CACHE )
		$_colabs_contact['cached'] = 1;

	wp_localize_script( 'colabs-contact-form', '_colabs_contact', $_colabs_contact );

	do_action( 'colabs_contact_enqueue_scripts' );
}

if(!class_exists('ShortcodesEditorSelector')):
 
class ShortcodesEditorSelector{
	var $buttonName = 'ContactShortcodeSelector';
	function addSelector(){
		// Don't bother doing this stuff if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') )
			return;
 
	   // Add only in Rich Editor mode
	    if ( get_user_option('rich_editing') == 'true') {
	      add_filter('mce_external_plugins', array($this, 'registerTmcePlugin'));
	      //you can use the filters mce_buttons_2, mce_buttons_3 and mce_buttons_4 
	      //to add your button to other toolbars of your tinymce
	      add_filter('mce_buttons', array($this, 'registerButton'));
	    }
	}
 
	function registerButton($buttons){
		array_push($buttons, "separator", $this->buttonName);
		return $buttons;
	}
 
	function registerTmcePlugin($plugin_array){
		$plugin_array[$this->buttonName] = COLABS_CONTACT_URL.'/contact-form/editor_plugin.js.php';
		return $plugin_array;
	}
}
 
endif;
 
if(!isset($shortcodesES)){
	$shortcodesES = new ShortcodesEditorSelector();
	add_action('admin_head', array($shortcodesES, 'addSelector'));
}

?>