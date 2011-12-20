<?php
/*  Copyright 2011 John Kleinschmidt  (email : jk@cure.org)

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

/**
 * Activate the plugin
 */
function pfund_activate() {
	if ( version_compare( get_bloginfo( 'version' ), '3.1', '<' ) ) {
		deactivate_plugins( PFUND_BASENAME );
	} else {
		$options_changed = false;
		$pfund_options = get_option( 'pfund_options' );
		if ( ! $pfund_options ) {
			//Setup default options
			$pfund_options = array(
				'allow_registration' => false,
				'campaign_slug' => 'give',
				'cause_slug' => 'causes',
				'currency_symbol' => '$',
				'date_format' => 'm/d/y',
				'login_required' => true,
				'mailchimp' => false,
				'submit_role' => array( 'administrator' ),
				'fields' => array(
					'camp-title' => array(
						'label' => __( 'Title', 'pfund' ),
						'desc' => __( 'The title of your campaign', 'pfund' ),
						'type' => 'camp_title',
						'required' => true
					),
					'camp-location' => array(
						'label' => __( 'URL', 'pfund' ),
						'desc' => __( 'The URL for your campaign', 'pfund' ),
						'type' => 'camp_location',
						'required' => true
					),
					'end-date'  => array(
						'label' => __( 'End Date', 'pfund' ),
						'desc' => __( 'The date your campaign ends', 'pfund' ),
						'type' => 'end_date',
						'required' => false
					),
					'gift-goal' => array(
						'label' => __( 'Goal', 'pfund' ),
						'desc' => __( 'The amount you hope to raise', 'pfund' ),
						'type' => 'user_goal',
						'required' => true
					),
					'gift-tally' => array(
						'label' => __( 'Total Raised', 'pfund' ),
						'desc' => __( 'Total donations received', 'pfund' ),
						'type' => 'gift_tally',
						'required' => true
					),
					'giver-tally' => array(
						'label' => __( 'Giver Tally', 'pfund' ),
						'desc' => __( 'The number of unique givers for the campaign.', 'pfund' ),
						'type' => 'giver_tally',
						'required' => true
					)
				)
			);
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['cause_root'] ) ) {
			$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => '_causes_listing',
				'post_title' => __( 'Causes Listing', 'pfund' ),
				'post_content' => '',
				'post_type' => 'pfund_cause_list'
			);
			$cause_root_id = wp_insert_post( $page );
			$pfund_options['cause_root'] = $cause_root_id;
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['campaign_root'] ) ) {
			$page = array(
				'comment_status' => 'closed',
				'ping_status' => 'closed',
				'post_status' => 'publish',
				'post_content' => '',
				'post_name' => '_campaign_listing',
				'post_title' => __( 'Campaign Listing', 'pfund' ),
				'post_content' => '',
				'post_type' => 'pfund_campaign_list'
			);
			$cause_root_id = wp_insert_post( $page );
			$pfund_options['campaign_root'] = $cause_root_id;
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['date_format'] ) ) {
			$pfund_options['date_format'] = 'm/d/y';
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['mailchimp'] ) ) {
			$pfund_options['mailchimp'] = false;
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['paypal_sandbox'] ) ) {
			$pfund_options['paypal_sandbox'] = false;
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['fields']['end-date'] ) ) {
			$pfund_options['fields']['end-date'] = array(
				'label' => __( 'End Date', 'pfund' ),
				'desc' => __( 'The date your campaign ends', 'pfund' ),
				'type' => 'end_date',
				'required' => false
			);
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['fields']['giver-tally'] ) ) {
			$pfund_options['fields']['giver-tally'] = array(
				'label' => __( 'Giver Tally', 'pfund' ),
				'desc' => __( 'The number of unique givers for the campaign.', 'pfund' ),
				'type' => 'giver_tally',
				'required' => true
			);
			$options_changed = true;
		}
		if ( ! isset( $pfund_options['campaign_listing'] ) ) {
			$pfund_options['campaign_listing'] = true;
			$options_changed = true;
		}

		if ( ! isset( $pfund_options['cause_listing'] ) ) {
			$pfund_options['cause_listing'] = true;
			$options_changed = true;
		}

		if ( isset( $pfund_options['version'] ) ) {
			$old_version = 	$pfund_options['version'];
		} else {
			$old_version = 	'0.7';
		}

		if ( version_compare( $old_version, '0.7.3', '<' ) ) {
			_pfund_add_sample_cause();
			if ( ! in_array( 'administrator', $pfund_options['submit_role'] ) ) {
				$pfund_options['submit_role'][] = 'administrator';
				$options_changed = true;
			}
		}

		if ( empty( $pfund_options['paypal_donate_btn'] ) ) {
			$sample_btn = '<form action="" method="post">';
			$sample_btn .= '<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" onclick="alert(\'This is a test button.  Please view the readme to setup your PayPal donate button.\');return false;">';
			$sample_btn .= '<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">';
			$sample_btn .= '</form>';
			$pfund_options['paypal_donate_btn'] = $sample_btn;
			$options_changed = true;
		}

		if ( $old_version != PFUND_VERSION ) {
			$pfund_options['version'] = PFUND_VERSION;
			$options_changed = true;
		}
		if ( $options_changed == true ) {
			update_option( 'pfund_options', $pfund_options );
		}
	}
	_pfund_register_types();

	$role =& get_role( 'administrator' );
	if ( !empty( $role ) ) {
		$role->add_cap( 'edit_campaign' );
	}
	pfund_add_rewrite_rules();
}

/**
 * Add personal fundraiser rewrite rules
 * @param boolean $flush_rules If true, flush the rewrite rules
 */
function pfund_add_rewrite_rules( $flush_rules = true ) {
	$options = get_option( 'pfund_options' );
	$campaign_root = $options['campaign_slug'];
	$cause_root = $options['cause_slug'];	
	if ( $options['campaign_listing'] ) {
		add_rewrite_rule("$campaign_root$", "index.php?pfund_action=campaign-list",'top');
	}
	add_rewrite_rule($campaign_root.'/([0-9]+)/?', 'index.php?post_type=pfund_campaign&p=$matches[1]&preview=true','top');
	if ( $options['cause_listing']  ) {
		add_rewrite_rule("$cause_root$", "index.php?pfund_action=cause-list",'top');
	}
	if ( $flush_rules ) {
		flush_rewrite_rules();
	}
}

/**
 * Initialize the plugin.
 */
function pfund_init() {
	global $pfund_processed_action;
	$pfund_options = get_option( 'pfund_options' );
	if ( ! isset( $pfund_options['version'] ) || $pfund_options['version'] != PFUND_VERSION ) {
		pfund_activate();
	}
	$pfund_processed_action = false;
	_pfund_load_translation_file();
	_pfund_register_types();
	pfund_add_rewrite_rules( false );	
	if ( ! is_admin() ) {
		pfund_setup_shortcodes();
	}
}


/**
 * Before personal fundraiser options are saved, add/update sort order for the
 * fields.
 * @param mixed $new_options The options that are about to be saved.
 * @param mixed $old_options The current options.
 * @return mixed the options to save.
 */
function pfund_pre_update_options( $new_options, $old_options ) {
	$i=0;
	foreach ( $new_options['fields'] as $idx => $field) {
		$field['sortorder'] = $i++;		
		$new_options['fields'][$idx] = $field;		
	}

	$checkboxes = array( 'allow_registration', 'approval_required', 
		'campaign_listing','cause_listing','login_required',  'mailchimp',
		'paypal_sandbox' );
	foreach ( $checkboxes as $field_name) {
		if ( isset( $new_options[$field_name] )
				&& $new_options[$field_name] == 'true' ) {
			$new_options[$field_name] = true;
		} else {
			$new_options[$field_name] = false;
		}
	}

	$new_options = array_merge( $old_options, $new_options );
	return $new_options;
}


/**
 * Add personal fundraiser query vars.
 * @param array $query_array current list of query vars
 * @return array updated list of query vars.
 */
function pfund_query_vars( $query_array ) {
	$query_array[] = 'pfund_action';
	$query_array[] = 'pfund_cause_id';
	return $query_array;
}


/**
 * Handler that fires when personal fundraiser options are updated.
 * @param mixed $oldvalue options before they were updated.
 * @param mixed $newvalue options after they were updated.
 */
function pfund_update_options( $oldvalue, $newvalue ) {
	_pfund_register_types();
	$current_submit_roles = $newvalue['submit_role'];

	global $wp_roles;
	$avail_roles = $wp_roles->get_names();
	foreach ( $avail_roles as $key => $desc ) {
		$role =& get_role( $key );
		if( in_array ( $key, $current_submit_roles ) ) {
			$role->add_cap( 'edit_campaign' );		
		} else {
			$role->remove_cap( 'edit_campaign' );
		}
	}
	pfund_add_rewrite_rules();	
}

/**
 * Adds a sample cause for plugin demonstration purposes.
 */
function _pfund_add_sample_cause() {
	$stat_li = '<li class="pfund-stat"><span class="highlight">%s</span>%s</li>';
	$sample_content = '<ul>';
	$sample_content .= sprintf( $stat_li, '$[pfund-gift-goal]', __( 'funding goal', 'pfund' ) );
	$sample_content .= sprintf( $stat_li, '$[pfund-gift-tally]', __( 'raised', 'pfund' ) );
	$sample_content .= sprintf( $stat_li, '[pfund-giver-tally]', __( 'givers', 'pfund' ) );
	$sample_content .= sprintf( $stat_li, '[pfund-days-left]', __( 'days left', 'pfund' ) );
	$sample_content .= '</ul>';
	$sample_content .= '<div style="clear: both;">';
	$sample_content .= '	<p>'.__( 'I have an event on [pfund-end-date] that I am involved with for my cause.', 'pfund' ).'</p>';
	$sample_content .= '	<p>'.__( 'I am hoping to raise $[pfund-gift-goal] for my cause.', 'pfund' ).'</p>';
	$sample_content .= '	<p>'.__( 'So far I have raised $[pfund-gift-tally].  If you would like to contribute to my cause, click on the donate button below:', 'pfund' ).'</p>';
	$sample_content .= '	<p>[pfund-donate]<p>';
	$sample_content .= '</div>';
	$sample_content .= '[pfund-edit]';
		
	$cause = array(
		'post_name' => 'sample-cause',
		'post_title' => __( 'Help Raise Money For My Cause', 'pfund' ),
		'post_content' => $sample_content,
		'post_status' => 'publish',
		'post_type' => 'pfund_cause'
	);
	$cause_root_id = wp_insert_post( $cause );
}

/**
 * Loads the translation file; fired from init action.
 */
function _pfund_load_translation_file() {
	load_plugin_textdomain( 'pfund', false, PFUND_FOLDER . 'translations' );
}

/**
 * Register the post types used by personal fundraiser.
 */
function _pfund_register_types() {
	$pfund_options = get_option( 'pfund_options' );
	$template_def = array(
		'public' => true,
		'query_var' => 'pfund_cause',
		'rewrite' => array(
			'slug' => $pfund_options['cause_slug'],
			'with_front' => false,
		),
		'hierarchical' => true,
		'label' => __( 'Causes', 'pfund' ),
		'labels' => array(
			'name' => __( 'Causes', 'pfund' ),
			'singular_name' => __( 'Cause', 'pfund' ),
			'add_new' => __( 'Add New Cause', 'pfund' ),
			'add_new_item' => __( 'Add New Cause', 'pfund' ),
			'edit_item' => __( 'Edit Cause', 'pfund' ),
			'view_item' => __( 'View Cause', 'pfund' ),
			'search_items' => __( 'Search Causes', 'pfund' ),
			'not_found' => __( 'No Causes Found', 'pfund' ),
			'not_found_in_trash' => __( 'No Causes Found In Trash', 'pfund' ),
		)
	);
	register_post_type( 'pfund_cause', $template_def );
	register_post_type( 'pfund_cause_list' );

	$campaign_def = array(
		'public' => true,
		'query_var' => 'pfund_campaign',
		'rewrite' => array(
			'slug' => $pfund_options['campaign_slug'],
			'with_front' => false
		),
		'hierarchical' => true,
		'label' => __( 'Campaigns', 'pfund' ),
		'labels' => array(
			'name' => __( 'Campaigns', 'pfund' ),
			'singular_name' => __( 'Campaign', 'pfund' ),
			'add_new' => __( 'Add New Campaign', 'pfund' ),
			'add_new_item' => __( 'Add New Campaign', 'pfund' ),
			'edit_item' => __( 'Edit Campaign', 'pfund' ),
			'view_item' => __( 'View Campaign', 'pfund' ),
			'search_items' => __( 'Search Campaigns', 'pfund' ),
			'not_found' => __( 'No Campaigns Found', 'pfund' ),
			'not_found_in_trash' => __( 'No Campaigns Found In Trash', 'pfund' ),
		),
		'supports' => array(
			'title'
		),
		'capabilities' => array(
			'edit_post' => 'edit_campaign'
		),
		'map_meta_cap' => true
	);
	register_post_type( 'pfund_campaign', $campaign_def );
	register_post_type( 'pfund_campaign_list' );
}

?>