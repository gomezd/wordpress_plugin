<?php
function tmpl_measure_select() {
	ob_start();
    include_once plugin_dir_path( dirname ( __FILE__ ) ). 'views/measure-select.php';
	$markup = ob_get_contents();
	ob_end_clean();
	return $markup;
}

$measure_select_markup = tmpl_measure_select();
?>

<div class="wrap">
	<h2><?php _e( 'Drafts for Friends', 'draftsforfriends' ); ?></h2>
<?php if ( isset($msg) ) : ?>
	<div id="message" class="updated fade"><?php echo esc_html( $msg ); ?></div>
<?php endif; ?>
	<h3><?php _e( 'Currently shared drafts', 'draftsforfriends' ); ?></h3>
	<table class="widefat draftsforfriends">
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
				$expires = $share['expires'];
				$url = get_bloginfo( 'url' ) . '/?p=' . $post->ID . '&draftsforfriends='. $key;
		?>
			<tr>
				<td><?php echo esc_html( $post->ID ); ?></td>
				<td><?php echo esc_html( $post->post_title ); ?></td>
				<td><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_url( $url ); ?></a></td>
				<td><?php echo $statuses[$post->post_status]; ?></td>
				<td>

					<span class="timer"
						data-expire="<?php echo esc_attr( $expires - time() ); ?>">
						<?php echo format_interval( $expires ) ?>
					</span>
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
							echo $measure_select_markup;
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
	<h3><?php _e( 'Drafts for Friends', 'draftsforfriends' ); ?></h3>
	<form id="draftsforfriends-share" class="draftsforfriends" action="" method="post">
		<p>
			<select id="draftsforfriends-postid" name="post_id">
				<option value=""><?php _e( 'Choose a draft', 'draftsforfriends' ); ?></option>
			<?php
				foreach ( $drafts as $draft ) :
					if ( !empty ( $draft['posts'] ) ) :
			?>
						<option value="" disabled="disabled"></option>
						<option value="" disabled="disabled"><?php echo esc_html( $draft['label'] ); ?></option>
					<?php
						foreach ( $draft['posts'] as $post ):
							if ( empty( $post->post_title ) ) {
								continue;
							}
					?>
							<option value="<?php echo esc_attr( $post->ID ); ?>">
								<?php echo esc_html( $post->post_title ); ?>
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
			<?php echo $measure_select_markup; ?>
		</p>
	</form>
</div>
