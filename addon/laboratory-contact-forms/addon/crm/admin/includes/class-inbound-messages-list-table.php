<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

class Crm_Inbound_Messages_List_Table extends WP_List_Table {

	public static function define_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'subject' => __( 'Subject', 'crm' ),
			'from' => __( 'From', 'crm' ),
			'channel' => __( 'Channel', 'crm' ),
			'date' => __( 'Date', 'crm' ) );

		return $columns;
	}

	function __construct() {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false ) );
	}

	function prepare_items() {
		$current_screen = get_current_screen();
		$per_page = $this->get_items_per_page( $current_screen->id . '_per_page' );

		$this->_column_headers = $this->get_column_info();

		$args = array(
			'posts_per_page' => $per_page,
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page,
			'orderby' => 'date',
			'order' => 'DESC' );

		if ( ! empty( $_REQUEST['s'] ) )
			$args['s'] = $_REQUEST['s'];

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'subject' == $_REQUEST['orderby'] )
				$args['meta_key'] = '_subject';
			elseif ( 'from' == $_REQUEST['orderby'] )
				$args['meta_key'] = '_from';
		}

		if ( ! empty( $_REQUEST['order'] ) && 'asc' == strtolower( $_REQUEST['order'] ) )
			$args['order'] = 'ASC';

		if ( ! empty( $_REQUEST['m'] ) )
			$args['m'] = $_REQUEST['m'];

		if ( ! empty( $_REQUEST['channel_id'] ) )
			$args['channel_id'] = $_REQUEST['channel_id'];
		
		if ( ! empty( $_REQUEST['channel'] ) )
			$args['channel'] = $_REQUEST['channel'];

		if ( ! empty( $_REQUEST['post_status'] ) && 'trash' == $_REQUEST['post_status'] )
			$args['post_status'] = 'trash';

		$this->items = Crm_Inbound_Message::find( $args );

		$total_items = Crm_Inbound_Message::$found_items;
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] == 'trash';
	}

	function get_views() {
		$status_links = array();
		$post_status = empty( $_REQUEST['post_status'] ) ? '' : $_REQUEST['post_status'];

		// Inbox
		Crm_Inbound_Message::find( array( 'post_status' => 'any' ) );
		$posts_in_inbox = Crm_Inbound_Message::$found_items;

		$inbox = sprintf(
			_nx( 'Inbox <span class="count">(%s)</span>', 'Inbox <span class="count">(%s)</span>',
				$posts_in_inbox, 'posts', 'crm' ),
			number_format_i18n( $posts_in_inbox ) );

		$status_links['inbox'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			admin_url( 'admin.php?page=crm_inbound' ),
			'trash' != $post_status ? ' class="current"' : '',
			$inbox );

		// Trash
		Crm_Inbound_Message::find( array( 'post_status' => 'trash' ) );
		$posts_in_trash = Crm_Inbound_Message::$found_items;

		if ( empty( $posts_in_trash ) )
			return $status_links;

		$trash = sprintf(
			_nx( 'Trash <span class="count">(%s)</span>', 'Trash <span class="count">(%s)</span>',
				$posts_in_trash, 'posts', 'crm' ),
			number_format_i18n( $posts_in_trash ) );

		$status_links['trash'] = sprintf( '<a href="%1$s"%2$s>%3$s</a>',
			admin_url( 'admin.php?page=crm_inbound&post_status=trash' ),
			'trash' == $post_status ? ' class="current"' : '',
			$trash );

		return $status_links;
	}

	function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	function get_sortable_columns() {
		$columns = array(
			'subject' => array( 'subject', false ),
			'from' => array( 'from', false ),
			'date' => array( 'date', true ) );

		return $columns;
	}

	function get_bulk_actions() {
		$actions = array();

		if ( $this->is_trash )
			$actions['untrash'] = __( 'Restore', 'crm' );

		if ( $this->is_trash || ! EMPTY_TRASH_DAYS )
			$actions['delete'] = __( 'Delete Permanently', 'crm' );
		else
			$actions['trash'] = __( 'Move to Trash', 'crm' );

		return $actions;
	}

	function extra_tablenav( $which ) {
		$channel = 0;

		if ( ! empty( $_REQUEST['channel_id'] ) ) {
			$term = get_term( $_REQUEST['channel_id'], Crm_Inbound_Message::channel_taxonomy );

			if ( ! empty( $term ) && ! is_wp_error( $term ) )
				$channel = $term->term_id;

		} elseif ( ! empty( $_REQUEST['channel'] ) ) {
			$term = get_term_by( 'slug', $_REQUEST['channel'],
				Crm_Inbound_Message::channel_taxonomy );

			if ( ! empty( $term ) && ! is_wp_error( $term ) )
				$channel = $term->term_id;
		}

?>
<div class="alignleft actions">
<?php
		if ( 'top' == $which ) {
			$this->months_dropdown( Crm_Inbound_Message::post_type );

			wp_dropdown_categories( array(
				'taxonomy' => Crm_Inbound_Message::channel_taxonomy,
				'name' => 'channel_id',
				'show_option_all' => __( 'View all channels', 'crm' ),
				'hide_empty' => 0,
				'hide_if_empty' => 1,
				'orderby' => 'name',
				'selected' => $channel ) );

			submit_button( __( 'Filter', 'crm' ),
				'secondary', false, false, array( 'id' => 'post-query-submit' ) );
		}

		if ( $this->is_trash && current_user_can( 'crm_delete_inbound_messages' ) ) {
			submit_button( __( 'Empty Trash', 'crm' ),
				'button-secondary apply', 'delete_all', false );
		}
?>
</div>
<?php
	}

	function column_default( $item, $column_name ) {
		return '';
    }

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id );
	}

	function column_subject( $item ) {
		if ( $this->is_trash )
			return '<strong>' . esc_html( $item->subject ) . '</strong>';

		$actions = array();

		$url = admin_url( 'admin.php?page=crm_inbound&post=' . absint( $item->id ) );
		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

		$actions['edit'] = '<a href="' . $edit_link . '">'
			. esc_html( __( 'Edit', 'crm' ) ) . '</a>';

		if ( crm_akismet_is_active() && ! empty( $item->akismet ) ) {
			if ( ! empty( $item->akismet['spam'] ) ) {
				$link = add_query_arg( array( 'action' => 'unspam' ), $url );
				$link = wp_nonce_url( $link, 'crm-unspam-inbound-message_' . $item->id );

				$actions['unspam'] = '<a href="' . $link . '">'
					. esc_html( __( 'Not Spam', 'crm' ) ) . '</a>';
			} else {
				$link = add_query_arg( array( 'action' => 'spam' ), $url );
				$link = wp_nonce_url( $link, 'crm-spam-inbound-message_' . $item->id );

				$actions['spam'] = '<a href="' . $link . '">'
					. esc_html( __( 'Spam', 'crm' ) ) . '</a>';
			}
		}

		$a = sprintf( '<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
			$edit_link,
			esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'crm' ), $item->subject ) ),
			esc_html( $item->subject ) );

		return '<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
	}

	function column_from( $item ) {
		return $item->from;
	}

	function column_channel( $item ) {
		if ( empty( $item->channel ) )
			return '';

		$term = get_term_by( 'slug', $item->channel, Crm_Inbound_Message::channel_taxonomy );

		if ( empty( $term ) || is_wp_error( $term ) )
			return $item->channel;

		$link = admin_url( 'admin.php?page=crm_inbound&channel=' . $term->slug );

		$output = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>',
			$link, esc_attr( $term->name ), esc_html( $term->name ) );

		return $output;
	}

	function column_date( $item ) {
		$post = get_post( $item->id );

		if ( ! $post )
			return '';

		$t_time = get_the_time( __( 'Y/m/d g:i:s A', 'crm' ), $item->id );
		$m_time = $post->post_date;
		$time = get_post_time( 'G', true, $item->id );

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24*60*60 )
			$h_time = sprintf( __( '%s ago', 'crm' ), human_time_diff( $time ) );
		else
			$h_time = mysql2date( __( 'Y/m/d', 'crm' ), $m_time );

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
	}
}

?>