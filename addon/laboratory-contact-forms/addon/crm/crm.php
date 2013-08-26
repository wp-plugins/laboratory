<?php

define( 'CRM_VERSION', '1.0' );

define( 'CRM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

define( 'CRM_PLUGIN_NAME', trim( dirname( CRM_PLUGIN_BASENAME ), '/' ) );

define( 'CRM_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

define( 'CRM_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

require_once CRM_PLUGIN_DIR . '/includes/functions.php';
require_once CRM_PLUGIN_DIR . '/includes/formatting.php';
require_once CRM_PLUGIN_DIR . '/includes/capabilities.php';
require_once CRM_PLUGIN_DIR . '/includes/class-contact.php';
require_once CRM_PLUGIN_DIR . '/includes/class-inbound-message.php';
require_once CRM_PLUGIN_DIR . '/includes/user.php';
require_once CRM_PLUGIN_DIR . '/includes/comment.php';
require_once CRM_PLUGIN_DIR . '/includes/akismet.php';

if ( is_admin() )
	require_once CRM_PLUGIN_DIR . '/admin/admin.php';

/* Init */

add_action( 'init', 'crm_init' );

function crm_init() {

	/* L10N */
	load_plugin_textdomain( 'crm', false, 'crm/languages' );

	/* Custom Post Types */
	Crm_Contact::register_post_type();
	Crm_Inbound_Message::register_post_type();

	do_action( 'crm_init' );
}

?>