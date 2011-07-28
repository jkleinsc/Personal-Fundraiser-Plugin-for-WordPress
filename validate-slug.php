<?php
	if (!defined('ABSPATH')) {
		require_once dirname(__FILE__) . '/../../../wp-load.php';
	}
	require_once( ABSPATH . 'wp-admin/includes/post.php' );
	
	$slug = $_POST['validateValue'];
	if (array_key_exists( 'extraData', $_POST ) ) {
		$campaignId = $_POST['extraData'];
	} else {
		$campaignId = 0;
	}
	$returnArray = array( $_POST['validateId'], $_POST['validateError'] );
	
	$approved_slug = wp_unique_post_slug( $slug, $campaignId, 'publish', 'pfund_campaign', 0 );
	if ($approved_slug == $slug) {
		$returnArray[] = "true";
	} else {
		$returnArray[] = "false";
	}
	echo '{"jsonValidateReturn":'.json_encode( $returnArray ).'}';
?>