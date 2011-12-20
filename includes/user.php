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

require_once( PFUND_DIR . '/includes/paypalfunctions.php' );

/**
 * Add a gift donation to the campaign.  Fired from the pfund_add_gift action.
 * This function will update the gift tally for the campaign, add a comment
 * detailing the donation and fire actions for additional processing.
 * @param array $transaction_array array detailing the donation with the
 * following keys:
 *   success -- boolean indicating if transaction was successful.
 *   amount -- Transaction amount
 *   donor_first_name -- Donor first name
 *   donor_last_name -- Donor last name
 *   donor_email -- Donor email
 *   anonymous -- boolean indicating if the gift was anonymous.
 *   error_code -- When an error occurs, one of the following values is returned:
 *		no_response_returned -- A response was not received from PayPal.
 *		paypal_returned_failure -- PayPal returned a failure.
 *		wp_error -- A WP error was returned.
 *		exception_encountered -- An unexpected exception was encountered.
 *	 wp_error -- If the error_code is wp_error, the WP_Error object returned.
 *	 error_msg -- Text message describing error encountered.
 * @param mixed $post the post object containing the campaign.
 */
function pfund_add_gift( $transaction_array, $post ) {
	$processed_transactions = get_post_meta( $post->ID, '_pfund_transaction_ids' );
	$transaction_nonce = $transaction_array['transaction_nonce'];
	//Make sure this transaction hasn't already been processed.
	if ( is_array( $processed_transactions ) && in_array( $transaction_nonce, $processed_transactions ) ) {
		return;
	} else if ( !is_array( $processed_transactions ) && $processed_transactions == $transaction_nonce ) {
		return;
	}
	if ( $transaction_array['success'] == true) {
		//Update gift tally
		$tally = get_post_meta( $post->ID, '_pfund_gift-tally', true );
		if ( $tally == '' ) {
			$tally = 0;
		}
		if ( ! empty( $transaction_array['tally_amount'] ) && is_numeric( $transaction_array['tally_amount'] ) ) {
			$tally += $transaction_array['tally_amount'];
		} else {
			$tally += $transaction_array['amount'];
		}
		update_post_meta( $post->ID, '_pfund_gift-tally', $tally );
		add_post_meta( $post->ID, '_pfund_transaction_ids', $transaction_nonce );

        if ( empty( $transaction_array['anonymous'] ) && (
                empty( $transaction_array['donor_email'] ) ||
                empty( $transaction_array['donor_first_name'] ) ||
                empty( $transaction_array['donor_last_name'] ) ) ) {
            $transaction_array['anonymous'] = true;
        }

		add_post_meta( $post->ID, '_pfund_transactions', $transaction_array );
		_pfund_update_giver_tally( $post->ID );

		$options = get_option( 'pfund_options' );
		//Add comment for transaction.
		if ( isset( $transaction_array['anonymous'] ) &&
				$transaction_array['anonymous'] == true ) {
			$commentdata = array(
				'comment_post_ID' => $post->ID,
				'comment_content' => sprintf(
					__( 'An anonymous gift of %s%d was received.', 'pfund' ),
					$options['currency_symbol'],
					$transaction_array['amount']
				),
				'comment_approved' => 1
			);
		} else {
			$commentdata = array(
				'comment_post_ID' => $post->ID,
				'comment_author' => $transaction_array['donor_first_name'] . ' ' . $transaction_array['donor_last_name'],
				'comment_author_email' => $transaction_array['donor_email'],
				'comment_content' => sprintf(
					__( '%s %s donated %s%d.', 'pfund' ),
					$transaction_array['donor_first_name'],
					$transaction_array['donor_last_name'],
					$options['currency_symbol'],
					$transaction_array['amount']
				),
				'comment_approved' => 1
			);
		}
		if ( ! empty( $transaction_array['comment'] ) ) {
			$commentdata['comment_content'] = $transaction_array['comment'];
		}
		$commentdata['comment_author_IP'] = '';
		$commentdata['comment_author_url'] = '';
		$commentdata = wp_filter_comment( $commentdata );
		$comment_id = wp_insert_comment( $commentdata );
		add_comment_meta($comment_id, 'pfund_trans_amount', $transaction_array['amount']);
		//Fire action for any additional processing.
		do_action( 'pfund_processed_transaction', $transaction_array, $post );
		$goal = get_post_meta( $post->ID, '_pfund_gift-goal', true );
		if ( $tally >= $goal ) {
			do_action('pfund_reached_user_goal', $transaction_array, $post, $goal );
		}
	}
}

/**
 * Shortcode handler for pfund-campaign-list to display the list of current
 * campaigns.
 * @return string HTML that contains the campaign list.
 */
function pfund_campaign_list() {
	global $wp_query;
	wp_enqueue_style( 'pfund-user', pfund_determine_file_location('user','css'),
			array(), PFUND_VERSION );
	$post_query = array(
		'post_type' => 'pfund_campaign',
		'orderby' => 'title',
		'order' => 'ASC',
		'posts_per_page' => -1
	);

	if ( isset(  $wp_query->query_vars['pfund_cause_id'] ) ) {
		$post_query['meta_query'] = array(
			array(
				'key' => '_pfund_cause_id',
				'value' => $wp_query->query_vars['pfund_cause_id']
			)
		);
	}
	$campaigns = get_posts($post_query);
	$list_content = '<ul class="pfund-list">';
	foreach ($campaigns as $campaign) {
		$list_content .= '<li>';
		$list_content .= '	<h2>';
		$list_content .= '		<a href="'.get_permalink($campaign->ID).'">'.$campaign->post_title.'</a></h2>';
		$list_content .= '</li>';

	}
	$list_content .= '</ul>';
	return $list_content;
}

/**
 * Shortcode handler for pfund-campaign-permalink to get the permalink for the
 * current campaign.
 * @return string the permalink for the current campaign.
 */
function pfund_campaign_permalink() {
	global $post;
	if( $post->ID == null || $post->post_type != 'pfund_campaign' ) {
		return '';
	}
	return get_permalink( $post->ID );
}

/**
 * Shortcode handler for pfund-cause-list to display the list of current causes.
 * @return string HTML that contains the campaign list.
 */
function pfund_cause_list() {
	wp_enqueue_style( 'pfund-user', pfund_determine_file_location('user','css'),
			array(), PFUND_VERSION );
	$options = get_option( 'pfund_options' );
	$causes = get_posts(
		array(
			'post_type' => 'pfund_cause',
			'orderby' => 'title',
			'order' => 'ASC',
			'posts_per_page' => -1
		)
	);
	$campaign_list_url = '/'.$options['campaign_slug'].'/?pfund_cause_id=';


	$user_can_create = _pfund_current_user_can_create( $options );
	$list_content = '<ul class="pfund-list">';
	foreach ($causes as $cause) {
		$list_content .= '<li>';
		$list_content .= '	<h2>';
		$list_content .= '		<a href="'.$campaign_list_url.$cause->ID.'">'.$cause->post_title.'</a></h2>';
		$list_content .= '<p class="pfund-cause-description">';
		$cause_img = get_post_meta($cause->ID, '_pfund_cause_image', true);
		if ( $cause_img ) {
			$list_content .= '<img class="pfund-image" width="184" src="'.wp_get_attachment_url( $cause_img ).'"/>';
		}
		$list_content .= '<span>';
		$list_content .= get_post_meta($cause->ID, '_pfund_cause_description', true);
		$list_content .= '</span>';
		$list_content .= '</p>';
		if ( $user_can_create ) {
			$list_content .= '<p>';
			$list_content .= '<a href="'.get_permalink($cause->ID).'">'.__( 'Create My Page', 'pfund' ).'</a>';
			$list_content .= '</p>';
		}
		$list_content .= '</li>';

	}
	$list_content .= '</ul>';
	return $list_content;
}

/**
 * Handler for campaign comments shortcode (pfund-comments).
 * @return string The HTML for the campaign contents.
 */
function pfund_comments() {
	global $post;
	if( $post->ID == null || $post->post_type != 'pfund_campaign' ) {
		return '';
	}
	if ( $post->post_status != 'publish' ) {
		return '';
	}
	$comment_list = get_comments( array( 'post_id'=>$post->ID ) );
	$return_content = '<ul class="pfund-comments">';
	foreach ( $comment_list as $comment ) {
		$return_content .= '<li class="pfund-comment" id="comment-'.$comment->comment_ID.'">';
		$return_content .= '<div class="comment-author vcard">';					
		if ( function_exists( 'get_avatar' ) ) {
			$return_content .= get_avatar( $comment, 32 );
		}
		$return_content .= '<cite class="fn">';
		$return_content .= get_comment_author_link( $comment->comment_ID );
		$return_content .= '</cite>';
		$return_content .= '</div>';
		$return_content .= $comment->comment_content;
		$return_content .= '</li>';
	}
	
	$return_content .= '</ul>';
	
	if ( comments_open() ) {
		$return_content .= '<div id="pfund-comment-resp">';
		$return_content .= '<h3>'.__( 'Post a Comment', 'pfund' ).'</h3>';

		if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) {
			$return_content .= '<p>';
			
			$return_content .= sprintf(
				__( 'You must be <a href="%s">logged in</a> to post a comment.', 'pfund' ),
				wp_login_url( get_permalink() )
			);
			
		}  else {
			$return_content .= '<form action="'.get_option( 'siteurl' ) .'/wp-comments-post.php" method="post" id="commentform">';
			if ( ! is_user_logged_in() ) {
				$return_content .= '<p><input type="text" name="author" id="author" size="22" tabindex="1"/>';
				$return_content .= '<label for="author"><small>'.__( 'Name', 'pfund' ).'</small></label></p>';
				$return_content .= '<p><input type="text" name="email" id="email" size="22" tabindex="2" />';
				$return_content .= '<label for="email"><small>'.__( 'Mail (will not be published)', 'pfund' ).'</small></label></p>';
			}
			$return_content .= '<p><textarea name="comment" id="comment" cols="58" rows="10" tabindex="4"></textarea></p>';
			$return_content .= '<p><input name="submit" type="submit" id="submit" tabindex="5" value="'.esc_attr__( 'Submit Comment', 'pfund').'" />';
			$return_content .= get_comment_id_fields();
			$return_content .= '</p>';
			$return_content .= '</form>';
		}
		$return_content .= '</div>';
	}
	return $return_content;
}

/**
 * Short code handler for pfund-days-left shortcode.  Returns the number of days
 * before the campaign ends.  Due to timezone differences if the end date is
 * within 24 hours of the current date, days left will return 1.  If the current
 * date is over 24 hours past the end date, days left will return 0.  Otherwise
 * the actual number of days will be returned.
 * @return int The number of days left in the campaign
 */
function pfund_days_left() {
	global $post;
	if ( ! pfund_is_pfund_post() ){
		return '';
	}
	$postid = $post->ID;
	if ( _pfund_is_edit_new_campaign() ) {
		$postid = _pfund_get_new_campaign()->ID;
	}
	$end_date = get_post_meta( $post->ID, '_pfund_end-date', true );
	$now = time();
	$diff = ( strtotime( $end_date ) - $now );
	$days = round($diff / 86400);
	if ( $days < -1 ) {
		$days = 0;
	} else if ( $days <= 1 ) {
		$days = 1;
	}
	return $days;
}

/**
 * Direct causes and campaigns to the proper display template.
 * @return void
 */
function pfund_display_template() {
	global $post;
	if ( ! pfund_is_pfund_post( $post, true ) ){
		//Only change the template for pfund causes and campaigns
		return;
	}
	$options = get_option( 'pfund_options' );
	$script_reqs = array( 'jquery', 'jquery-ui-dialog' );
	if ( $options['allow_registration'] ) {
		$script_reqs[] = 'jquery-form';
	}
	wp_enqueue_script( 'pfund-user', pfund_determine_file_location('user','js'),
			$script_reqs, PFUND_VERSION, true );
	wp_enqueue_style( 'pfund-user', pfund_determine_file_location('user','css'),
			array(), PFUND_VERSION );
	$admin_email = get_option( 'admin_email' );
	$script_vars = array(
		'cancel_btn' => __( 'Cancel', 'pfund' ),
		'continue_editing_btn' => __( 'Continue Editing', 'pfund' ),
		'email_exists' => __( 'This email address is already registered', 'pfund' ),
		'invalid_email' =>__( 'Invalid email address', 'pfund' ),
		'login_btn' => __( 'Login', 'pfund' ),
		'mask_passwd' => __( 'Mask password', 'pfund' ),
		'ok_btn' => __( 'Ok', 'pfund' ),
		'register_btn' => __( 'Register', 'pfund' ),
		'register_fail' => sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you.  Please contact <a href="mailto:%s">us</a>.' ), $admin_email ),
		'reg_wait_msg' => __( 'Please wait while your registration is processed.', 'pfund' ),
		'save_warning' => __( 'Your campaign has not been saved.  If you would like to save your campaign, stay on this page, click on the Edit button and then click on the Ok button.', 'pfund' ),
		'unmask_passwd' => __( 'Unmask password', 'pfund' ),
		'username_exists' => __( 'This username is already registered', 'pfund' )
	);
	if ( ! empty( $options['date_format'] ) ) {
		$script_vars['date_format'] = _pfund_get_jquery_date_fmt( $options['date_format'] );
	}
	$login_fn = apply_filters( 'pfund_login_javascript_function', '' );
	if ( ! empty( $login_fn ) ) {
		$script_vars['login_fn'] = $login_fn;
	}
	$register_fn = apply_filters( 'pfund_register_javascript_function', '' );
	if ( ! empty( $register_fn ) ) {
		$script_vars['register_fn'] = $register_fn;
	}
	wp_localize_script( 'pfund-user', 'pfund', $script_vars);

	wp_enqueue_script( 'jquery-ui-datepicker', PFUND_URL.'js/jquery.ui.datepicker.js', array( 'jquery-ui-core' ), '1.8.14', true );
	wp_enqueue_style( 'jquery-ui-pfund', PFUND_URL.'css/smoothness/jquery.ui.pfund.css', array(), '1.8.14' );

	wp_enqueue_script( 'jquery-validationEngine', PFUND_URL.'js/jquery.validationEngine.js', array( 'jquery'), 1.7, true );
	wp_enqueue_script( 'jquery-validationEngine-lang', PFUND_URL.'js/jquery.validationEngine-'.get_locale().'.js', array( 'jquery'), 1.7, true );
	wp_enqueue_style( 'jquery-validationEngine', PFUND_URL.'css/jquery.validationEngine.css', array(), 1.7 );

	$templates[] = 'page.php';
	$template = apply_filters( 'page_template', locate_template( $templates ) );

	if( '' != $template ) {
		load_template( $template );
		// The exit tells WP to not try to load any more templates
		exit;
	}
}

/**
 * Shortcode handler for pfund-donate shortcode.  Displays a button for
 * accepting donations.
 * @return string HTML for a donate button.
 */
function pfund_donate_button() {
	global $post;
	$options = get_option( 'pfund_options' );
	if ( ! pfund_is_pfund_post() ) {
		return '';
	}	
	$page_url = get_permalink( $post );
	$gentime = time();
	$returnparms = array(
		'g' => $gentime,
		'n' => 	wp_create_nonce( 'pfund-donate-campaign'.$post->ID.$gentime),
		'pfund_action'=>'donate-campaign',
		't' => 'pp'
	);
	$return_url = $page_url . '?' . http_build_query($returnparms);
	$returnparms['t'] = 'ipn';
	$notify_url = $page_url . '?' . http_build_query($returnparms);
	$donate_btn = $options['paypal_donate_btn'];
	if ( ! empty( $donate_btn ) ) {
		$btn_doc = new DOMDocument();
		$btn_doc->loadHTML( $donate_btn );
		$form_node = $btn_doc->getElementsByTagName('form')->item(0);
		$form_node->setAttribute( 'class' , 'pfund-donate-form' );
		_pfund_create_input_node( $btn_doc, $form_node, 'return', $return_url );
		_pfund_create_input_node( $btn_doc, $form_node, 'cancel_return', $page_url );
		_pfund_create_input_node( $btn_doc, $form_node, 'notify_url', $notify_url );
		$tmp_node = $btn_doc->createElement( 'br' );
		$form_node->appendChild($tmp_node);
		$tmp_node = $btn_doc->createElement( 'label',
				__('Anonymous gift', 'pfund')
		);
		$tmp_node->setAttribute( 'for' , 'pfund-anonymous-donate' );
		$form_node->appendChild($tmp_node);
		$tmp_node = _pfund_create_input_node( $btn_doc, $form_node, 'custom', 'anon', 'checkbox' );
		$tmp_node->setAttribute( 'id', 'pfund-anonymous-donate' );
		$donate_btn = $btn_doc->saveHTML();
		$form_start = strpos( $donate_btn, '<form' );
		$form_length = (strpos( $donate_btn, '</form>', $form_start ) - $form_start) + 7;
		$donate_btn = substr( $donate_btn, $form_start, $form_length );
	}
	$donate_btn = apply_filters( 'pfund_donate_button', $donate_btn, $page_url, $return_url );
	return $donate_btn;
}

/**
 * Shortcode handler for pfund-edit to generate campaign creation/editing form
 * and button to edit the personal fundraising fields.
 * @return string HTML for form and edit button.
 */
function pfund_edit() {
	global $post, $current_user;
	$current_user = wp_get_current_user();
	if ( ! pfund_is_pfund_post() ){
		return '';
	} else if ( ! _pfund_current_user_can_create( ) ) {
		return '';	
	} else if ( $post->post_type == 'pfund_campaign' && $post->post_author != $current_user->ID ) {
		return '';
	}
	if( $post->post_type == 'pfund_cause' ) {
		$editing_campaign = _pfund_is_edit_new_campaign();
		if ( $editing_campaign ) {
			$campaign = _pfund_get_new_campaign();
			$campaign_id = $campaign->ID;
			$campaign_title = $campaign->post_title;
		} else {
			$campaign_title = $post->post_title;			
			$campaign_id = null;
		}
		$default_goal = get_post_meta( $post->ID, '_pfund_cause_default_goal', true);
	} else {
		$editing_campaign = true;
		$campaign_id = $post->ID;
		$campaign_title = $post->post_title;
		$campaign = $post;
		$default_goal = '';
	}

	$wait_title = esc_attr__( 'Please wait', 'pfund' );
	if ( $editing_campaign ) {
		$dialog_title = esc_attr__( 'Edit Campaign', 'pfund' );
		$dialog_desc = esc_html__( 'Change your campaign by editing the information below.', 'pfund' );
		$wait_desc = esc_html__( 'Please wait while your campaign is updated.', 'pfund' );
		$dialog_id = 'pfund-edit-dialog';
	} else {
		$dialog_title = esc_attr__( 'Create Campaign', 'pfund' );
		$dialog_desc = esc_html__( 'Please fill in the following information to create your campaign.', 'pfund' );
		$wait_desc = esc_html__( 'Please wait while your campaign is created.', 'pfund' );
		$dialog_id = 'pfund-add-dialog';
	}
	$return_form = '<div id="pfund-wait-dialog" style="display:none;" title="'.$wait_title.'">';
	$return_form .= '<div>'.$wait_desc.'</div>';
	$return_form .= '</div>';
	$return_form .= '<div id="'.$dialog_id.'" style="display:none;" title="'.$dialog_title.'">';
	$return_form .= '<div>'.$dialog_desc.'</div>';
	$return_form .= '<form enctype="multipart/form-data" action="" method="post" name="pfund_form" id="pfund-form">';

	if ( $editing_campaign ) {
		$return_form .= '	<input type="hidden" name="pfund_action" value="update-campaign"/>';
		$return_form .= '	<input id="pfund-campaign-id" type="hidden" name="pfund_campaign_id" value="'.$campaign_id.'"/>';
		$return_form .= wp_nonce_field( 'pfund-update-campaign'.$campaign_id, 'n', true , false );
	} else {
		$return_form .= '	<input type="hidden" name="pfund_action" value="create-campaign"/>';
		$return_form .= wp_nonce_field( 'pfund-create-campaign'.$post->ID, 'n', true , false );
	}
	$return_form .= pfund_render_fields( $campaign_id, $campaign_title, $editing_campaign, $default_goal );
	$return_form .= '</form>';
	$return_form .= '</div>';
	$return_form .= '<script type="text/javascript">';
	$validateSlug = array(
		'file' => PFUND_URL.'validate-slug.php',
		'alertTextLoad' => __( 'Please wait while we validate this location', 'pfund' ),
		'alertText' => __( '* This location is already taken', 'pfund' )
	);
	if ( $editing_campaign ) {
		$validateSlug['extraData'] = $campaign_id;
	}
	$return_form .= 'jQuery(function($) {$.validationEngineLanguage.allRules.pfundSlug = '.json_encode($validateSlug).'});';
	$return_form .= '</script>';
	$return_form .= '<button class="pfund-edit-btn">'. __( 'Edit', 'pfund' ).'</button>';
	return $return_form;	
}

/**
 * Shortcode handler for pfund-giver-list shortcode.  Returns markup for the
 * list of supporters for the current campaign.
 * @param array $attrs the attributes for the shortcode.  The supported
 * attributes are:
 * -- row_max -- Number of supporters to display in one row
 * -- row_end_class -- Class to apply to last support in a row.
 * @return string the HTML representing the list of supporters for the current
 * campaign.
 */
function pfund_giver_list( $attrs ) {	
	global $post;
	if ( ! empty( $attrs ) && ! empty( $attrs['row_max'] ) &&
			! empty( $attrs['row_end_class'] ) ) {
		$row_end_class = $attrs['row_end_class'];
		$row_max = $attrs['row_max'];
	} else {
		$row_end_class = 'row-end clearfix';
		$row_max = 3;
	}
	$max_givers = -1;
	if ( ! empty( $attrs['max_givers'] ) ) {
		$max_givers = intval( $attrs['max_givers'] );
	}
	if ( ! pfund_is_pfund_post() ) {
		return '';
	} else {		
		$givers = get_post_meta( $post->ID, '_pfund_givers', true );
		if ( empty( $givers ) ) {
			return '';
		}
		if ( $max_givers > -1 && count($givers) > $max_givers ) {
			$email_array = array_rand( $givers, $max_givers );
		} else {
			$email_array = array_keys( $givers );
		}
		$giver_count = 0;
		$list = '<ul class=".pfund-supporters-list">';
		foreach( $email_array as $email ) {
			$donor = $givers[$email];
			$giver_count++;
			$class = 'pfund-supporter';
			if ($giver_count % $row_max == 0) {
				$class .= ' '.$row_end_class;
			}
			$list .= '<li class="'.$class.'">';
			$list .= '	<span class="pfund-supporter-img">';
			$list .= get_avatar($email, '50');
			$list .= '	</span>';
			$list .= '	<span class="pfund-supporter-name">';
			$list .= $donor['first_name'].' '.$donor['last_name'];
			$list .= '	</span>';
			$list .= '</li>';
		}
		$list .= '</ul>';
		return $list;
	}
}

/**
 * Handler for actions performed on a cause.
 * @param mixed $posts The current posts provided by the_posts filter.
 * @return mixed $posts The current posts provided by the_posts filter.
 */
function pfund_handle_action( $posts ) {
	global $pfund_processed_action, $pfund_processing_action, $wp_query;
	if ( empty ( $posts ) ) {
		return $posts;
	}
	$post = $posts[0];
	if ( isset( $wp_query->query_vars['pfund_action'] ) 
			&& ! $pfund_processed_action && ! $pfund_processing_action ) {
		$pfund_processing_action = true;
		$action = $wp_query->query_vars['pfund_action'];
		if ( ! in_array( $action, array( 'cause-list', 'campaign-list' ) ) ) {			
			if ( ! pfund_is_pfund_post( $post ) ){
				return $posts;
			}
			if ( _pfund_is_edit_new_campaign() ) {
				$referer_action = 'pfund-'.$action._pfund_get_new_campaign()->ID;
			} else {
				$referer_action = 'pfund-'.$action.$post->ID;
			}
			if( $action == 'donate-campaign' ) {
				$referer_action .= $_REQUEST['g'];
			}			
			if( $action == 'user-login' ) {
				global $current_user;
				get_currentuserinfo();
				$save_user = $current_user;
				$current_user = new WP_User(0);
				check_admin_referer( $referer_action, 'n' );
				$current_user = $save_user;
			} else {
				check_admin_referer( $referer_action, 'n' );
			}
		}			
		switch( $action ) {
			case 'campaign-list':
				$posts = _pfund_campaign_list_page();
				break;
			case 'cause-list':
				$posts = _pfund_cause_list_page();
				break;
			case 'create-campaign':
				_pfund_save_camp( $post, 'add' );
				break;
			case 'donate-campaign':
				_pfund_process_donate( $post );
				break;
			case 'donate-thanks':
				_pfund_display_thanks();
				break;
			case 'update-campaign':
				_pfund_save_camp( $post, 'update' );
				break;
			case 'user-login':
				_pfund_save_camp( $post, 'user-login' );
				break;
		}
		if( ! empty( $posts ) ) {
			$wp_query->is_home = false;
			$wp_query->queried_object = $posts[0];
			$wp_query->queried_object_id = $posts[0]->ID;
			$wp_query->is_page = true;
			$wp_query->is_singular = true;
		}
		$pfund_processed_action = true;
		$pfund_processing_action = false;
	}
	return $posts;
}

/**
 * For personal fundraising campaigns, if the user just updated a campaign
 * title, pull the new value from the request; otherwise just use the
 * saved title.
 * @param string $atitle The currently saved title.
 * @return string the title to display.
 */
function pfund_handle_title( $atitle ) {
	if ( ! pfund_is_pfund_post( ) ){
		return $atitle;
	}
	return pfund_get_value( $_REQUEST, 'pfund-camp-title', $atitle );
}

/**
 * Shortcode handler for pfund-progress-bar shortcode
 * @return string HTML markup for progress bar.
 */
function pfund_progress_bar() {
	global $post;
	if ( ! pfund_is_pfund_post() ){
		return '';
	}
	$postid = $post->ID;
	if ( _pfund_is_edit_new_campaign() ) {
		$postid = _pfund_get_new_campaign()->ID;
	}

	$options = get_option( 'pfund_options' );
	$goal = get_post_meta( $postid, '_pfund_gift-goal', true );
	$tally = get_post_meta( $postid, '_pfund_gift-tally', true );
	if ( $tally == '' ) {
		$tally = 0;
	}
	$remaining = ($goal - $tally);
	$funding_percentage = 1;
	if ( $remaining <= 0 ) {
		$funding_percentage = 1;
	} else if ( $tally < $goal ) {
		$funding_percentage = ($tally / $goal);
	}
	$goal_length = number_format((240 * $funding_percentage));
	$return_content = '<div class="pfund-progress-meter ">';
	$return_content .= '	<p class="pfund-progress-met">';
	$return_content .= '		<span class="pfund-amount"><sup>'.$options['currency_symbol'].'</sup>';
	$return_content .=				number_format( floatval( $tally ) );
	$return_content .= '		</span> '.__('Raised', 'pfund');
	$return_content .= '	</p>';
	$return_content .= '	<div class="pfund-progress-bar">';
	$return_content .= '		<div class="pfund-amount-raised" style="width:'.$goal_length.'px;"></div>';
	$return_content .= '	</div>';
	$return_content .= '	<p class="pfund-progress-goal">'.__('Goal:', 'pfund').' ';
	$return_content .= '		<span class="pfund-amount"><sup>'.$options['currency_symbol'].'</sup>';
	$return_content .=				number_format( floatval( $goal ) );
	$return_content .= '		</span>';
	$return_content .= '	</p>';
	$return_content .= '</div>';
	return $return_content;
}

/**
 * Send an email when a campaign receives a donation
 * @param array $transaction_array array detailing the donation with the
 * following keys:
 *   success -- boolean indicating if transaction was successful.
 *   amount -- Transaction amount
 *   donor_first_name -- Donor first name
 *   donor_last_name -- Donor last name
 *   donor_email -- Donor email
 *   anonymous -- boolean indicating if the gift was anonymous.
 *   error_code -- When an error occurs, one of the following values is returned:
 *		no_response_returned -- A response was not received from PayPal. 
 *		paypal_returned_failure -- PayPal returned a failure.
 *		wp_error -- A WP error was returned.
 *		exception_encountered -- An unexpected exception was encountered.
 *	 wp_error -- If the error_code is wp_error, the WP_Error object returned.
 *	 error_msg -- Text message describing error encountered.
 * @param mixed $post the post object containing the campaign.
 */
function pfund_send_donate_email( $transaction_array, $post ) {
	if ( apply_filters ('pfund_mail_on_donate', true, $transaction_array ) ) {
		$options = get_option( 'pfund_options' );
		$author_data = pfund_get_contact_info( $post, $options );
		$campaignUrl = get_permalink( $post );
		$trans_amount = number_format( floatval( $transaction_array['amount'] ) );
		if ( $options['mailchimp'] ) {
			$merge_vars = array(
				'NAME' => $author_data->display_name,
				'CAMP_TITLE' => $post->post_title,
				'CAMP_URL' => $campaignUrl,
				'DONATE_AMT' => $options['currency_symbol'].$trans_amount
			);			
			if ( isset( $transaction_array['anonymous'] ) &&
					$transaction_array['anonymous'] == true ) {
				$merge_vars['DONOR_ANON'] = 'true';
			} else {
				$merge_vars['DONOR_ANON'] = 'false';
				$merge_vars['DONOR_FNAM'] = $transaction_array['donor_first_name'];
				$merge_vars['DONOR_LNAM'] = $transaction_array['donor_last_name'];
				$merge_vars['DONOR_EMAL'] = $transaction_array['donor_email'];
			}
			pfund_send_mc_email($author_data->user_email, $merge_vars, $options['mc_email_donate_id']);
		} else {
			$pub_message = sprintf(__( 'Dear %s,', 'pfund' ), $author_data->display_name ).PHP_EOL;
			if ( isset( $transaction_array['anonymous'] ) &&
				$transaction_array['anonymous'] == true ) {
				$pub_message .= sprintf(__( 'An anonymous gift of %s%d has been received for your campaign, %s.', 'pfund' ),
						$options['currency_symbol'],
						$trans_amount,
						$post->post_title).PHP_EOL;
			} else {
				$pub_message .= sprintf(__( '%s %s donated %s%d to your campaign, %s.', 'pfund' ),
						$transaction_array['donor_first_name'],
						$transaction_array['donor_last_name'],
						$options['currency_symbol'],
						$trans_amount,
						$post->post_title).PHP_EOL;
				$pub_message .= sprintf(__( 'If you would like to thank %s, you can email %s at %s.', 'pfund' ),
						$transaction_array['donor_first_name'],
						$transaction_array['donor_first_name'],
						$transaction_array['donor_email']).PHP_EOL;
			}			
			$pub_message .= sprintf(__( 'You can view your campaign at: %s.', 'pfund' ), $campaignUrl ).PHP_EOL;
			wp_mail($author_data->user_email, __( 'A donation has been received', 'pfund' ) , $pub_message);
		}
	}
}

/**
 * Send an email when a campaign goal has been reached.
 * @param array $transaction_array array detailing the donation with the
 * following keys:
 *   success -- boolean indicating if transaction was successful.
 *   amount -- Transaction amount
 *   donor_first_name -- Donor first name
 *   donor_last_name -- Donor last name
 *   donor_email -- Donor email
 *   anonymous -- boolean indicating if the gift was anonymous.
 *   error_code -- When an error occurs, one of the following values is returned:
 *		no_response_returned -- A response was not received from PayPal.
 *		paypal_returned_failure -- PayPal returned a failure.
 *		wp_error -- A WP error was returned.
 *		exception_encountered -- An unexpected exception was encountered.
 *	 wp_error -- If the error_code is wp_error, the WP_Error object returned.
 *	 error_msg -- Text message describing error encountered.
 * @param mixed $post the post object containing the campaign.
 */
function pfund_send_goal_reached_email( $transaction_array, $post, $goal ) {
	if ( apply_filters ('pfund_mail_on_goal_reached', true, $transaction_array ) ) {
		$options = get_option( 'pfund_options' );
		$author_data = pfund_get_contact_info( $post, $options );
		$campaignUrl = get_permalink( $post );
		if ( $options['mailchimp'] ) {
			$merge_vars = array(
				'NAME' => $author_data->display_name,
				'CAMP_TITLE' => $post->post_title,
				'CAMP_URL' => $campaignUrl,
				'GOAL_AMT' => $options['currency_symbol'].number_format( floatval( $goal ) )
			);
			pfund_send_mc_email($author_data->user_email, $merge_vars, $options['mc_email_goal_id']);
		} else {
			$pub_message = sprintf(__( 'Dear %s,', 'pfund' ), $author_data->display_name).PHP_EOL;
		
			$pub_message .= sprintf(__( 'Congratulations!  Your campaign goal of %s has been met!', 'pfund' ),
					number_format( floatval( $goal ) ),
					$post->post_title ).PHP_EOL;
			$pub_message .= sprintf(__( 'You can view your campaign at: %s.', 'pfund' ), $campaignUrl ).PHP_EOL;
			wp_mail($author_data->user_email, __( 'Campaign goal met!', 'pfund' ) , $pub_message);
		}
	}
}

/**
 * Setup the short codes that personal fundraiser uses.
 */
function pfund_setup_shortcodes() {
	add_shortcode( 'pfund-campaign-list', 'pfund_campaign_list' );
	add_shortcode( 'pfund-campaign-permalink', 'pfund_campaign_permalink');
	add_shortcode( 'pfund-cause-list', 'pfund_cause_list' );
	add_shortcode( 'pfund-comments', 'pfund_comments' );
	add_shortcode( 'pfund-days-left', 'pfund_days_left' );
	add_shortcode( 'pfund-donate', 'pfund_donate_button' );
	add_shortcode( 'pfund-edit', 'pfund_edit' );
	add_shortcode( 'pfund-giver-list', 'pfund_giver_list' );
	add_shortcode( 'pfund-giver-tally', 'pfund_giver_tally' );
	add_shortcode( 'pfund-progress-bar', 'pfund_progress_bar' );
	add_shortcode( 'pfund-user-avatar', 'pfund_user_avatar' );
	$options = get_option( 'pfund_options' );
	if ( isset( $options['fields'] ) ) {
		foreach ( $options['fields'] as $field_id => $field ) {
			add_shortcode( 'pfund-'.$field_id, '_pfund_dynamic_shortcode' );
		}
	}
}

/**
 * Get the avatar for the user associated to this campaign.
 * @param array $attrs the attributes for the shortcode.  You can specify the
 * size of the avatar by passing a "size" attribute.
 * @return the avatar for the user associated to this campaign.
 */
function pfund_user_avatar( $attrs ){
	global $post;
	if( $post->ID == null || $post->post_type != 'pfund_campaign' ) {
		$user_email = '';
	} else {
		$options = get_option( 'pfund_options' );
		$contact_info = pfund_get_contact_info( $post, $options );
		$user_email = $contact_info->user_email;
	}
	if ( ! empty ( $attrs['size'] ) ) {
		$size = $attrs['size'];
	} else {
		$size = '';
	}
	return get_avatar( $user_email, $size );
}

/**
 * Display the list of current campaigns
 * @return array with 1 page/post that contains the campaign list.
 */
function _pfund_campaign_list_page() {
	$options = get_option( 'pfund_options' );
	$page = get_page( $options['campaign_root']);
	$page->post_title = __( 'Campaign List', 'pfund' );
	$page->post_content = pfund_campaign_list();
	return array( $page );

}

/**
 * Display the list of current causes
 * @return array with 1 page/post that contains the campaign list.
 */
function _pfund_cause_list_page() {
	$options = get_option( 'pfund_options' );
	$page = get_page( $options['cause_root']);
	$page->post_title = __( 'Cause List', 'pfund' );
	$page->post_content = pfund_cause_list();
	return array( $page );
}

/**
 * Create an HTML input field in the specified document.
 * @param DOMDocument $doc The HTML document to add the field to.
 * @param DOMNode $parent The parent to add the field to.
 * @param string $name The name of the input field.
 * @param string $value The value for the input field.
 * @param string $type The type of input field.  Defaults to hidden.
 * @return DOMNode The node representing the input field.
 */
function _pfund_create_input_node( $doc, $parent, $name, $value, $type = 'hidden' ) {
	$input_node = $doc->createElement('input');
	$input_node->setAttribute( 'type', $type );
	$input_node->setAttribute( 'name' , $name );
	$input_node->setAttribute( 'value' , $value );
	return $parent->appendChild( $input_node );
}

/**
 * Determines if current user can create campaigns or edit the current campaign.
 * @param array $pfund_options The current options for personal fundraiser.
 * If the value isn't passed in, the option is retrieved.
 * @return boolean true if user can create; otherwise false.
 */
function _pfund_current_user_can_create( $pfund_options = array() ) {
	global $pfund_current_user_can_create;

	if ( isset ($pfund_current_user_can_create) ) {
		return $pfund_current_user_can_create;
	} else {
		$pfund_current_user_can_create = false;
		if ( empty( $pfund_options ) ) {
			$pfund_options = get_option( 'pfund_options' );
		}
		if ( !is_user_logged_in() ) {
			$pfund_current_user_can_create = ! $pfund_options['login_required'];
		} else  {
			$pfund_current_user_can_create = _pfund_current_user_can_submit( $pfund_options );
		}
		return $pfund_current_user_can_create;
	}
}

/**
 * Determines if current user can submit campaigns
 * @param array $pfund_options The current options for personal fundraiser.
 * If the value isn't passed in, the option is retrieved.
 * @return boolean true if user can submit; otherwise false.
 */
function _pfund_current_user_can_submit( $pfund_options = array() ) {
	if ( empty( $pfund_options ) ) {
		$pfund_options = get_option( 'pfund_options' );
	}
	if ( ! empty ($pfund_options['submit_role']) ) {
		foreach ( $pfund_options['submit_role'] as $role ) {
			if ( current_user_can ( $role ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Determine what the status of the campaign should be set to.
 * @param string $current_status The current status of the campaign.
 * @return string the status to use.
 */
function _pfund_determine_campaign_status( $current_status = '') {
	$options = get_option( 'pfund_options' );
	if ($current_status == 'publish') {
		return $current_status ;
	}
	if ( _pfund_current_user_can_submit() ) {
		if ( $options['approval_required'] ) {
			return 'pending';
		} else {
			return 'publish';
		}
	} else {
		return 'draft';		
	}
}

/**
 * Displays a thank you message after a donation has been received.
 */
function _pfund_display_thanks() {
	global $pfund_update_message;
	$title = esc_attr__( 'Thanks for donating', 'pfund');
	$pfund_update_message = '<div id="pfund-update-dialog" style="display:none;" title="'.$title.'">';
	$pfund_update_message .= '<div>'.__( 'Thank you for your donation!', 'pfund' ).'</div></div>';
}

/**
 * Handler for the dynamic shortcodes created by personal fundraiser fields.
 * @param array $attrs the attributes for the shortcode
 * @param string $content the content between the shortcode begin and end tags.
 * @param string $tag the name of the shortcode.
 * @return string The data associated to the specified personal fundraiser field.
 */
function _pfund_dynamic_shortcode( $attrs, $content, $tag ) {
	global $post;
	if ( ! pfund_is_pfund_post() ){
		return '';
	}

	$postid = $post->ID;	
	if ( _pfund_is_edit_new_campaign() ) {
		$postid = _pfund_get_new_campaign()->ID;
	}

	$options = get_option( 'pfund_options' );
	$field_id = substr( $tag, 6 );
	$field = $options['fields'][$field_id];

	$data = get_post_meta( $postid, '_pfund_'.$field_id, true );
	$return_data = '';
	switch ( $field['type'] ) {
		case 'end_date':
		case 'date':
			if( ! empty( $data ) ) {
				$return_data = pfund_format_date( $data , $options['date_format'] );
			}
			break;
		case 'text':
		case 'textarea':
			$return_data = wpautop( make_clickable( $data ) );
			break;
		case 'image':
			if( empty( $data ) && isset( $attrs['default'] ) ) {
				$img_src = $attrs['default'];
			} else if ( ! empty( $data ) ) {
				$img_src = wp_get_attachment_url( $data );
			}
			if ( ! empty( $img_src ) ) {
				$return_data = '<img class="pfund-img" src="' .$img_src. '" />';
			}
			break;
		case 'user_goal':
		case 'gift_tally':
		case 'giver_tally':
			if ( empty ( $data ) ) {
				$data = '0';
			}
			$return_data = number_format( floatval( $data ) );
			break;
		default:
			$return_data = apply_filters( 'pfund_'.$field['type'].'_shortcode', $data );
	}
	if ( ! empty ( $attrs['esc_js'] ) && $attrs['esc_js'] == 'true' ) {
		$return_data = esc_js($return_data);
	}
	return $return_data;
}

/**
 * Convert php date format to jquery date format.
 * Derived from http://icodesnip.com/snippet/php/convert-php-date-style-dateformat-to-the-equivalent-jquery-ui-datepicker-string
 * @param string $date_format php date format to convert.
 * @return string corresponding jquery date format.
 */
function _pfund_get_jquery_date_fmt( $date_format ) {
    $php_patterns = array(
        //day
        '/d/',        //day of the month
        '/j/',        //day of the month with no leading zeros
        //month
        '/m/',        //numeric month leading zeros
        '/n/',        //numeric month no leading zeros
        //year
        '/Y/',        //full numeric year
        '/y/'     //numeric year: 2 digit
    );
    $jquery_formats = array(
        'dd','d',
        'mm','m',
        'yy','y'
    );
    return preg_replace($php_patterns, $jquery_formats, $date_format);
}

/**
 * When a new campaign has been created, get that campaign.
 * @return mixed the new campaign or null if the current campaign isn't a
 * new campaign.
 */
function _pfund_get_new_campaign() {
	global $pfund_new_campaign;
	
	$new_campaign_actions = array( 'update-campaign', 'user-login' );
	$action = pfund_get_value( $_REQUEST, 'pfund_action' );
	if ( ! isset( $pfund_new_campaign ) &&
			isset( $_REQUEST['pfund_campaign_id'] )
			&& in_array( $action, $new_campaign_actions ) ) {
		$campaign_id = $_REQUEST['pfund_campaign_id'];
		$campaign = get_post( $campaign_id );
		$referer_action = 'pfund-'.$action.$campaign_id;
		if( $action == 'user-login' ) {
			global $current_user;
			get_currentuserinfo();
			$save_user = $current_user;
			$current_user = new WP_User( 0 );
		}
		if ( wp_verify_nonce( $_REQUEST['n'], $referer_action ) ) {
			$pfund_new_campaign = $campaign;
		}
		if( $action == 'user-login' ) {
			$current_user = $save_user;
		}
	}
	return $pfund_new_campaign;	
}

/**
 * Generate HTML text input fields.
 * @param string $id DOM id for field.
 * @param string $label Label to display with text input field.
 * @param string $name Input field name.
 * @param string $class Class to apply to input field.
 * @param string $additional_content Additional content to display.
 * @return string The generated HTML.
 */
function _pfund_generate_input_field( $id, $label, $name, $class, $additional_content = '' ) {
	$input_field = '<li>';
	$input_field .= '	<label for="'.$id.'">'.$label;
	$input_field .= '		<abbr title="'.esc_attr__( 'required', 'pfund' ).'">*</abbr>';
	$input_field .= '	</label><br/>';
	$input_field .= '	<input id="'.$id.'" type="text" name="'.$name.'" class="'.$class.'" value=""/>';	
	if ( ! empty( $additional_content ) ) {
		$input_field .= $additional_content;
	}
	$input_field .= '</li>';
	return $input_field;
}

/**
 * Determine if a new campaign is being edited
 * @return boolean true if a new campaign is being edited; false otherwise.
 */
function _pfund_is_edit_new_campaign() {	
	global $pfund_new_campaign, $pfund_is_edit_new_campaign;
	if ( ! isset( $pfund_is_edit_new_campaign ) ) {
		_pfund_get_new_campaign();
		if ( isset( $pfund_new_campaign ) ){
			$pfund_is_edit_new_campaign = true;
		} else {
			$pfund_is_edit_new_campaign = false;
		}
	}
	return $pfund_is_edit_new_campaign ;
}
/**
 * Process a donation to a campaign.
 */
function _pfund_process_donate( $post ){
	//Handle various payment platforms.
	$confirmation_type = $_REQUEST['t'];
	switch( $confirmation_type ) {
		case 'pp':
			$transaction_array = pfund_process_paypal_pdt();
			break;
		case 'ipn':
			$transaction_array = pfund_process_paypal_ipn();
			break;
		default:
			$transaction_array = array();
			if (isset($_REQUEST['a'])) {
				$transaction_array['amount'] = $_REQUEST['a'];
				$transaction_array['success'] = true;
			}
	}

	if (isset($_REQUEST['n'])) {
		$transaction_array['transaction_nonce'] = $_REQUEST['n'];
	}

	//Allow integration of other transactions processing systems
	$transaction_array = apply_filters( 'pfund_transaction_array', $transaction_array );
	if ( ! empty( $transaction_array ) ) {
		do_action( 'pfund_add_gift', $transaction_array, $post );
	}
	
	//For IPN response exit since this is a server-side call.
	if ( $confirmation_type == 'ipn' ) {
		exit();
	}
	_pfund_display_thanks();
}

/**
 * Save the campaign
 * @param mixed $post the current cause or campaign to use to save the campaign.
 * @param string $update_type either 'add' or 'update.
 */
function _pfund_save_camp( $post, $update_type = 'add' ) {
	global $pfund_new_campaign, 
			$pfund_update_message,
			$pfund_is_edit_new_campaign;
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php');

	$options = get_option( 'pfund_options' );

	if ( ! _pfund_current_user_can_create( $options ) ) {
		return;
	}

	if ( $update_type == 'user-login' ) {
		$campaign_fields = array();
	} else {
		$campaign_fields = array(
			'post_name' => strip_tags( $_REQUEST['pfund-camp-location'] ),
			'post_title' => strip_tags( $_REQUEST['pfund-camp-title'] )
		);
	}

	if ( $update_type == 'update' || $update_type == 'user-login' ) {
		if ( $post->post_type == 'pfund_cause' ) {
			if ( _pfund_is_edit_new_campaign() ) {
				$campaign = _pfund_get_new_campaign();
				$campaign_id = $campaign->ID;
				$current_status = $campaign->post_status;
			} else {
				return;
			}
		} else {
			$campaign_id = $_REQUEST['pfund_campaign_id'];
			$campaign = get_post( $campaign_id );
			$current_status = $campaign->post_status;
		}
	} else {
		$current_status = '';
	}
	$status = _pfund_determine_campaign_status( $current_status );
	if ( $status != 'publish' ) {
		$campaign_fields['post_status'] = $status;
	}

	if ( $update_type == 'add' ) {
		$campaign_fields['post_type'] = 'pfund_campaign';
		$campaign_id = wp_insert_post( $campaign_fields );
		update_post_meta( $campaign_id, '_pfund_cause_id', $post->ID );
		$pfund_is_edit_new_campaign = true;
		$update_title = esc_attr__( 'Campaign added', 'pfund');
		if ( $status == 'publish' ) {
			$update_message = __( 'Your campaign has been created and is now available for public viewing at <a href="%s">%s</a>.', 'pfund' );
		} else if ( $status == 'pending' && is_user_logged_in() ) {
			$update_message = __( 'Your campaign has been created and submitted for approval.  Once your campaign has been approved you will receive an email notifying you.', 'pfund' );
		} else {
			$update_message = __( 'Your campaign has been created. %s','pfund' );
		}
	} else {
		$campaign_fields['ID'] = $campaign_id;
		if ( $status != 'publish' ) {
			$campaign_fields['post_status'] = $status;
		}
		wp_update_post( $campaign_fields );
		$update_title = esc_attr__( 'Campaign updated', 'pfund' );
		if ( $status == 'publish' ) {
			$update_message = __( 'Your campaign has been updated and is available for public viewing at <a href="%s">%s</a>.', 'pfund' );
		} else if ( $status == 'pending' && is_user_logged_in() ) {
			$update_message = __( 'Your campaign has been updated and submitted for approval.  Once your campaign has been approved you will receive an email notifying you.  Until then you can access your campaign at: <a href="%s">%s</a>', 'pfund' );
		} else {			
			$update_message = __( 'Your campaign has been updated.  %s', 'pfund' );			
		}
	}
	if ( $update_type != 'user-login' ) {
		pfund_save_campaign_fields( $campaign_id );
	}
	
	$additional_content = '';
	if ( $status == 'publish' ) {
		wp_publish_post( $campaign_id );		
		$camp_url = get_permalink( $campaign_id );
		$update_content = sprintf( $update_message, $camp_url, $camp_url );
	} else if ( $status == 'pending' && is_user_logged_in() ) {
		$preview_url = trailingslashit( get_option( 'siteurl' ) ).trailingslashit( $options['campaign_slug'] ).$campaign_id;
		$update_content = sprintf( $update_message, $preview_url, $preview_url );
	} else {
		$previewparms = array(
			'pfund_action' => 'user-login',
			'n' => 	wp_create_nonce( 'pfund-user-login'.$campaign_id ),
			'pfund_campaign_id' =>$campaign_id
		);
		$preview_url = get_permalink( $post->ID ) . '?'. http_build_query( $previewparms );
		$login_link = wp_login_url( $preview_url );
		if ( $options['allow_registration'] ) {
			$update_message = sprintf( $update_message,
					__( 'To make this campaign available for others to view, please <a id="pfund-login-link" href="%s">Login</a> or <a id="pfund-register-link" href="#">Register</a>.', 'pfund' ) );
			$additional_content = '<div id="pfund-register-dialog" style="display:none;" title="'.esc_attr__( 'Register','pfund' ).'">';
			$additional_content .= '<form name="pfund_create_account_form" id="pfund-create-account-form" action="'.PFUND_URL.'register-user.php" method="post">';
			$additional_content .= '<ul class="pfund-list">';
			$additional_content .= _pfund_generate_input_field( 
					'pfund-register-username',
					__( 'Username', 'pfund' ),
					'pfund_user_login',
					'validate[required,length[0,60]]' );
			$mask_password = '<div class="pfund-field-desc"><small><a id="pfund-mask-pass" href="#">'.__( 'Mask password', 'pfund' ).'</a></small></div>';
			$additional_content .= _pfund_generate_input_field( 
					'pfund-register-pass', __( 'Password', 'pfund' ),
					'pfund_user_pass',
					'validate[required,length[0,20]]',
					$mask_password );
			$additional_content .= _pfund_generate_input_field(
					'pfund-register-email',
					__( 'Email', 'pfund' ),
					'pfund_user_email',
					'validate[required,custom[email],length[0,100]]' );
			$additional_content .= _pfund_generate_input_field(
					'pfund-register-fname',
					__( 'First Name', 'pfund' ),
					'pfund_user_first_name',
					'validate[required,length[0,100]]' );
			$additional_content .= _pfund_generate_input_field( 
					'pfund-register-lname',
					__( 'Last Name', 'pfund' ),
					'pfund_user_last_name',
					'validate[required,length[0,100]]' );
			$additional_content .= '</form>';
			$additional_content .= '<form name="pfund_login_form" id="pfund-login-form" action="'.wp_login_url().'" method="post">';
			$additional_content .= '<input type="hidden" name="log" id="pfund-user-login">';
			$additional_content .= '<input type="hidden" name="pwd" id="pfund-user-pass">';
			$additional_content .= '<input type="hidden" name="redirect_to" value="'.$preview_url.'">';
			$additional_content .= '</ul">';
			$additional_content .= '</form>';
			$additional_content .= '</div>';
		} else {
			$update_message = sprintf( $update_message,
					__( 'To make this campaign available for others to view, please <a id="pfund-login-link" href="%s">Login</a>.', 'pfund' ) );
		}
		$update_content = sprintf( $update_message, $login_link );
	}

	$pfund_update_message = '<div id="pfund-update-dialog" style="display:none;" title="'.$update_title.'">';
	$pfund_update_message .= '<div>'.$update_content.'</div></div>';
	$pfund_update_message .= $additional_content;

	if ($pfund_is_edit_new_campaign) {
		$pfund_new_campaign = get_post( $campaign_id );
	}
}

/**
 * Update the total number of unique givers for this campaign.  A giver is
 * considered unique by email address.  All anonymous gifts are considered
 * unique givers.
 * @param <type> $campaign_id
 */
function _pfund_update_giver_tally( $campaign_id ) {
	$givers = get_post_meta( $campaign_id, '_pfund_givers', true );
	if ( empty( $givers )) {
		$givers = array();
	}
	$total_giver_tally = 0;
	$new_giver = false;
	$transactions = get_post_meta( $campaign_id, '_pfund_transactions' );
	foreach ( $transactions as $transaction ) {
		if ( isset( $transaction['anonymous'] ) &&
				$transaction['anonymous'] == true ) {
			$total_giver_tally++;
			$new_giver = true;
		} else if ( ! empty( $transaction['donor_email']) &&
				! array_key_exists( $transaction['donor_email'], $givers ) ) {
			$total_giver_tally++;
			$new_giver = true;
			$givers[$transaction['donor_email']] = array(
				'first_name' => $transaction['donor_first_name'],
				'last_name' => $transaction['donor_last_name'],
			);
		}
	}
	$giver_tally = get_post_meta( $campaign_id, '_pfund_giver-tally', true );
	if ( empty( $giver_tally ) ) {
		$giver_tally = $total_giver_tally;
	} else if ( $new_giver ) {
		$giver_tally++;
	}
	update_post_meta( $campaign_id, '_pfund_giver-tally', $giver_tally );
	update_post_meta( $campaign_id, '_pfund_givers', $givers );
}
?>