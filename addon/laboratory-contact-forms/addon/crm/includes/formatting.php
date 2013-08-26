<?php

function crm_htmlize( $val ) {
	if ( is_array( $val ) ) {
		$result = '';

		foreach ( $val as $v )
			$result .= '<li>' . crm_htmlize( $v ) . '</li>';

		return '<ul>' . $result . '</ul>';
	}

	return wpautop( esc_html( (string) $val ) );
}

function crm_csv_row( $inputs = array() ) {
	$row = array();

	foreach ( $inputs as $input ) {
		$input = preg_replace( '/(?<!\r)\n/', "\r\n", $input );
		$input = esc_sql( $input );
		$input = sprintf( '"%s"', $input );
		$row[] = $input;
	}

	return implode( ',', $row );
}

?>