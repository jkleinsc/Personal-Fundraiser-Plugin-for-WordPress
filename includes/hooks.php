<?php
/*  Copyright 2011 CURE International  (email : info@cure.org)

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

//Activation of plugin
register_activation_hook( PFUND_FOLDER . 'personal-fundraiser.php', 'pfund_activate' );
//Deactivation of plugin
//register_deactivation_hook( PFUND_FOLDER . 'personal-fundraiser.php', 'pfund_deactivate' );

// Make sure everything is set after upgrade.
add_filter( 'upgrader_post_install', 'pfund_activate' );

add_action( 'init', 'pfund_init' );

if ( is_admin() ) {
	add_action( 'admin_init', 'pfund_admin_init' );

	add_action( 'admin_menu', 'pfund_admin_setup' );

	add_action( 'admin_print_styles-post.php', 'pfund_admin_css' );

	add_action( 'manage_edit-pfund_campaign_sortable_columns', 'pfund_campaign_sortable_columns' );
	
	add_filter( 'manage_pfund_campaign_posts_columns', 'pfund_campaign_posts_columns' );

	add_action( 'manage_pfund_campaign_posts_custom_column', 'pfund_campaign_posts_custom_column', 10, 2 );

	add_filter( 'plugin_action_links', 'pfund_plugin_action_links', 10, 2 );

	add_action( 'post_edit_form_tag' , 'pfund_edit_form_tag' );

	add_action( 'pre_update_option_pfund_options' , 'pfund_pre_update_options' );
	
	add_action( 'publish_pfund_campaign', 'pfund_handle_publish', 10, 2 );

	add_action( 'save_post', 'pfund_save_meta', 10, 2);

	add_action( 'update_option_pfund_options' , 'pfund_update_options', 10, 2 );
}

add_action( 'map_meta_cap', 'pfund_restrict_edit', 10, 4 );

add_action( 'pfund_add_gift', 'pfund_add_gift', 10, 2 );

add_action( 'pfund_processed_transaction', 'pfund_send_donate_email', 10, 2 );

add_action( 'pfund_reached_user_goal', 'pfund_send_goal_reached_email', 10, 3 );

add_filter( 'pfund_render_field_list_item', 'pfund_render_field_list_item', 10, 2 );

add_action( 'template_redirect', 'pfund_display_template' );

add_filter( 'the_posts', 'pfund_handle_action' ) ;

add_filter( 'the_content', 'pfund_handle_content' );

add_filter( 'the_title', 'pfund_handle_title' );

add_filter( 'query_vars', 'pfund_query_vars' );

?>