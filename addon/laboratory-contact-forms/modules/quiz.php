<?php
/**
** A base module for [quiz]
**/

/* Shortcode handler */

add_action( 'init', 'colabs7_add_shortcode_quiz', 5 );

function colabs7_add_shortcode_quiz() {
	colabs7_add_shortcode( 'quiz', 'colabs7_quiz_shortcode_handler', true );
}

function colabs7_quiz_shortcode_handler( $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	if ( empty( $tag->name ) )
		return '';

	$validation_error = colabs7_get_validation_error( $tag->name );

	$class = colabs7_form_controls_class( $tag->type );

	if ( $validation_error )
		$class .= ' colabs7-not-valid';

	$atts = array();

	$atts['size'] = $tag->get_size_option( '40' );
	$atts['maxlength'] = $tag->get_maxlength_option();
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
	$atts['aria-required'] = 'true';

	$pipes = $tag->pipes;

	if ( is_a( $pipes, 'COLABS7_Pipes' ) && ! $pipes->zero() ) {
		$pipe = $pipes->random_pipe();
		$question = $pipe->before;
		$answer = $pipe->after;
	} else {
		// default quiz
		$question = '1+1=?';
		$answer = '2';
	}

	$answer = colabs7_canonicalize( $answer );

	$atts['type'] = 'text';
	$atts['name'] = $tag->name;

	$atts = colabs7_format_atts( $atts );

	$html = sprintf(
		'<span class="colabs7-form-control-wrap %1$s"><span class="colabs7-quiz-label">%2$s</span>&nbsp;<input %3$s /><input type="hidden" name="_colabs7_quiz_answer_%1$s" value="%4$s" />%5$s</span>',
		$tag->name, esc_html( $question ), $atts,
		wp_hash( $answer, 'colabs7_quiz' ), $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'colabs7_validate_quiz', 'colabs7_quiz_validation_filter', 10, 2 );

function colabs7_quiz_validation_filter( $result, $tag ) {
	$tag = new COLABS7_Shortcode( $tag );

	$name = $tag->name;

	$answer = isset( $_POST[$name] ) ? colabs7_canonicalize( $_POST[$name] ) : '';
	$answer_hash = wp_hash( $answer, 'colabs7_quiz' );

	$expected_hash = isset( $_POST['_colabs7_quiz_answer_' . $name] )
		? (string) $_POST['_colabs7_quiz_answer_' . $name]
		: '';

	if ( $answer_hash != $expected_hash ) {
		$result['valid'] = false;
		$result['reason'][$name] = colabs7_get_message( 'quiz_answer_not_correct' );
	}

	return $result;
}


/* Ajax echo filter */

add_filter( 'colabs7_ajax_onload', 'colabs7_quiz_ajax_refill' );
add_filter( 'colabs7_ajax_json_echo', 'colabs7_quiz_ajax_refill' );

function colabs7_quiz_ajax_refill( $items ) {
	if ( ! is_array( $items ) )
		return $items;

	$fes = colabs7_scan_shortcode( array( 'type' => 'quiz' ) );

	if ( empty( $fes ) )
		return $items;

	$refill = array();

	foreach ( $fes as $fe ) {
		$name = $fe['name'];
		$pipes = $fe['pipes'];

		if ( empty( $name ) )
			continue;

		if ( is_a( $pipes, 'COLABS7_Pipes' ) && ! $pipes->zero() ) {
			$pipe = $pipes->random_pipe();
			$question = $pipe->before;
			$answer = $pipe->after;
		} else {
			// default quiz
			$question = '1+1=?';
			$answer = '2';
		}

		$answer = colabs7_canonicalize( $answer );

		$refill[$name] = array( $question, wp_hash( $answer, 'colabs7_quiz' ) );
	}

	if ( ! empty( $refill ) )
		$items['quiz'] = $refill;

	return $items;
}


/* Messages */

add_filter( 'colabs7_messages', 'colabs7_quiz_messages' );

function colabs7_quiz_messages( $messages ) {
	return array_merge( $messages, array( 'quiz_answer_not_correct' => array(
		'description' => __( "Sender doesn't enter the correct answer to the quiz", 'colabs7' ),
		'default' => __( 'Your answer is not correct.', 'colabs7' )
	) ) );
}


/* Tag generator */

add_action( 'admin_init', 'colabs7_add_tag_generator_quiz', 40 );

function colabs7_add_tag_generator_quiz() {
	if ( ! function_exists( 'colabs7_add_tag_generator' ) )
		return;

	colabs7_add_tag_generator( 'quiz', __( 'Quiz', 'colabs7' ),
		'colabs7-tg-pane-quiz', 'colabs7_tg_pane_quiz' );
}

function colabs7_tg_pane_quiz( &$contact_form ) {
?>
<div id="colabs7-tg-pane-quiz" class="hidden">
<form action="">
<table>
<tr><td><?php echo esc_html( __( 'Name', 'colabs7' ) ); ?><br /><input type="text" name="name" class="tg-name oneline" /></td><td></td></tr>
</table>

<table>
<tr>
<td><code>id</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="id" class="idvalue oneline option" /></td>

<td><code>class</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="text" name="class" class="classvalue oneline option" /></td>
</tr>

<tr>
<td><code>size</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="size" class="numeric oneline option" min="1" /></td>

<td><code>maxlength</code> (<?php echo esc_html( __( 'optional', 'colabs7' ) ); ?>)<br />
<input type="number" name="maxlength" class="numeric oneline option" min="1" /></td>
</tr>

<tr>
<td><?php echo esc_html( __( 'Quizzes', 'colabs7' ) ); ?><br />
<textarea name="values"></textarea><br />
<span style="font-size: smaller"><?php echo esc_html( __( "* quiz|answer (e.g. 1+1=?|2)", 'colabs7' ) ); ?></span>
</td>
</tr>
</table>

<div class="tg-tag"><?php echo esc_html( __( "Copy this code and paste it into the form left.", 'colabs7' ) ); ?><br /><input type="text" name="quiz" class="tag" readonly="readonly" onfocus="this.select()" /></div>
</form>
</div>
<?php
}

?>