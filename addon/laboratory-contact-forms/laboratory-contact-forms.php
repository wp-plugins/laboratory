<?php
/**
 * Addon Name: Contact Forms
 * Addon Description: The popular for custom contact forms.
 * Addon Version: 1.0.0
 *
 * @package Laboratory
 * @subpackage Addon
 * @author colabs
 * @since 1.0.1
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

if ( ! defined( '_COLABS_PLUGIN_DIR' ) )
	define( '_COLABS_PLUGIN_DIR', untrailingslashit( dirname( __FILE__ ) ) );
	
require_once (_COLABS_PLUGIN_DIR . '/colabs-contact-form.php');
require_once (_COLABS_PLUGIN_DIR . '/addon/crm/crm.php');
require_once (_COLABS_PLUGIN_DIR . '/addon/colabs-simple-captcha/colabs-simple-captcha.php');
?>