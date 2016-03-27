<?php
/*
Plugin Name: Drafts for Friends
Plugin URI: http://automattic.com/
Description: Now you don't need to add friends as users to the blog in order to let them preview your drafts
Author: Neville Longbottom
Version: 2.2
Author URI:
*/

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
			plugins_url( 'public/css/drafts-for-friends.css', __FILE__ ),
			array(),
			$this->version,
			'all'
		);
		wp_enqueue_style( $this->plugin_name );
		wp_enqueue_script( 'jquery'  );
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'public/js/drafts-for-friends.js',
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
			array( $this, 'output_existing_menu_sub_admin_page' )
		);
	}

	function calc( $params ) {
		$exp = 60;
		$multiply = 60;

		if ( isset( $params['expires'] ) && ($e = intval( $params['expires'] )) ) {
			$exp = $e;
		}

		$mults = array(
			's' => 1,
			'm' => 60,
			'h' => 3600,
			'd' => 24*3600
		);

		if ( $params['measure'] && $mults[$params['measure']] ) {
			$multiply = $mults[$params['measure']];
		}

		return $exp * $multiply;
	}

	function process_post_options( $params ) {
		global $current_user;

		if ( $params['post_id'] ) {
			$p = get_post( $params['post_id'] );
			if ( !$p ) {
				return __( 'There is no such post!', 'draftsforfriends' );
			}
			if ( 'publish' == get_post_status( $p ) ) {
				return __( 'The post is published!', 'draftsforfriends' );
			}
			$this->user_options['shared'][] = array(
				'id' => $p->ID,
				'expires' => time() + $this->calc( $params ),
				'key' => 'baba_' . wp_generate_password( 8 )
			);
			$this->save_admin_options();
		}
	}

	function process_delete( $params ) {
		$shared = array();
		foreach ( $this->user_options['shared'] as $share ) {
			if ( $share['key'] == $params['key'] ) {
				continue;
			}
			$shared[] = $share;
		}
		$this->user_options['shared'] = $shared;
		$this->save_admin_options();
	}

	function process_extend( $params ) {
		$shared = array();
		foreach( $this->user_options['shared'] as $share ) {
			if ( $share['key'] == $params['key'] ) {
				$share['expires'] += $this->calc( $params );
			}
			$shared[] = $share;
		}
		$this->user_options['shared'] = $shared;
		$this->save_admin_options();
	}

	function get_drafts() {
		global $current_user;

		$my_drafts = get_users_drafts( $current_user->ID );

		$my_scheduled = get_posts(array(
			'post_author' => $current_user->ID,
			'post_status' => 'future',
			'orderby' => 'post_modified'
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

	function get_shared() {
		return isset( $this->user_options['shared'] ) ? $this->user_options['shared'] : array();
	}

	function format_expire_time( $expires ) {
		$now = new DateTime();
		$exp = new DateTime();
		$exp->setTimestamp($expires);
		$diff = $now->diff($exp);
		$format = array();

		if ( $diff->h !== 0 ) {
			$format[] = sprintf( _n( '%d hour', '%d hours', $diff->h, 'draftsforfriends' ), $diff->h );
		}
		if ( $diff->i !== 0 ) {
			$format[] = sprintf( _n( '%d minute', '%d minutes', $diff->i, 'draftsforfriends' ), $diff->i );
		}
		if ( ! count( $format ) ) {
			return __('Less than a minute');
		} else {
			$format[] = sprintf( __( '%d seconds', 'draftsforfriends' ), $diff->s );
		}

		if ( count( $format ) > 1 ) {
			/* translators: expiration time e.g. 3 hours and 27 minutes or 5 minutes and 10 seconds */
			return sprintf( __( '%s and %s', 'draftsforfriends' ), $format[0], $format[1] );
		}

		return $format[0];
	}

	function output_existing_menu_sub_admin_page() {
		if ( isset($_POST['draftsforfriends_submit']) ) {
			$msg = $this->process_post_options( $_POST );
		} elseif ( isset($_POST['action']) && $_POST['action'] == 'extend' ) {
			$msg = $this->process_extend( $_POST );
		} elseif ( isset($_GET['action']) && $_GET['action'] == 'delete') {
			$msg = $this->process_delete( $_GET );
		}
		$drafts = $this->get_drafts();
?>
		<div class="wrap">
			<h2><?php _e( 'Drafts for Friends', 'draftsforfriends' ); ?></h2>
		<?php if ( isset($msg) ) : ?>
			<div id="message" class="updated fade"><?php echo $msg; ?></div>
		<?php endif; ?>
			<h3><?php _e( 'Currently shared drafts', 'draftsforfriends' ); ?></h3>
			<table class="widefat">
				<thead>
					<tr>
						<th><?php _e( 'ID', 'draftsforfriends' ); ?></th>
						<th><?php _e( 'Title', 'draftsforfriends' ); ?></th>
						<th><?php _e( 'Link', 'draftsforfriends' ); ?></th>
						<th><?php _e( 'Expires In', 'draftsforfriends' ); ?></th>
						<th colspan="2" class="actions"><?php _e( 'Actions', 'draftsforfriends' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php
					$shared = $this->get_shared();
					foreach ( $shared as $share ) :
						$post = get_post( $share['id'] );
						$key = $share['key'];
						$expires = $share['expires'];
						$url = get_bloginfo( 'url' ) . '/?p=' . $post->ID . '&draftsforfriends='. $key;
				?>
					<tr>
						<td><?php echo $post->ID; ?></td>
						<td><?php echo $post->post_title; ?></td>
						<!-- TODO: make the draft link selecatble -->
						<td><a href="<?php echo $url; ?>"><?php echo esc_html( $url ); ?></a></td>
						<td><?php echo $this->format_expire_time( $expires ) ?></td>
						<td class="actions">
							<a class="draftsforfriends-extend edit" id="draftsforfriends-extend-link-<?php echo $key; ?>"
								href="javascript:draftsforfriends.toggle_extend('<?php echo $key; ?>' );">
									<?php _e( 'Extend', 'draftsforfriends' ); ?>
							</a>
							<form class="draftsforfriends-extend" id="draftsforfriends-extend-form-<?php echo $key; ?>"
								action="" method="post">
								<input type="hidden" name="action" value="extend" />
								<input type="hidden" name="key" value="<?php echo $key; ?>" />
								<input type="submit" class="button" name="draftsforfriends_extend_submit"
									value="<?php _e( 'Extend', 'draftsforfriends' ); ?>"/>
								<?php _e( 'by', 'draftsforfriends' );?>
								<?php echo $this->tmpl_measure_select(); ?>
								<a class="draftsforfriends-extend-cancel"
									href="javascript:draftsforfriends.cancel_extend('<?php echo $key; ?>');">
									<?php _e( 'Cancel', 'draftsforfriends' ); ?>
								</a>
							</form>
						</td>
						<td class="actions">
							<a class="delete" href="edit.php?page=<?php echo plugin_basename(__FILE__); ?>&action=delete&key=<?php echo $key; ?>">
								<?php _e( 'Delete', 'draftsforfriends' ); ?>
							</a>
						</td>
					</tr>
				<?php
					endforeach;

					if ( empty($s) ) :
				?>
					<tr>
						<td colspan="5"><?php _e( 'No shared drafts!', 'draftsforfriends' ); ?></td>
					</tr>
				<?php endif; ?>
				</tbody>
			</table>
			<h3><?php _e( 'Drafts for Friends', 'draftsforfriends' ); ?></h3>
			<form id="draftsforfriends-share" action="" method="post">
				<p>
					<select id="draftsforfriends-postid" name="post_id">
						<option value=""><?php _e( 'Choose a draft', 'draftsforfriends' ); ?></option>
					<?php
						foreach ( $drafts as $draft ) :
							if ( $draft[1] ) :
					?>
								<option value="" disabled="disabled"></option>
								<option value="" disabled="disabled"><?php echo $draft[0]; ?></option>
							<?php
								foreach ($draft[2] as $d):
									if ( empty( $d->post_title ) ) {
										continue;
									}
							?>
									<option value="<?php echo $d->ID?>">
										<?php echo esc_html( $d->post_title ); ?>
									</option>
							<?php
								endforeach;
							endif;
						endforeach;
					?>
					</select>
				</p>
				<p>
					<input type="submit" class="button" name="draftsforfriends_submit"
						value="<?php _e( 'Share it', 'draftsforfriends' ); ?>" />
					<?php _e( 'for', 'draftsforfriends' ); ?>
					<?php echo $this->tmpl_measure_select(); ?>
				</p>
			</form>
		</div>
<?php
	// end output_existing_menu_sub_admin_page
	}

	function can_view ( $pid ) {
		foreach ( $this->admin_options as $option ) {
			$shares = $option['shared'];

			foreach ( $shares as $share ) {
				if ( $share[ 'key'] == $_GET['draftsforfriends'] && $pid ) {
					return true;
				}
			}
		}
		return false;
	}

	function posts_results_intercept( $pp ) {
		if ( 1 != count( $pp ) ) {
			return $pp;
		}

		$p = $pp[0];
		$status = get_post_status( $p );

		if ( 'publish' != $status && $this->can_view($p->ID) ) {
			$this->shared_post = $p;
		}
		return $pp;
	}

	function the_posts_intercept( $pp ) {
		if ( empty( $pp ) && ! is_null( $this->shared_post ) ) {
			return array( $this->shared_post );
		} else {
			$this->shared_post = null;
			return $pp;
		}
	}

	function tmpl_measure_select() {
		$secs  =  __( 'seconds', 'draftsforfriends' );
		$mins  =  __( 'minutes', 'draftsforfriends' );
		$hours =  __( 'hours', 'draftsforfriends' );
		$days  =  __( 'days', 'draftsforfriends' );

		return <<<SELECT
			<input name="expires" type="text" value="2" size="4"/>
			<select name="measure">
				<option value="s">${secs}</option>
				<option value="m">${mins}</option>
				<option value="h" selected="selected">${hours}</option>
				<option value="d">${days}</option>
			</select>
SELECT;
	}
}

$DraftsForFriendsInstance = new DraftsForFriends();
