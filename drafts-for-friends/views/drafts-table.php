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
					<a class="delete" href="edit.php?page=<?php echo $page_name; ?>&action=delete&key=<?php echo $key; ?>">
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
