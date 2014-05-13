<?php

class Crm_Inbound_Message {

	const post_type = 'crm_inbound';
	const channel_taxonomy = 'crm_inbound_channel';

	public static $found_items = 0;

	public $id;
	public $channel;
	public $date;
	public $subject;
	public $from;
	public $from_name;
	public $from_email;
	public $fields;
	public $meta;
	public $akismet;

	public static function register_post_type() {
		register_post_type( self::post_type, array(
			'labels' => array(
				'name' => __( 'Crm Inbound Messages', 'crm' ),
				'singular_name' => __( 'Crm Inbound Message', 'crm' ) ),
			'rewrite' => false,
			'query_var' => false ) );

		register_taxonomy( self::channel_taxonomy, self::post_type, array(
			'labels' => array(
				'name' => __( 'Crm Inbound Message Channels', 'crm' ),
				'singular_name' => __( 'Crm Inbound Message Channel', 'crm' ) ),
			'public' => false,
			'rewrite' => false,
			'query_var' => false ) );
	}

	public static function find( $args = '' ) {
		$defaults = array(
			'posts_per_page' => 10,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_key' => '',
			'meta_value' => '',
			'post_status' => 'any',
			'tax_query' => array(),
			'channel' => '',
			'channel_id' => '' );

		$args = wp_parse_args( $args, $defaults );

		$args['post_type'] = self::post_type;

		if ( ! empty( $args['channel_id'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => self::channel_taxonomy,
				'terms' => $args['channel_id'],
				'field' => 'term_id' );
		}

		if ( ! empty( $args['channel'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => self::channel_taxonomy,
				'terms' => $args['channel'],
				'field' => 'slug' );
		}

		$q = new WP_Query();
		$posts = $q->query( $args );

		self::$found_items = $q->found_posts;

		$objs = array();

		foreach ( (array) $posts as $post )
			$objs[] = new self( $post );

		return $objs;
	}

	public static function add( $args = '' ) {
		$defaults = array(
			'channel' => '',
			'subject' => '',
			'from' => '',
			'from_name' => '',
			'from_email' => '',
			'fields' => array(),
			'meta' => array(),
			'akismet' => array() );

		$args = wp_parse_args( $args, $defaults );

		$obj = new self();

		$obj->channel = $args['channel'];
		$obj->subject = $args['subject'];
		$obj->from = $args['from'];
		$obj->from_name = $args['from_name'];
		$obj->from_email = $args['from_email'];
		$obj->fields = $args['fields'];
		$obj->meta = $args['meta'];
		$obj->akismet = $args['akismet'];

		$obj->save();

		return $obj;
	}

	public function __construct( $post = null ) {
		if ( ! empty( $post ) && ( $post = get_post( $post ) ) ) {
			$this->id = $post->ID;

			$this->date = get_the_time( __( 'Y/m/d g:i:s A', 'crm' ), $this->id );
			$this->subject = get_post_meta( $post->ID, '_subject', true );
			$this->from = get_post_meta( $post->ID, '_from', true );
			$this->from_name = get_post_meta( $post->ID, '_from_name', true );
			$this->from_email = get_post_meta( $post->ID, '_from_email', true );
			$this->fields = get_post_meta( $post->ID, '_fields', true );
			$this->meta = get_post_meta( $post->ID, '_meta', true );
			$this->akismet = get_post_meta( $post->ID, '_akismet', true );

			$terms = wp_get_object_terms( $this->id, self::channel_taxonomy );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) )
				$this->channel = $terms[0]->slug;
		}
	}

	public function save() {
		if ( ! empty( $this->subject ) )
			$post_title = $this->subject;
		else
			$post_title = __( '(No Title)', 'crm' );

		$fields = crm_array_flatten( $this->fields );
		$fields = array_filter( array_map( 'trim', $fields ) );

		$post_content = implode( "\n", $fields );

		$postarr = array(
			'ID' => absint( $this->id ),
			'post_type' => self::post_type,
			'post_status' => 'publish',
			'post_title' => $post_title,
			'post_content' => $post_content );

		$post_id = wp_insert_post( $postarr );

		if ( $post_id ) {
			$this->id = $post_id;
			update_post_meta( $post_id, '_subject', $this->subject );
			update_post_meta( $post_id, '_from', $this->from );
			update_post_meta( $post_id, '_from_name', $this->from_name );
			update_post_meta( $post_id, '_from_email', $this->from_email );
			update_post_meta( $post_id, '_fields', $this->fields );
			update_post_meta( $post_id, '_meta', $this->meta );
			update_post_meta( $post_id, '_akismet', $this->akismet );

			if ( term_exists( $this->channel, self::channel_taxonomy ) )
				wp_set_object_terms( $this->id, $this->channel, self::channel_taxonomy );
		}

		return $post_id;
	}

	public function trash() {
		if ( empty( $this->id ) )
			return;

		if ( ! EMPTY_TRASH_DAYS )
			return $this->delete();

		$post = wp_trash_post( $this->id );

		return (bool) $post;
	}

	public function untrash() {
		if ( empty( $this->id ) )
			return;

		$post = wp_untrash_post( $this->id );

		return (bool) $post;
	}

	public function delete() {
		if ( empty( $this->id ) )
			return;

		if ( $post = wp_delete_post( $this->id, true ) )
			$this->id = 0;

		return (bool) $post;
	}

	public function spam() {
		if ( empty( $this->id ) || empty( $this->akismet ) )
			return;

		if ( isset( $this->akismet['spam'] ) && $this->akismet['spam'] )
			return;

		if ( empty( $this->akismet['comment'] ) )
			return;

		if ( crm_akismet_submit_spam( $this->akismet['comment'] ) ) {
			$this->akismet['spam'] = true;
			update_post_meta( $this->id, '_akismet', $this->akismet );
			return true;
		}
	}

	public function unspam() {
		if ( empty( $this->id ) || empty( $this->akismet ) )
			return;

		if ( isset( $this->akismet['spam'] ) && ! $this->akismet['spam'] )
			return;

		if ( empty( $this->akismet['comment'] ) )
			return;

		if ( crm_akismet_submit_ham( $this->akismet['comment'] ) ) {
			$this->akismet['spam'] = false;
			update_post_meta( $this->id, '_akismet', $this->akismet );
			return true;
		}
	}
}

?>