<?php
//Exit if not called from Wordpress uninstall
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

//JK TODO WHAT DO WE DO ON UNINSTALL??

//Delete option from options table
delete_option('pfund_options');

?>
