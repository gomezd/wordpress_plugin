<?php
/**
 * Plugin Name: Drafts for Friends
 */
require_once plugin_dir_path( dirname (__FILE__ ) ) . 'includes/time-utils.php';


/**
 * The core plugin class.
 *
 * Adds admin and public hooks
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    drafts-for-friends
 * @subpackage drafts-for-friends/includes
 */
class DraftsForFriends {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * The current admin options.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      array    $admin_options    The current admin options.
	 */
	protected $admin_options;

	/**
	 * Set plugin name and version and init plugin
	 *
	 * @since    0.0.1
	 */
	function __construct() {
		$this->plugin_name = 'drafts-for-friends';
		$this->version = '0.0.1';
		add_action( 'init', array( &$this, 'init' ) );
	}

	/**
	 * Adds hooks, filters and enqueues scripts and styles.
	 *
	 * @since    0.0.1
	 */
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

	/**
	 * Enqueues scripts and styles and sets up localization for scripts.
	 *
	 * @since    0.0.1
	 */
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
			$this->version
		);
		wp_localize_script(
			$this->plugin_name,
			'draftsforfriends_l10n',
			array(
				'hours'   => _n_noop( '{num} hour', '{num} hours', 'draftsforfriends' ),
				'minutes' => _n_noop( '{num} minute', '{num} minutes', 'draftsforfriends' ),
				'seconds' => _n_noop( '{num} second', '{num} seconds', 'draftsforfriends' ),
				/* translators: expiration time e.g. 3 days and 5 hours, 2 minuts and 5 seconds */
				'time'    => __( '{first} and {second}', 'draftsforfriends' )
			)
		);
	}

	/**
	 * Retrieve the save admin options
	 *
	 * @since    0.0.1
	 */
	function get_admin_options() {
		$saved_options = get_option( 'shared'  );
		return is_array( $saved_options ) ? $saved_options : array();
	}

	/**
	 * Save current admin options
	 *
	 * @since    0.0.1
	 */
	function save_admin_options() {
		global $current_user;

		if ( $current_user->ID > 0 ) {
			$this->admin_options[$current_user->ID] = $this->user_options;
		}

		update_option( 'shared', $this->admin_options );
	}

	/**
	 * Adds admin submenu
	 *
	 * @since    0.0.1
	 */
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

	/**
	 * Shares a post for the specified amount of time.
	 *
	 * @since    0.0.1
	 *
	 * @param int $post_id    The ID of the post to share
	 * @param int $expires    Amount of time the post will be shared
	 * @param string $unit    Unit of time
	 *                        One of ['s', 'm', 'h', 'd'] (seconds, minutes, hours, days)
	 */
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

			// shares ae indexed by the share key for easier retrieval
			$this->user_options['shared'][$key] = array(
				'id'      => $post->ID,
				'expires' => time() + calculate_time( $expires, $unit ),
				'key'     => $key
			);

			$this->save_admin_options();

			return __( 'Your post was succesfully shared.', 'draftsforfriends' );
		}
	}

	/**
	 * Deletes a shared post
	 *
	 * @since    0.0.1
	 *
	 * @param string key    The post url "draftsforfriends" shared key
	 */
	function delete_shared_post( $key ) {
		unset( $this->user_options['shared'][$key] );
		$this->save_admin_options();
		return __( 'Your shared post was succesfully deleted.', 'draftsforfriends' );
	}

	/**
	 * Extend the shared post expiration time for the specified amount of time.
	 *
	 * @since    0.0.1
	 *
	 * @param int $post_id    The ID of the post to share
	 * @param int $expires    Amount of time the post will be shared
	 * @param string $unit    Unit of time
	 *                        One of ['s', 'm', 'h', 'd'] (seconds, minutes, hours, days)
	 */
	function extend_shared_post_expiration( $key, $amount, $unit ) {
		if ( isset( $this->user_options['shared'][$key] ) ) {
			$expiration_time = &$this->user_options['shared'][$key]['expires'];
			$extra_time = calculate_time( $amount, $unit );

			if ( $expiration_time < time() ) {
				// if it is expired, add time from now.
				$expiration_time = time() + $extra_time;
			} else {
				// add more time to the existing expiring time
				$expiration_time += $extra_time;
			}

			$this->save_admin_options();

			return __( 'Your post expiration time was succesfully extended.', 'draftsforfriends' );
		}
	}

	/**
	 * Gets all posts that user is able to share
	 *
	 * User can share his/her current drafts, schedule posts and pending review posts.
	 * Posts cannot be shared again while they are still active, meaning while share
	 * expiration time is still valid.
	 *
	 * @since    0.0.1
	 *
	 * @return array    The drafts to share
	 * <code>
	 *    array(
	 *        'user' => user's drafs,
	 *        'scheduled' => user's scheduled posts,
	 *        'pending' => user's pending review posts
	 *    )
	 * </code>
	 */
	function get_drafts() {
		global $current_user;

		// get currently shared to exclude them
		$shared_ids = array_map(
			function ( $share ) { return  $share['id']; },
			array_values( $this->user_options['shared'] )
		);

		$my_drafts = array_filter(
			get_users_drafts( $current_user->ID ),
			function ( $draft ) use (&$shared_ids) {
				return ! in_array( $draft->ID, $shared_ids );
			}
		);

		$my_scheduled = get_posts(array(
			'post_author' => $current_user->ID,
			'post_status' => 'future',
			'orderby'     => 'post_modified',
			'exclude'     => $shared_ids
		));

		$pending = get_posts(array(
			'post_author' => $current_user->ID,
			'post_status' => 'pending',
			'exclude'     => $shared_ids
		));

		$drafts = array(
			'user' => array(
				'label' => __( 'Your Drafts:', 'draftsforfriends' ),
				'posts' => $my_drafts,
			),
			'scheduled' => array(
				'label' => __( 'Your Scheduled Posts:', 'draftsforfriends' ),
				'posts' => $my_scheduled,
			),
			'pending' => array(
				'label' => __( 'Pending Review:', 'draftsforfriends' ),
				'posts' => $pending,
			),
		);
		return $drafts;
	}

	/**
	 * Retrieves the current shared posts
	 *
	 * @since    0.0.1
	 *
	 * @return array shared posts    Current shared posts, indexed by key
	 * <code>
	 *    array(
	 *        ["key"] => array(
	 *            'id' => post ID,
	 *            'expires' => expiration timestamp
	 *            'key' => share key
	 *        )
	 *    )
	 * </code>
	 */
	function get_shared_posts() {
		return isset( $this->user_options['shared'] ) ? $this->user_options['shared'] : array();
	}

	/**
	 * Process the request and dispatches the corresponding action
	 * and renders the main table view.
	 *
	 * @since    0.0.1
	 */
	function process_page_request() {
		if ( isset($_POST['draftsforfriends_submit']) ) {
			$post_id = intval( $_POST['post_id'] );
			$expires = intval ( $_POST['expires'] );
			$measure = sanitize_key( $_POST['measure'] );

			$msg = $this->share_post( $post_id, $expires, $measure );

		} elseif ( isset($_POST['action']) && 'extend' == $_POST['action'] ) {
			$key     = sanitize_text_field( $_POST['key'] );
			$expires = intval( $_POST['expires'] );
			$measure = sanitize_key( $_POST['measure'] );

			$msg = $this->extend_shared_post_expiration( $key, $expires, $measure );

		} elseif ( isset($_GET['action']) &&'delete' == $_GET['action']) {
			$key = sanitize_text_field( $_GET['key'] );

			$msg = $this->delete_shared_post( $key );
		}

		$drafts = $this->get_drafts();
		$page_name = sanitize_key( $_GET['page'] );

		include_once plugin_dir_path( dirname ( __FILE__ ) ). 'views/drafts-table.php';
	}

	/**
	 * Checks if the specified post is shared and active.
	 *
	 * @since    0.0.1
	 *
	 * @param int $post_id    The post id
	 * @return true if the post is shared and active, false otherwise
	 */
	function can_view ( $post_id ) {
		if ( isset( $_GET['draftsforfriends'] ) ) {
			$key = sanitize_text_field( $_GET['draftsforfriends'] );

			foreach ( $this->admin_options as $option ) {
				$share = $option['shared'][$key];

				if ( isset( $share ) && $post_id == $share['id'] &&
					$share['expires'] > time() ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * post_results hook
	 *
	 * Checks if the post has not been published and it is shared and active.
	 *
	 * @since    0.0.1
	 *
	 * @param array $posts    The posts results array
	 * @return array The posts results array
	 */
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

	/**
	 * the_posts hook
	 *
	 * @since    0.0.1
	 *
	 * @param array $posts    The posts results array
	 * @return array The posts results array
	 */
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
