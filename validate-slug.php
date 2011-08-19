<?php
	if (!defined('ABSPATH')) {
		require_once dirname(__FILE__) . '/../../../wp-load.php';
	}
	require_once( ABSPATH . 'wp-admin/includes/post.php' );
	
	$slug = $_POST['validateValue'];
	if ( isset( $_POST['extraData'] ) ) {
		$campaign_id = $_POST['extraData'];
	} else {
		$campaign_id = 0;
	}
	$returnArray = array( $_POST['validateId'], $_POST['validateError'] );
	
	$approved_slug = wp_unique_post_slug( $slug, $campaign_id, 'publish', 'pfund_campaign', 0 );
	if ($approved_slug == $slug) {
		$returnArray[] = "true";
	} else {
		$returnArray[] = "false";
	}
	echo '{"jsonValidateReturn":'.json_encode( $returnArray ).'}';
?>