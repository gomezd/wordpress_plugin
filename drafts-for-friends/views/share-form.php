<h3><?php _e( 'Drafts for Friends', 'draftsforfriends' ); ?></h3>
<form id="draftsforfriends-share" action="" method="post">
	<p>
		<select name="post_id">
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
		<?php
			_e( 'for', 'draftsforfriends' );
			include plugin_dir_path( dirname ( __FILE__ ) ). 'views/measure-select.php';
		?>
	</p>
</form>
