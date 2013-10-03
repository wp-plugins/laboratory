<?php
/**
 * Addon Name: Megamenu
 * Addon Description: Make your own list menu with this Mega Menu. Also you can add the favourite menu with the image and a little description within.
 * Addon Version: 1.0.0
 *
 * @package Laboratory
 * @subpackage Addon
 * @author ColorLabs
 * @since 1.0.0
 */

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

 /* Instantiate Class */
  if ( class_exists( 'Laboratory' ) ) {
	require_once( 'classes/laboratory-megamenu.class.php' );

	global $laboratory_megamenu;
	$laboratory_megamenu = new Laboratory_Megamenu( __FILE__ );

 }
// End IF Statement
 
 /*-----------------------------------------------------------------------------------*/
/* Megamenu change backend walker*/
/*-----------------------------------------------------------------------------------*/
if(!function_exists('laboratory_ajax_switch_menu_walker'))
{
	function laboratory_ajax_switch_menu_walker()
	{	
		if ( ! current_user_can( 'edit_theme_options' ) )
		die('-1');

		check_ajax_referer( 'add-menu_item', 'menu-settings-column-nonce' );
	
		require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
	
		$item_ids = wp_save_nav_menu_items( 0, $_POST['menu-item'] );
		if ( is_wp_error( $item_ids ) )
			die('-1');
	
		foreach ( (array) $item_ids as $menu_item_id ) {
			$menu_obj = get_post( $menu_item_id );
			if ( ! empty( $menu_obj->ID ) ) {
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title; // don't show "(pending)" in ajax-added items
				$menu_items[] = $menu_obj;
			}
		}
	
		if ( ! empty( $menu_items ) ) {
			$args = array(
				'after' => '',
				'before' => '',
				'link_after' => '',
				'link_before' => '',
				'walker' => new Laboratory_Backend_Walker,
			);
			echo walk_nav_menu_tree( $menu_items, 0, (object) $args );
		}
		
		die('end');
	}
	
	//hook into wordpress admin.php
	add_action('wp_ajax_laboratory_ajax_switch_menu_walker', 'laboratory_ajax_switch_menu_walker');
}

//}
?>