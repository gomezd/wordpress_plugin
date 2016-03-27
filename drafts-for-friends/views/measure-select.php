<?php
/**
 * Expire extend measure select template
 */
?>
<input name="expires" class="fixed" type="text" value="2" size="4"/>
<select name="measure">
	<option value="s"><?php echo __( 'seconds', 'draftsforfriends' ); ?></option>
	<option value="m"><?php echo __( 'minutes', 'draftsforfriends' ) ?></option>
	<option value="h" selected="selected"><?php echo __( 'hours', 'draftsforfriends' ); ?></option>
	<option value="d"><?php echo __( 'days', 'draftsforfriends' ); ?></option>
</select>
