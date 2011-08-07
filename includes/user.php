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
 * Shortcode handler for pfund-campaign-list to display the list of current
 * campaigns.
 * @return string HTML that contains the campaign list.
 */
function pfund_campaign_list() {
	global $wp_query;

	wp_enqueue_style( 'pfund-user', PFUND_URL.'css/user.css', array(), PFUND_VERSION );
	$post_query = array(
		'post_type' => 'pfund_campaign',
		'orderby' => 'title',
		'order' => 'ASC',
		'posts_per_page' => -1
	);

	if ( array_key_exists( 'pfund_cause_id', $wp_query->query_vars ) ) {
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
 * Shortcode handler for pfund-cause-list to display the list of current causes.
 * @return string HTML that contains the campaign list.
 */
function pfund_cause_list() {
	wp_enqueue_style( 'pfund-user', PFUND_URL.'css/user.css', array(), PFUND_VERSION );
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
	wp_enqueue_script( 'pfund-user', PFUND_URL.'js/user.js', $script_reqs, PFUND_VERSION, true );
	wp_enqueue_style( 'pfund-user', PFUND_URL.'css/user.css', array(), PFUND_VERSION );

	$admin_email = get_option( 'admin_email' );
	wp_localize_script( 'pfund-user', 'pfund', array(
		'cancel_btn' => __( 'Cancel', 'pfund' ),
		'email_exists' => __( 'This email address is already registered', 'pfund' ),
		'invalid_email' =>__( 'Invalid email address', 'pfund' ),
		'mask_passwd' => __( 'Mask password', 'pfund' ),
		'ok_btn' => __( 'Ok', 'pfund' ),
		'register_btn' => __( 'Register', 'pfund' ),
		'register_fail' => sprintf( __( '<strong>ERROR</strong>: Couldn&#8217;t register you.  Please contact <a href="mailto:%s">us</a>.' ), $admin_email ),
		'reg_wait_msg' => __( 'Please wait while your registration is processed.', 'pfund' ),
		'save_warning' => __( 'Your campaign has not been saved.  If you would like to save your campaign, stay on this page, click on the Edit button and then click on the Ok button.', 'pfund' ),
		'unmask_passwd' => __( 'Unmask password', 'pfund' ),
		'username_exists' => __( 'This username is already registered', 'pfund' )
	) );

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
	if( $post->ID == null || $post->post_type != 'pfund_campaign' ) {
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
			$campaignId = $campaign->ID;
			$campaignTitle = $campaign->post_title;
		} else {
			$campaignTitle = $post->post_title;
		}
	} else {
		$editing_campaign = true;
		$campaignId = $post->ID;
		$campaignTitle = $post->post_title;
		$campaign = $post;
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
		$return_form .= '	<input id="pfund-campaign-id" type="hidden" name="pfund_campaign_id" value="'.$campaignId.'"/>';
		$return_form .= wp_nonce_field( 'pfund-update-campaign'.$campaignId, 'n', true , false );
	} else {
		$return_form .= '	<input type="hidden" name="pfund_action" value="create-campaign"/>';
		$return_form .= wp_nonce_field( 'pfund-create-campaign'.$post->ID, 'n', true , false );
	}
	$return_form .= pfund_render_fields( $campaignId, $campaignTitle, $editing_campaign );
	$return_form .= '</form>';
	$return_form .= '</div>';
	$return_form .= '<script type="text/javascript">';
	$validateSlug = array(
		'file' => PFUND_URL.'validate-slug.php',
		'alertTextLoad' => __( 'Please wait while we validate this location', 'pfund' ),
		'alertText' => __( '* This location is already taken', 'pfund' )
	);
	if ( $editing_campaign ) {
		$validateSlug['extraData'] = $campaignId;
	}
	$return_form .= 'jQuery(function($) {$.validationEngineLanguage.allRules.pfundSlug = '.json_encode($validateSlug).'});';
	$return_form .= '</script>';
	$return_form .= '<button class="pfund-edit-btn">'. __( 'Edit', 'pfund' ).'</button>';
	return $return_form;	
}

/**
 * Shortcode handler for pfund-giver-tally.  Returns the number of unique givers
 * for the current campaign.
 * @return string the number of unique givers.
 */
function pfund_giver_tally() {
	global $post;
	if( $post->ID == null || $post->post_type != 'pfund_campaign' ) {
		return '';
	} else {
		$tally = get_post_meta( $post->ID, '_pfund_giver-tally', true );
		if ( $tally == '' ) {
			$tally = '0';
		}
		return $tally;
	}
}

/**
 * Handler for actions performed on a cause.
 * @param mixed $posts The current posts provided by the_posts filter.
 * @return mixed $posts The current posts provided by the_posts filter.
 */
function pfund_handle_action( $posts ) {
	global $pfund_processed_action, $wp_query;
	$post = $posts[0];
	if ( array_key_exists( 'pfund_action', $wp_query->query_vars ) && ! $pfund_processed_action ) {
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
			case 'update-campaign':
				_pfund_save_camp( $post, 'update' );
				break;
			case 'user-login':
				_pfund_save_camp( $post, 'user-login' );
				break;
		}
		$pfund_processed_action = true;
		if( ! empty( $posts ) ) {
			$wp_query->is_home = false;
			$wp_query->queried_object = $posts[0];
			$wp_query->queried_object_id = $posts[0]->ID;
			$wp_query->is_page = true;
			$wp_query->is_singular = true;
		}
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
	if ( array_key_exists( 'pfund-camp-title', $_REQUEST ) ) {
		return $_REQUEST['pfund-camp-title'];
	} else {		
		return $atitle;
	}
}

/**
 * Shortcode handler for pfund-progress-bar shortcode
 * @param array $attrs Attributes array for the progress bar.  Allowable
 * attributes are:
 *   1) title -- title to display with progress bar
 *   2) funded_msg -- message to display once the goal has been met.
 * @param string $content Description of what this progress bar is for.
 * Displayed above the progress bar.
 * @return string HTML markup for progress bar.
 */
function pfund_progress_bar( $attrs, $content ) {
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
	$remaining = $goal - $tally;

	$return_content = '<div class="pfund-progress">';
	$return_content .='	<h3>'.do_shortcode( $attrs['title'] ).'</h3>';
	$return_content .='	<p class="pfund-progress-desc">'.do_shortcode( $content ).'</p>';

	$funding_percentage = 1;
	if ( $remaining <= 0 ) {
		$funding_percentage = 1;
	} else if ( $tally < $goal ) {
		$funding_percentage = $tally / $goal;
	}
	$goal_length = number_format( ( 240 * $funding_percentage ) -240 );
	$return_content .= '	<div id="progressBar" style="background-position:'.$goal_length.'px 0">';
	$return_content .= '		<span>&nbsp;</span>';
	$return_content .= '	</div>';
	if ( $remaining <= 0 ) {
		if ( array_key_exists( 'funded_msg', $attrs ) ) {
			$return_content .= '<h4>'.do_shortcode( $attrs['funded_msg'] ).'</h4>';
		}
	} else {
		$return_content .= '<div id="progressStat-Met"><span class="arrow"></span>';
		$return_content .= '<p>'.__( 'met', 'pfund' );
		$return_content .= '<span class="met-amount">'.$options['currency_symbol'].$tally.'</span></p></div>';
		$return_content .= '<div id="progressStat-Needed">';
		$return_content .= '<span class="arrow"></span>';
		$return_content .= '<p>'.__( 'needed', 'pfund' );
		$return_content .= '<span class="needed-amount">'.$options['currency_symbol'].$remaining.'</span></p></div>';
	}
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
	$author_data = get_userdata($post->post_author);
	$campaignUrl = get_permalink( $post );
	if ( apply_filters ('pfund_mail_on_donate', true, $transaction_array ) ) {
		$options = get_option( 'pfund_options' );
		if ( $options['mailchimp'] ) {
			$merge_vars = array(
				'FNAME' => $author_data->first_name,
				'LNAME' => $author_data->last_name,
				'CAMP_TITLE' => $post->post_title,
				'CAMP_URL' => $campaignUrl,
				'DONATE_AMT' => $options['currency_symbol'].$transaction_array['amount']
			);
			
			if ( $transaction_array['anonymous'] ) {
				$merge_vars['DONOR_ANON'] = 'true';
			} else {
				$merge_vars['DONOR_FNAME'] = $transaction_array['donor_first_name'];
				$merge_vars['DONOR_LNAME'] = $transaction_array['donor_last_name'];
				$merge_vars['DONOR_EMAIL'] = $transaction_array['donor_email'];
			}
			pfund_send_mc_email($author_data->user_email, $merge_vars, $options['mc_email_donate_id']);
		} else {
			$pub_message = sprintf(__( 'Dear %s,', 'pfund' ), $author_data->first_name).PHP_EOL;
			if ( $transaction_array['anonymous'] ) {
				$pub_message .= sprintf(__( 'An anonymous gift of %s%d has been received for your campaign, %s.', 'pfund' ),
						$options['currency_symbol'],
						$transaction_array['amount'],
						$post->post_title).PHP_EOL;
			} else {
				$pub_message .= sprintf(__( '%s %s donated %s%d to your campaign, %s.', 'pfund' ),
						$transaction_array['donor_first_name'],
						$transaction_array['donor_last_name'],
						$options['currency_symbol'],
						$transaction_array['amount'], 
						$post->post_title).PHP_EOL;
				$pub_message .= sprintf(__( 'If you would like to thank %s, you can email %s at %s.', 'pfund' ),
						$transaction_array['donor_first_name'],
						$transaction_array['donor_first_name'],
						$transaction_array['donor_email']).PHP_EOL;
			}			
			$pub_message .= sprintf(__( 'You can view your campaign at: %s.', 'pfund' ), $campaignUrl ).PHP_EOL;
			wp_mail($author_data->user_email, __( 'A donation has been received ', 'pfund' ) , $pub_message);
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
	$author_data = get_userdata($post->post_author);
	$campaignUrl = get_permalink( $post );
	if ( apply_filters ('pfund_mail_on_goal_reached', true, $transaction_array ) ) {
		$options = get_option( 'pfund_options' );
		if ( $options['mailchimp'] ) {
			$merge_vars = array(
				'FNAME' => $author_data->first_name,
				'LNAME' => $author_data->last_name,
				'CAMP_TITLE' => $post->post_title,
				'CAMP_URL' => $campaignUrl,
				'GOAL_AMT' => $goal
			);

			pfund_send_mc_email($author_data->user_email, $merge_vars, $options['pfund_mc_email_goal_id']);
		} else {
			$pub_message = sprintf(__( 'Dear %s,', 'pfund' ), $author_data->first_name).PHP_EOL;
		
			$pub_message .= sprintf(__( 'Congratulations!  Your campaign goal of %s has been met!', 'pfund' ),
					$goal,
					$post->post_title ).PHP_EOL;
			$pub_message .= sprintf(__( 'You can view your campaign at: %s.', 'pfund' ), $campaignUrl ).PHP_EOL;
			wp_mail($author_data->user_email, __( 'A donation has been received ', 'pfund' ) , $pub_message);
		}
	}
}

/**
 * Setup the short codes that personal fundraiser uses.
 */
function pfund_setup_shortcodes() {
	add_shortcode( 'pfund-campaign-list', 'pfund_campaign_list' );
	add_shortcode( 'pfund-cause-list', 'pfund_cause_list' );
	add_shortcode( 'pfund-comments', 'pfund_comments' );
	add_shortcode( 'pfund-donate', 'pfund_donate_button' );
	add_shortcode( 'pfund-edit', 'pfund_edit' );
	add_shortcode( 'pfund-giver-tally', 'pfund_giver_tally' );
	add_shortcode( 'pfund-progress-bar', 'pfund_progress_bar' );
	$options = get_option( 'pfund_options' );
	if ( array_key_exists( 'fields',  $options ) ) {
		foreach ( $options['fields'] as $field_id => $field ) {
			add_shortcode( 'pfund-'.$field_id, '_pfund_dynamic_shortcode' );
		}
	}
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
	if( empty( $data ) ) {
		return '';
	}
	switch ( $field['type'] ) {
		case 'date':
			if( ! empty( $options['date_format'] ) ) {
				return date( $options['date_format'], strtotime( $data ) );
			} else {
				return $data;
			}
		case 'text':
		case 'textarea':
			return wpautop( $data );
		case 'image':
			return '<img class="pfund-img" src="' . wp_get_attachment_url( $data ) . '" />';
		default:
			return $data;
	}
}

/**
 * When a new campaign has been created, get that campaign.
 * @return mixed the new campaign or null if the current campaign isn't a
 * new campaign.
 */
function _pfund_get_new_campaign() {
	global $pfund_new_campaign;
	
	$new_campaign_actions = array( 'update-campaign', 'user-login' );
	$action = $_REQUEST['pfund_action'];
	if ( ! isset( $pfund_new_campaign ) &&
			array_key_exists( 'pfund_campaign_id', $_REQUEST )
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
	$transaction_nonce = $_REQUEST['n'];
	$processed_transactions = get_post_meta( $post->ID, '_pfund_transaction_ids');
	//Make sure this transaction hasn't already been processed.
	if ( is_array( $processed_transactions ) && in_array( $transaction_nonce, $processed_transactions ) ) {		
		return;
	} else if ( !is_array( $processed_transactions ) && $processed_transactions == $transaction_nonce) {
		return;
	}

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
			$transaction_array = array(
				'amount' => $_REQUEST['a']
			);
	}

	//Allow integration of other transactions processing systems
	$transaction_array = apply_filters( 'pfund-transaction-array', $transaction_array );

	if ( $transaction_array['success'] == true) {
		//Update gift tally
		$tally = get_post_meta( $post->ID, '_pfund_gift-tally', true );
		if ( $tally == '' ) {
			$tally = 0;
		}
		$tally += $transaction_array['amount'];
		update_post_meta( $post->ID, '_pfund_gift-tally', $tally );
		add_post_meta( $post->ID, '_pfund_transaction_ids', $transaction_nonce );
		$transaction_array['transaction_nonce'] = $transaction_nonce;
		add_post_meta( $post->ID, '_pfund_transactions', $transaction_array );
		_pfund_update_giver_tally( $post->ID );

		$options = get_option( 'pfund_options' );
		//Add comment for transaction.
		if ( $transaction_array['anonymous'] ) {
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
		wp_insert_comment( $commentdata );
		//Fire action for any additional processing.
		do_action( 'pfund-processed-transaction', $transaction_array, $post );
		$goal = get_post_meta( $post->ID, '_pfund_gift-goal', true );
		if ( $tally >= $goal ) {
			do_action('pfund-reached-user-goal', $transaction_array, $post, $goal );
		}
		//For IPN response exit since this is a server-side call.
		if ( $confirmation_type == 'ipn' ) {
			exit();
		}
	}
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
		$pfund_new_campaign = get_post( $campaign_id );
		$pfund_is_edit_new_campaign = true;
		$update_title = __( 'Campaign added', 'pfund');
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
		$update_title = __( 'Campaign updated', 'pfund' );
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
					__( 'To make this campaign available for others to view, please <a href="%s">Login</a> or <a id="pfund-register-link" href="#">Register</a>.', 'pfund' ) );
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
					__( 'To make this campaign available for others to view, please <a href="%s">Login</a>.', 'pfund' ) );
		}
		$update_content = sprintf( $update_message, $login_link );
	}

	$pfund_update_message = '<div id="pfund-update-dialog" style="display:none;" title="'.$update_title.'">';
	$pfund_update_message .= '<div>'.$update_content.'</div></div>';
	$pfund_update_message .= $additional_content;

}

/**
 * Update the total number of unique givers for this campaign.  A giver is
 * considered unique by email address.  All anonymous gifts are considered
 * unique givers.
 * @param <type> $campaign_id
 */
function _pfund_update_giver_tally( $campaign_id ) {
	$giver_emails = array();
	$giver_tally = 0;
	$transactions = get_post_meta( $campaign_id, '_pfund_transactions' );
	foreach ( $transactions as $transaction ) {
		if ( $transaction['anonymous'] ) {
			$giver_tally++;
		} else if ( ! in_array( $transaction_array['donor_email'], $giver_emails ) ) {
			$giver_tally++;
			$giver_emails[] = $transaction_array['donor_email'];
		}
	}
	update_post_meta( $campaign_id, '_pfund_giver-tally', $giver_tally );
}
?>