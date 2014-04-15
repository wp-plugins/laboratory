<?php
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
    die ( 'Please do not load this screen directly. Thanks!' );
}

/**
 * Laboratory Frontend Class
 *
 * All functionality pertaining to the frontend sections of Laboratory.
 *
 * @package WordPress
 * @subpackage Laboratory
 * @category Frontend
 * @author ColorLabs
 * @since 1.0.0
 *
 * TABLE OF CONTENTS
 *
 * - __construct()
 */
class Laboratory_Frontend extends Laboratory_Base {
	function __construct() {
		parent::__construct();
	} // End __construct()
}
?>