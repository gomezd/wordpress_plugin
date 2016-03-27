<?php
/**
 * The main drafts table template
 */
?>
<div class="wrap draftsforfriends">
	<h2><?php _e( 'Drafts for Friends', 'draftsforfriends' ); ?></h2>
<?php if ( isset($msg) ) : ?>
	<div id="message" class="updated fade"><?php echo esc_html( $msg ); ?></div>
<?php endif; ?>
	<h3><?php _e( 'Currently shared drafts', 'draftsforfriends' ); ?></h3>
	<table class="widefat shared">
		<thead>
			<tr>
				<th><?php _e( 'ID', 'draftsforfriends' ); ?></th>
				<th><?php _e( 'Title', 'draftsforfriends' ); ?></th>
				<th><?php _e( 'Link', 'draftsforfriends' ); ?></th>
				<th><?php _e( 'Status', 'draftsforfriends' ); ?></th>
				<th><?php _e( 'Expires In', 'draftsforfriends' ); ?></th>
				<th colspan="2" class="actions"><?php _e( 'Actions', 'draftsforfriends' ); ?></th>
			</tr>
		</thead>
		<tbody>
		<?php
			$shared = $this->get_shared_posts();

			if ( empty( $shared ) ) :
		?>
			<tr>
				<td colspan="5"><?php _e( 'No shared drafts!', 'draftsforfriends' ); ?></td>
			</tr>
		<?php
			endif;

			$statuses = get_post_statuses();
			// why isn't this in the initial statuses?
			$statuses['future'] = _x( 'Scheduled', 'post' );

			foreach ( $shared as $share ) :
				$post = get_post( $share['id'] );
				$key = esc_html( $share['key'] );
				$expire_time = $share['expires'];
				$time_to_expire = $expire_time - time();
				$url = get_bloginfo( 'url' ) . '/?p=' . $post->ID . '&draftsforfriends='. $key;
		?>
			<tr>
				<td><?php echo esc_html( $post->ID ); ?></td>
				<td><?php echo esc_html( $post->post_title ); ?></td>
				<td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a></td>
				<td><?php echo $statuses[$post->post_status]; ?></td>
				<td>
					<?php if ( $time_to_expire < 0 ) : ?>
						<span class="expired"><?php echo __( 'Expired' ); ?></span>
					<?php else : ?>
						<span class="timer"
							data-expire="<?php echo esc_attr( $time_to_expire ); ?>">
							<?php echo format_interval( $expire_time ); ?>
						</span>
					<?php endif; ?>
				</td>
				<td class="actions">
					<a class="edit extend" href="">
						<?php _e( 'Extend', 'draftsforfriends' ); ?>
					</a>
					<form class="small" action="" method="post">
						<input type="hidden" name="action" value="extend" />
						<input type="hidden" name="key" value="<?php echo $key; ?>" />
						<input type="submit" class="button" name="draftsforfriends_extend_submit"
							value="<?php _e( 'Extend', 'draftsforfriends' ); ?>"/>
						<?php
							_e( 'by', 'draftsforfriends' );
							include plugin_dir_path( dirname ( __FILE__ ) ). 'views/measure-select.php';
						?>
						<a class="cancel" href="">
							<?php _e( 'Cancel', 'draftsforfriends' ); ?>
						</a>
					</form>
				</td>
				<td class="draftforfriends actions">
					<a class="delete"
						href="edit.php?page=<?php echo esc_html( $page_name ); ?>&action=delete&key=<?php echo $key; ?>">
						<?php _e( 'Delete', 'draftsforfriends' ); ?>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
	<?php include_once plugin_dir_path( dirname ( __FILE__ ) ). 'views/share-form.php'; ?>
</div>
