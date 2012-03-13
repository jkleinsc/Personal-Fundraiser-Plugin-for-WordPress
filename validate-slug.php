<?php
/*  Copyright 2012 CURE International  (email : info@cure.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
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