<?php
/**
 * Plugin Name: Drafts for Friends
 */
require_once plugin_dir_path( dirname (__FILE__ ) ) . 'includes/time-utils.php';


class DraftsForFriends {

	function __construct() {
		$this->plugin_name = 'drafts-for-friends';
		$this->version = '0.0.1';
		add_action( 'init', array( &$this, 'init' ) );
	}

	function init() {
		global $current_user;

		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
		add_filter( 'the_posts', array( $this, 'the_posts_intercept' ) );
		add_filter( 'posts_results', array( $this, 'posts_results_intercept' ) );

		$this->admin_options = $this->get_admin_options();

		$this->user_options =
			($current_user->ID > 0 && isset( $this->admin_options[$current_user->ID] )) ?
			$this->admin_options[$current_user->ID] : array();

		$this->save_admin_options();
	}

	function add_admin_scripts() {
		wp_register_style(
			$this->plugin_name,
			plugins_url( 'public/css/drafts-for-friends.css', dirname ( __FILE__ ) ),
			array(),
			$this->version,
			'all'
		);
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( 'jquery'  );
		wp_enqueue_script(
			$this->plugin_name,
			plugins_url( 'public/js/drafts-for-friends.js', dirname( __FILE__ ) ),
			array(),
			$this->version,
			true
		);
	}

	function get_admin_options() {
		$saved_options = get_option( 'shared'  );
		return is_array( $saved_options )? $saved_options : array();
	}

	function save_admin_options() {
		global $current_user;

		if ( $current_user->ID > 0 ) {
			$this->admin_options[$current_user->ID] = $this->user_options;
		}

		update_option( 'shared', $this->admin_options );
	}

	function add_admin_pages() {
		add_submenu_page(
			'edit.php',
			__( 'Drafts for Friends', 'draftsforfriends' ),
			__( 'Drafts for Friends', 'draftsforfriends' ),
			'edit_posts',
			'drafts-for-friends',
			array( $this, 'process_page_request' )
		);
	}

	function share_post( $post_id, $expires, $unit ) {
		global $current_user;

		if ( $post_id ) {
			$post = get_post( $post_id );

			if ( !$post ) {
				return __( 'There is no such post!', 'draftsforfriends' );
			}

			if ( 'publish' == get_post_status( $post ) ) {
				return __( 'The post is published!', 'draftsforfriends' );
			}

			$key = 'baba_' . wp_generate_password( 8, false, false );

			$this->user_options['shared'][$key] = array(
				'id'      => $post->ID,
				'expires' => time() + calculate_time( $expires, $unit ),
				'key'     => $key
			);

			$this->save_admin_options();
		}
	}

	function delete_shared_post( $key ) {
		unset( $this->user_options['shared'][$key] );
		$this->save_admin_options();
	}

	function extend_shared_post_expiration( $key, $amount, $unit ) {
		if ( isset( $this->user_options['shared'][$key] ) ) {
			$this->user_options['shared'][$key]['expires'] += calculate_time( $amount, $unit );
			$this->save_admin_options();
		}
	}

	function get_drafts() {
		global $current_user;

		$my_drafts = get_users_drafts( $current_user->ID );

		$my_scheduled = get_posts(array(
			'post_author' => $current_user->ID,
			'post_status' => 'future',
			'orderby'     => 'post_modified'
		));

		$pending = get_posts(array(
			'post_author' => $current_user->ID,
			'post_status' => 'pending'
		));

		$drafts = array(
			array(
				__( 'Your Drafts:', 'draftsforfriends' ),
				count( $my_drafts ),
				$my_drafts,
			),
			array(
				__( 'Your Scheduled Posts:', 'draftsforfriends' ),
				count( $my_scheduled ),
				$my_scheduled,
			),
			array(
				__( 'Pending Review:', 'draftsforfriends' ),
				count( $pending ),
				$pending,
			),
		);
		return $drafts;
	}

	function get_shared_posts() {
		return isset( $this->user_options['shared'] ) ? $this->user_options['shared'] : array();
	}

	function process_page_request() {
		if ( isset($_POST['draftsforfriends_submit']) ) {
			$post_id = $_POST['post_id'];
			$expires = $_POST['expires'];
			$measure = $_POST['measure'];

			$msg = $this->share_post( $post_id, $expires, $measure );

		} elseif ( isset($_POST['action']) && $_POST['action'] == 'extend' ) {
			$key     = $_POST['key'];
			$expires = $_POST['expires'];
			$measure = $_POST['measure'];

			$msg = $this->extend_shared_post_expiration( $key, $expires, $measure );

		} elseif ( isset($_GET['action']) && $_GET['action'] == 'delete') {
			$key = $_GET['key'];

			$msg = $this->delete_shared_post( $key );
		}

		$drafts = $this->get_drafts();
		$page_name = $_GET['page'];

		include_once plugin_dir_path( dirname ( __FILE__ ) ). 'views/drafts-table.php';
	}

	function can_view ( $post_id ) {
		foreach ( $this->admin_options as $option ) {
			$shares = $option['shared'];

			foreach ( $shares as $share ) {
				if ( $share[ 'key'] == $_GET['draftsforfriends'] && $post_id ) {
					return true;
				}
			}
		}
		return false;
	}

	function posts_results_intercept( $posts ) {
		if ( 1 != count( $posts ) ) {
			return $posts;
		}

		$post = $posts[0];
		$status = get_post_status( $post );

		if ( 'publish' != $status && $this->can_view( $post->ID ) ) {
			$this->shared_post = $post;
		}
		return $posts;
	}

	function the_posts_intercept( $posts ) {
		if ( empty( $posts ) && ! is_null( $this->shared_post ) ) {
			return array( $this->shared_post );
		} else {
			$this->shared_post = null;
			return $posts;
		}
	}
}
?>
