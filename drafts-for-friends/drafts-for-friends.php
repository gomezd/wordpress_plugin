<?php
/*
Plugin Name: Drafts for Friends
Plugin URI: http://automattic.com/
Description: Now you don't need to add friends as users to the blog in order to let them preview your drafts
Author: David Garcia Gomez
Version: 0.0.1
Author URI:
*/
require_once plugin_dir_path( __FILE__ ) . 'includes/class-drafts-for-friends.php';

$DraftsForFriendsInstance = new DraftsForFriends();

?>
