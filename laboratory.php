<?php
/**
 * Plugin Name: Laboratory
 * Plugin URI: http://colorlabsproject.com/plugins/laboratory/
 * Description: Laboratory is a powerful collection of ColorLabs & Company features to enhance your website.
 * Version: 1.0.9
 * Author: ColorLabs & Company
 * Author URI: http://colorlabsproject.com/
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */
/**  Copyright 2012  ColorLabs 

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

	if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) && basename( __FILE__ ) == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
        die ( 'Please do not load this screen directly. Thanks!' );
    }

	require_once( plugin_dir_path( __FILE__ ).'classes/laboratory.class.php' );


	global $laboratory;

	$laboratory = new Laboratory( __FILE__ );
	$laboratory->version = '1.0.8';

	
	
?>