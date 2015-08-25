<?php

function colabs7_current_action() {
	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
		return $_REQUEST['action'];

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
		return $_REQUEST['action2'];

	return false;
}

function colabs7_admin_has_edit_cap() {
	return current_user_can( 'colabs7_edit_contact_forms' );
}

function colabs7_add_tag_generator( $name, $title, $elm_id, $callback, $options = array() ) {
	global $colabs7_tag_generators;

	$name = trim( $name );
	if ( '' == $name )
		return false;

	if ( ! is_array( $colabs7_tag_generators ) )
		$colabs7_tag_generators = array();

	$colabs7_tag_generators[$name] = array(
		'title' => $title,
		'content' => $elm_id,
		'options' => $options );

	if ( is_callable( $callback ) )
		add_action( 'colabs7_admin_footer', $callback );

	return true;
}

function colabs7_tag_generators() {
	global $colabs7_tag_generators;

	$taggenerators = array();

	foreach ( (array) $colabs7_tag_generators as $name => $tg ) {
		$taggenerators[$name] = array_merge(
			(array) $tg['options'],
			array( 'title' => $tg['title'], 'content' => $tg['content'] ) );
	}

	return $taggenerators;
}

?>