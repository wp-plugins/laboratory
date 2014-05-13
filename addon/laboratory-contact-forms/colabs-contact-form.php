<?php

define( 'COLABS7_VERSION', '10' );

define( 'COLABS7_REQUIRED_WP_VERSION', '3.5' );

if ( ! defined( 'COLABS7_PLUGIN_BASENAME' ) )
	define( 'COLABS7_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'COLABS7_PLUGIN_NAME' ) )
	define( 'COLABS7_PLUGIN_NAME', trim( dirname( COLABS7_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'COLABS7_PLUGIN_DIR' ) )
	define( 'COLABS7_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );

if ( ! defined( 'COLABS7_PLUGIN_URL' ) )
	define( 'COLABS7_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

if ( ! defined( 'COLABS7_PLUGIN_MODULES_DIR' ) )
	define( 'COLABS7_PLUGIN_MODULES_DIR', COLABS7_PLUGIN_DIR . '/modules' );

if ( ! defined( 'COLABS7_LOAD_JS' ) )
	define( 'COLABS7_LOAD_JS', true );

if ( ! defined( 'COLABS7_LOAD_CSS' ) )
	define( 'COLABS7_LOAD_CSS', true );

if ( ! defined( 'COLABS7_AUTOP' ) )
	define( 'COLABS7_AUTOP', true );

if ( ! defined( 'COLABS7_USE_PIPE' ) )
	define( 'COLABS7_USE_PIPE', true );

/* If you or your client hate to see about donation, set this value false. */
if ( ! defined( 'COLABS7_SHOW_DONATION_LINK' ) )
	define( 'COLABS7_SHOW_DONATION_LINK', true );

if ( ! defined( 'COLABS7_ADMIN_READ_CAPABILITY' ) )
	define( 'COLABS7_ADMIN_READ_CAPABILITY', 'edit_posts' );

if ( ! defined( 'COLABS7_ADMIN_READ_WRITE_CAPABILITY' ) )
	define( 'COLABS7_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

if ( ! defined( 'COLABS7_VERIFY_NONCE' ) )
	define( 'COLABS7_VERIFY_NONCE', true );

require_once COLABS7_PLUGIN_DIR . '/settings.php';

?>