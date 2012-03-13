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

/**
 * Ajax call to process donation using Authorize.Net 
 */
function pfund_auth_net_donation() {	
    $campaign_id = $_POST['post_id'];
    $gentime = $_POST['g'];    
    $msg = array();
    if ( wp_verify_nonce( $_POST['n'],  'pfund-donate-campaign'.$campaign_id.$gentime ) ) {
        $post = get_post( $campaign_id );
        $transaction_array = pfund_process_authorize_net();            
        if ($transaction_array['success']) {
            pfund_add_gift( $transaction_array, $post ); 
            $msg['success'] = true;
        } else {
            $msg['success'] = false;
            $msg['error'] = $transaction_array['error_msg'];
        }	
    } else {
        $msg['success'] = false;
        $msg['error'] =  __( 'You are not permitted to perform this action.', 'pfund' );        
    }
	echo json_encode($msg);
	die();
}

/**
 * Convert the passed in date to iso8601 (YYYY-MM-DD) format.
 * @param string $date date to convert.
 * @param string $format current format of date.
 * @return string date in iso8601 format.
 */
function pfund_date_to_iso8601( $date, $format ) {
	if( class_exists( 'DateTime' ) && method_exists( 'DateTime', 'createFromFormat' ) ) {
		$date = DateTime::createFromFormat( $format, $date );
		if ( $date ) {
			return $date->format( 'Y-m-d' );
		} else {
			return "";
		}
	} else {
		$date_map = array(
			'y'=>'year',
			'Y'=>'year',
			'm'=>'month',
			'n'=>'month',
			'd'=>'day',
			'j'=>'day'
		);
		$date_array = array(
			'error_count' => 0,
			'errors' => array()
		);

		$format = preg_split( '//', $format, -1, PREG_SPLIT_NO_EMPTY );
		$date = preg_split( '//', $date, -1, PREG_SPLIT_NO_EMPTY );
		$format_frag = $format[0];
		$format_idx = 0;
		$error_msg = null;

		foreach ( $date as $idx => $date_frag ) {
			if ( ! ctype_digit( $date_frag ) ) {
				$format_idx++;
				if ( !isset( $format[$format_idx] ) ) {
					$error_msg = 'An unexpected separator was encountered';
				} else {
					$format_frag = $format[$format_idx];
					if ( $date_frag != $format_frag ) {
						$error_msg = 'An unexpected separator was encountered';
					} else {
						$format_idx++;
						if ( ! isset( $format[$format_idx] ) ) {
							$error_msg = 'An unexpected character was encountered';
						} else {
							$format_frag = $format[$format_idx];
						}
					}
				}
				if ( isset( $error_msg ) ) {
					$date_array['error_count']++;
					$date_array['errors'][$idx] = $error_msg;
					break;
				}
			} else {
				$date_key = $date_map[$format_frag];
				if ( !isset( $date_array[$date_key] ) ) {
					$date_array[$date_key] = $date_frag;
				} else {
					$date_array[$date_key] .= $date_frag;
				}
			}

		}
		if ( isset( $date_array['month'] ) && isset( $date_array['day'] )
				&&  isset( $date_array['year'] ) )  {
			$gmttime = gmmktime( 0, 0, 0, $date_array['month'], $date_array['day'], $date_array['year'] );
			return gmdate( 'Y-m-d', $gmttime );
		} else {
			return '';
		}
	}
}

/**
 * Determine the short code for a specific personal fundraiser field.
 * @param string $id The id of the field.
 * @param string $type The type of field.
 * @return string the corresponding shortcode.
 */
function pfund_determine_shortcode( $id, $type = '' ) {
	$scode = '[pfund-'.$id;
	if ( $type == 'fixed' ) {
		$scode .= ' value="?"';
	}
	$scode .= ']';
	return $scode;
}

/**
 * Determine and return the proper location of the specified file.  This
 * function allows the use of .dev files when debugging.
 * @param string $name The name of the file, not including directory and
 * extension.
 * @param string $type The type of file.  Valid values are js or css.
 * @return string the file location to use.
 */
function pfund_determine_file_location( $name, $type ) {
	$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '.dev' : '';
	return PFUND_URL."$type/$name$suffix.$type";
}

/**
 * If the option is set to use ssl for campaigns, redirect campaign pages to 
 * secure.
 */
function pfund_force_ssl_for_campaign_pages() {
	global $post;     
    if ( ! is_admin() && $post && $post->post_type == 'pfund_campaign' ) {
        $options = get_option( 'pfund_options' ); 
        if ( ! empty ( $options['use_ssl'] ) && ! is_ssl() ) {
            $ssl_redirect = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $ssl_redirect = apply_filters( 'pfund_ssl_campaign_location', $ssl_redirect );
            wp_redirect( $ssl_redirect, 301 );
	   		exit();
	   	}
	}
}

/**
 * Filter to campaigns to use content from the cause they where created from.
 * @param string $content The current post content
 * @return string The cause content if the post is a personal fundraiser
 * campaign; otherwise return the content unmodified.
 */
function pfund_handle_content( $content ) {
	global $post, $pfund_update_message;
	if( $post->ID == null || ! pfund_is_pfund_post() ) {
		return $content;
	} else if ( $post->post_type == 'pfund_campaign' ) {
		$causeid = get_post_meta( $post->ID, '_pfund_cause_id', true ) ;
		$cause = get_post( $causeid );
		return $cause->post_content.$pfund_update_message;
	} else if ( $post->post_type == 'pfund_cause' ) {
		return $post->post_content.$pfund_update_message;
	}
}

/**
 * Determine if current post is a personal fundraiser post type.
 * @param mixed $post_to_check the post to check.  If this is not passed, the
 * global $post object is used.
 * @param boolean $include_lists flag to indicate if the list post_types should
 * be checked as well.
 * @return boolean true if the current post is a personal fundraiser post type;
 * otherwise return false.
 */
function pfund_is_pfund_post( $post_to_check = false, $include_lists = false ) {
	if ( ! $post_to_check ) {
		global $post;
		$post_to_check = $post;
	}
	$pfund_post_types = array( 'pfund_cause', 'pfund_campaign' );
	if ( $include_lists ) {
		$pfund_post_types[] = 'pfund_cause_list';
		$pfund_post_types[] = 'pfund_campaign_list';
	}
	if( $post_to_check && $post_to_check->ID != null && in_array( $post_to_check->post_type, $pfund_post_types ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Process an Authorize.Net donation.
 * @return array with the following keys:
 *   success -- boolean indicating if transaction was successful.
 *   amount -- Transaction amount
 *   donor_first_name -- Donor first name
 *   donor_last_name -- Donor last name
 *   donor_email -- Donor email
 *   error_code -- When an error occurs, one of the following values is returned:
 *		no_response_returned -- A response was not received from PayPal.
 *		auth_net_failure -- PayPal returned a failure.
 *		wp_error -- A WP error was returned.
 *		exception_encountered -- An unexpected exception was encountered.
 *	 wp_error -- If the error_code is wp_error, the WP_Error object returned.
 *	 error_msg -- Text message describing error encountered.
 */
function pfund_process_authorize_net() {
	$return_array = array( 'success' => false );
	if ( ! (int)$_POST['cc_num'] || ! (int)$_POST['cc_amount'] || ! $_POST['cc_email'] || ! $_POST['cc_first_name']
		 || ! $_POST['cc_last_name'] || ! $_POST['cc_address'] || ! $_POST['cc_city'] || ! $_POST['cc_zip']) {
		if ( ! (int)$_POST['cc_num']) {
			$return_array['error_msg'] = __( 'Error: Please enter a valid Credit Card number.', 'pfund' );
		} elseif ( ! (int)$_POST['cc_amount']) {
			$return_array['error_msg'] = __( 'Error: Please enter a donation amount.', 'pfund' );
		} elseif ( ! $_POST['cc_email']) {
			$return_array['error_msg'] = __( 'Error: Please enter a valid email address.', 'pfund' );
		} elseif ( ! $_POST['cc_first_name']) {
			$return_array['error_msg'] = __( 'Error: Please enter your first name.', 'pfund' );
		} elseif ( ! $_POST['cc_last_name']) {
			$return_array['error_msg'] = __( 'Error: Please enter your last name.', 'pfund' );
		} elseif ( ! $_POST['cc_address']) {
			$return_array['error_msg'] = __( 'Error: Please enter your address.', 'pfund' );
		} elseif ( ! $_POST['cc_city']) {
			$return_array['error_msg'] = __( 'Error: Please enter your city.', 'pfund' );
		} elseif ( ! $_POST['cc_zip']) {
			$return_array['error_msg'] = __( 'Error: Please enter your zip code.', 'pfund' );
		}
		return $return_array;
	}
	
	//process Authorize.Net donation
	require('AuthnetAIM.class.php');
	 
	try {
		$pfund_options = get_option('pfund_options');
	    $email   = $_POST['cc_email'];
	    $product = ($pfund_options['authorize_net_product_name'] !='') ? $pfund_options['authorize_net_product_name'] : 'Donation';
	    $firstname = $_POST['cc_first_name'];
	    $lastname  = $_POST['cc_last_name'];
	    $address   = $_POST['cc_address'];
	    $city      = $_POST['cc_city'];
	    $state     = $_POST['cc_state'];
	    $zipcode   = $_POST['cc_zip'];
	 	    
	    $creditcard = $_POST['cc_num'];
	    $expiration = $_POST['cc_exp_month'] . '-' . $_POST['cc_exp_year'];
	    $total      = $_POST['cc_amount'];
	    $cvv        = $_POST['cc_cvv2'];
	    $invoice    = substr(time(), 0, 6);	    
	    
	    
	    $api_login = $pfund_options['authorize_net_api_login_id'];
	    $transaction_key = $pfund_options['authorize_net_transaction_key']; 
	 
	    $payment = new AuthnetAIM( $api_login, $transaction_key, ( $pfund_options['authorize_net_test_mode']==1 ) ? true : false );

	    $payment->setTransaction($creditcard, $expiration, $total, $cvv, $invoice);
	    $payment->setParameter("x_duplicate_window", 180);
	    $payment->setParameter("x_customer_ip", $_SERVER['REMOTE_ADDR']);
	    $payment->setParameter("x_email", $email);
	    $payment->setParameter("x_email_customer", FALSE);
	    $payment->setParameter("x_first_name", $firstname);
	    $payment->setParameter("x_last_name", $lastname);
	    $payment->setParameter("x_address", $address);
	    $payment->setParameter("x_city", $city);
	    $payment->setParameter("x_state", $state);
	    $payment->setParameter("x_zip", $zipcode);
	    $payment->setParameter("x_description", $product);

	    $payment->process();
	 
	    if ($payment->isApproved())  {
			// if success, return array
			$return_array['amount'] = $total;
			$return_array['donor_email'] = $email;

			if ( isset( $_POST['anonymous'] ) && $_POST['anonymous']==1) {
				$return_array['anonymous'] = true;
			} else {
				$return_array['donor_first_name'] = $firstname;
				$return_array['donor_last_name'] = $lastname;
			}
			$return_array['transaction_nonce'] = $_POST['n'];
			$return_array['success'] = true;

	    } else if ($payment->isDeclined()) {
	        // Get reason for the decline from the bank. This always says,
	        // "This credit card has been declined". Not very useful.
	        $reason = $payment->getResponseText();	 
	        $return_array['error_msg'] = __( 'This credit card has been declined.  Please use another form of payment.', 'pfund' );
	    } else if ($payment->isError()) {	 
	        // Capture a detailed error message. No need to refer to the manual
	        // with this one as it tells you everything the manual does.
	        $return_array['error_msg'] =  $payment->getResponseMessage();
	 
	        // We can tell what kind of error it is and handle it appropriately.
	        if ($payment->isConfigError()) {
	            // We misconfigured something on our end.
	            //$return_array['error_msg'] .= " Please notify the webmaster of this error.";
	        } else if ($payment->isTempError()) {
	            // Some kind of temporary error on Authorize.Net's end. 
	            // It should work properly "soon".
	            $return_array['error_msg'] .= __( '  Please try your donation again.', 'pfund' );
	        } else {
	            // All other errors.
	        }
	 
	    }
	} catch (AuthnetAIMException $e) {
	    $return_array['error_msg'] = sprintf( __( 'There was an error processing the transaction. Here is the error message: %s', 'pfund' ),  $e->__toString() );
	}
	return $return_array;
}

/**
 * Render the input fields for the personal fundraising fields.
 * @param int $postid The id of the campaign that is being edited.
 * @param string $campaign_title The title of the campaign being edited.
 * @param boolean $editing_campaign true if campaign is being edited;false if new campaign.
 * Defaults to true.
 * @param string $default_goal default goal for campaign.  Defaults to empty.
 * @return string The HTML for the input fields.
 */
function pfund_render_fields( $postid, $campaign_title, $editing_campaign = true, $default_goal = '' ) {
	global $current_user, $post;
	$options = get_option( 'pfund_options' );
	$inputfields = array();
	$matches = array();
	$result = preg_match_all( '/'.get_shortcode_regex().'/s', pfund_handle_content( $post->post_content ), $matches );
	$tags = $matches[2];
	$attrs = $matches[3];
	if ( is_admin() ) {
		$render_type = 'admin';
		if ( isset( $options['fields'] ) ) {
			foreach ( $options['fields'] as $field_id => $field ) {
				$field_value = get_post_meta( $postid, '_pfund_'.$field_id, true );
				$inputfields['pfund-'.$field_id] = array(
					'field' => $field,
					'value' => $field_value
				);
			}
			$content_idx = array_search('pfund-'.$field_id, $tags);
			if ( $content_idx !== false ){
				$inputfields['pfund-'.$field_id]['attrs'] = $attrs[$content_idx];
			}
		}
		$content = '';
	} else {
		$render_type = 'user';
		get_currentuserinfo();
		$inputfields = array();
		foreach( $tags as $idx => $tag ) {
			if ( $tag == 'pfund-days-left' ) {
				$tag = 'pfund-end-date';
			}
			$field_id = substr( $tag, 6 );			
			$field_value = get_post_meta( $postid, '_pfund_'.$field_id, true );
			if ( isset( $options['fields'][$field_id] ) ) {
				$inputfields[$tag] = array(
					'field' => $options['fields'][$field_id],
					'attrs' => $attrs[$idx],
					'value' => $field_value
				);
			}
		}
		$content = '<ul class="pfund-list">';
	}

	if ( ! isset( $inputfields['pfund-camp-title'] ) ) {
		$inputfields['pfund-camp-title'] = array(
			'field' => $options['fields']['camp-title'],
			'value' => $campaign_title
		);
	}

	if ( ! isset( $inputfields['pfund-camp-location'] ) ) {
		$inputfields['pfund-camp-location'] = array(
			'field' => $options['fields']['camp-location']
		);
	}

	if ( ! isset( $inputfields['pfund-gift-goal'] ) ) {
		$current_goal = get_post_meta( $postid, '_pfund_gift-goal', true );
		$inputfields['pfund-gift-goal'] = array(
			'field' => $options['fields']['gift-goal'],
			'value' => $current_goal
		);
	}
	if ( empty( $inputfields['pfund-gift-goal']['value'] ) ) {
		$inputfields['pfund-gift-goal']['value'] = $default_goal;
	}

	if ( ! isset( $inputfields['pfund-gift-tally'] ) ) {
		$current_tally = get_post_meta( $postid, '_pfund_gift-tally', true );
		$inputfields['pfund-gift-tally'] = array(
			'field' => $options['fields']['gift-tally'],
			'value' => $current_tally
		);
	}

	uasort( $inputfields, '_pfund_sort_fields' );
	$hidden_inputs = '';
	$field_idx = 0;
	foreach( $inputfields as $tag => $field_data ) {
		$field = $field_data['field'];
		$value = pfund_get_value( $field_data, 'value' );

		$field_options = array(			
			'name' => $tag,
			'desc' => pfund_get_value( $field, 'desc' ),
			'label' => pfund_get_value( $field, 'label' ),
			'value' => $value,
			'render_type' => $render_type,
			'field_count' => $field_idx,
			'required' => pfund_get_value( $field, 'required', false )
		);
		if ( isset( $field_data['attrs'] ) ) {
			$field_options['attrs']= shortcode_parse_atts( $field_data['attrs'] );
		}
		switch ( $field['type'] ) {
			case 'camp_title':
				if ( ! is_admin() ) {
					$field_options['value'] = $campaign_title;
					$content .= _pfund_render_text_field( $field_options );					
					$field_idx++;
				}
				break;
			case 'camp_location':
				if ( ! is_admin() ) {
					if ( $editing_campaign ) {
						require_once( ABSPATH . 'wp-admin/includes/post.php' );
						list( $permalink, $post_name ) = get_sample_permalink( $postid );
					} else {
						$post_name = '';
					}
					$field_options['custom_validation'] = 'ajax[pfundSlug]';
					$field_options['value'] = $post_name;
					$field_options['pre_input'] = trailingslashit( get_option( 'siteurl' ) ).trailingslashit( $options['campaign_slug'] );					
					$content .= _pfund_render_text_field( $field_options );
					$field_idx++;
				}
				break;
			case 'fixed':
			case 'gift_tally':
				if ( is_admin() ) {
					$content .= _pfund_render_text_field( $field_options );
				} else if ( $field['type'] == 'fixed' ) {
					$attr = shortcode_parse_atts( $field_data['attrs'] );
					$hidden_inputs .= '	<input type="hidden" name="'.$tag.'" value="'.$attr["value"].'"/>';
				}
				break;
			case 'end_date':
			case 'date':
				$field_options['class'] = 'pfund-date';
				$field_options['value'] = pfund_format_date( 
						$field_options['value'],  
						$options['date_format']
				);
				$content .= _pfund_render_text_field( $field_options );
				$field_idx++;
				break;
			case 'giver_tally':
				if ( is_admin() ) {
					$content .= _pfund_render_text_field( $field_options );
					$field_idx++;					
				}
				break;
			case 'user_goal':
				$field_options['custom_validation'] = 'custom[onlyNumber]';
				$content .= _pfund_render_text_field( $field_options );
				$field_idx++;
				break;
			case 'text':
				$content .= _pfund_render_text_field( $field_options );
				$field_idx++;
				break;
			case 'textarea':
				$field_content = '<textarea class="pfund-textarea" id="'.$tag.'" name="'.$tag.'" rows="10" cols="50">'.$value.'</textarea>';
				$content .= pfund_render_field_list_item( $field_content, $field_options);
				$field_idx++;
				break;
			case 'image':
				$content .= _pfund_render_image_field( $field_options );
				$field_idx++;
				break;
			case 'select':
				$field_content = pfund_render_select_field( $field['data'], $tag, $value );
				$content .= pfund_render_field_list_item( $field_content, $field_options );
				$field_idx++;
				break;
			case 'user_email':
				if ( empty ( $value ) && !is_admin() ) {
					$value = $current_user->user_email;
					$field_options['value'] = $value;
				}
				$field_options['custom_validation'] = 'custom[email]';
				$content .= _pfund_render_text_field( $field_options );
				$field_idx++;
				break;
			case 'user_displayname':
				if ( empty ($value) && !is_admin() ) {
					$value = $current_user->display_name;
					$field_options['value'] = $value;
				}				
				$content .= _pfund_render_text_field( $field_options );
				$field_idx++;
				break;
			default:
				$content .= apply_filters( 'pfund_'.$field['type'].'_input', $field_options );
				$field_idx++;
		}
	}
	if ( ! is_admin() ) {
		$content .= '</ul>';
	}
	$content .= $hidden_inputs;
	return $content;

}

/**
 * Render a drop down
 * @param string $values newline delimited values for the dropdown
 * @param string $name name of drop down
 * @param string $currentValue the value in the drop down that should be
 * selected.
 * @return string The HTML for the dropdown.
 */
function pfund_render_select_field( $values, $name = '', $currentValue = '' ) {
	$values = preg_split( "/[\n]+/", $values );
	$content = '<select name="'.$name.'" value="'.$name.'>';
	foreach( $values as $value ) {
		$content .= '<option value="' . trim( $value ) . '"'.selected( $currentValue, $value, false ).'>'.$value.'</option>';
	}
	$content .= '</select>';
	return $content;
}

/**
 * Use the map_meta_cap filter to limit editing of campaigns to owners or users
 * who can edit others posts.
 * @param array $caps The current capabilities.
 * @param string $cap Capability name.
 * @param int $user_id User ID.
 * @param mixed $args Additional arguments passed.
 * @return array  Actual capabilities for meta capability.
 */
function pfund_restrict_edit( $caps, $cap, $user_id, $args ) {
	global $current_user;
	if ( $cap == 'edit_campaign' ) {
		$post = get_post( $args[0] );
		$post_type = get_post_type_object( $post_type->cap->edit_others_posts );
		if ( $post->post_author == $current_user->ID ) {
			return $caps;
		} else {
			return array('edit_others_posts');
		}
	}
	return $caps;
}

/**
 * Save the personal fundraising fields for the specified campaign.
 * @param string $campid The id of the campaign to save the personal fundraising
 * fields to.
 */
function pfund_save_campaign_fields( $campid ) {
	$options = get_option( 'pfund_options' );	
	if ( isset( $options['fields'] ) ) {
		$fieldname = '';
		foreach ( $options['fields'] as $field_id => $field ) {
			$fieldname = 'pfund-'.$field_id;			
			switch( $field['type'] ) {
				case 'end_date':
				case 'date':
					if ( isset( $_REQUEST[$fieldname] ) ) {
						$date_format = pfund_get_value( $options, 'date_format', 'm/d/y' );
						if ( isset( $_REQUEST[$fieldname] ) && empty( $_REQUEST[$fieldname] ) ) {
							$date_to_save = $_REQUEST[$fieldname];
						} else {
							$date_to_save = pfund_date_to_iso8601( $_REQUEST[$fieldname] , $date_format );
						}
						update_post_meta( $campid, "_pfund_".$field_id, $date_to_save );
					}
				case 'image':
					 _pfund_attach_uploaded_image( $fieldname, $campid, "_pfund_".$field_id );
					break;
				case 'user_goal':
				case 'gift_tally':
					if ( isset( $_REQUEST[$fieldname] ) ) {
						update_post_meta( $campid, "_pfund_".$field_id, absint( $_REQUEST[$fieldname] ) );
					}
					break;
				default:
					if ( isset( $_REQUEST[$fieldname] ) ) {
						if ( is_array( $_REQUEST[$fieldname] ) ) {
							$value_to_save = $_REQUEST[$fieldname];
						} else {
							$value_to_save = strip_tags( $_REQUEST[$fieldname] );
						}
						update_post_meta( $campid, "_pfund_".$field_id, $value_to_save );
					}
					break;
			}
		}
	}
}


/**
 * Send a mailchimp transactional email
 * @param string $email the email address to send to.
 * @param array $merge_vars An array of the email merge variables.
 * @param string $campId the campaign id to use to send the email.
 * @param string $listId  (optional) the list id to use to send the email.  If
 * this value is not provided the campaign id will be used to determine the list
 * id.
 * @param string $email_type (optional) The type of email to send (text or
 * html).  If this value isn't passed, the email will be sent as html.
 * @return boolean flag indicating if send was successful.
 */
function pfund_send_mc_email($email, $merge_vars, $campId, $listId='', $email_type='html') {
	$options = get_option( 'pfund_options' );

	if ( ! class_exists ( 'MCAPI_PFund' ) ) {
		require_once( PFUND_DIR . '/includes/MCAPI.class.php' );
	} 
    $api = new MCAPI_PFund($options['mc_api_key']);

    if ($listId == '') {
		//Retrieve the list id using the campaign id
		$opts = array('campaign_id' => $campId);
		$retval = $api->campaigns($opts);
		if ($api->errorCode){
			error_log("Unable to retrieve specified campaign: $campId to determine list id.  Code=".$api->errorCode." Msg=".$api->errorMessage);
			return false;
		} else {
			$listId = $retval['data'][0]['list_id'];
		}
    }

    $retval = $api->listSubscribe($listId, $email, $merge_vars,$email_type,false, true, false);

    if ($api->errorCode || $retval!="1"){
        error_log("Unable to subscribe the email address: .$email to the specified list: $listId.  Error Code=".$api->errorCode." Msg=".$api->errorMessage);
        return false;
    }

    $retval = $api->campaignSendNow($campId);
    if ($api->errorCode){
        error_log("Unable to send campaign id $campId to the email: $email.  Errror Code=".$api->errorCode." Msg=".$api->errorMessage);
        return false;
    }

    $retval = $api->listUnsubscribe($listId,$email,false,false,false);
    if ($api->errorCode || $retval!="1"){
        error_log("Unable to unsubscribe the email: $email from the specified list: $listId.  Error Code=".$api->errorCode." Msg=".$api->errorMessage);
        return false;
    } else {
        return true;
    }
}

/**
 * Add the specified image file upload to the specified post
 * @param string $fieldname Name of the file in the request.
 * @param string $postid The id of the post to attach the file to.
 * @param string $metaname The name of the metadata field to store the
 * attachment in.
 */
function _pfund_attach_uploaded_image( $fieldname, $postid, $metaname ) {
	if( isset( $_FILES[$fieldname] ) && is_uploaded_file( $_FILES[$fieldname]['tmp_name'] ) ) {
		$data = media_handle_upload( $fieldname, $postid, array( 'post_status' => 'private' ) );
		if( is_wp_error( $data ) ) {
			error_log("error adding image for personal fundraising:".print_r( $data, true ) );
		} else {
			update_post_meta( $postid, $metaname, $data );
		}
	}
}

/**
 * Format the specified date with the specified format.
 * @param string $date either an iso8601 (YYYY-MM-DD) formatted date or a
 * mm/dd/yy date.
 * @param string $format the format to return the date in.
 * @return string the formatted date.
 */
function pfund_format_date( $date, $format ) {
	if ( empty($date) ) {
		return $date;
	}
	//Date is stored in old format of m/d/y
	if ( strlen( $date ) == 8 ) {
		$date = pfund_date_to_iso8601( $date, 'm/d/y' );
	}
	return gmdate( $format, strtotime( $date ) );
}

/**
 * Determine the proper contact information for the specified campaign.  If the
 * campaign has a user display name and user email field, use those values instead
 * of the post author's contact information.  This function is necessary for use
 * cases where the campaign is created by an administrator, but the notifications
 * should be sent to another contact.
 * @param <mixed> $post The post representing the campaign to get the contact
 * information for
 * @param <mixed> $options The current personal fundraiser options.
 * @return <mixed> a WP_User object containing the contact information for
 * the specified campaign.
 */
function pfund_get_contact_info( $post, $options = array() ) {
	$metavalues = get_post_custom( $post->ID );
	$contact_email = '';
	$contact_name = '';
	foreach( $metavalues as $metakey => $metavalue ) {
		if ( strpos( $metakey, "_pfund_" ) === 0 ) {
			$field_id = substr( $metakey , 7);
			if ( isset($options['fields'][$field_id]) ) {
				$field_info = $options['fields'][$field_id];
				if ( ! empty( $field_info )  && ! empty( $metavalue[0] ) ) {
					switch( $field_info['type'] ) {
						case 'user_email':
							$contact_email = $metavalue[0];
							break;
						case 'user_displayname':
							$contact_name = $metavalue[0];
							break;
					}
					if ( ! empty( $contact_email ) && ! empty( $contact_name ) ) {
						break;
					}
				}
			}
		}
	}
	$contact_data = clone get_userdata($post->post_author);
	if ( $contact_data->user_email != $contact_email ) {
		$contact_data->user_email = $contact_email;
		$contact_data->display_name = $contact_name;
		$contact_data->ID = -1;
	}
	return $contact_data;
}

/**
 * Utility function to get value from array.  If the value doesn't exist,
 * return the specified default value.
 * @param array $array The array to pull the value from.
 * @param string $key The array key to use to get the value.
 * @param mixed $default The optional default to use if the key doesn't exist.
 * This value defaults to an empty string.
 * @return mixed The specified value from the array or the default if it doesn't
 * exist
 */
function pfund_get_value( $array, $key, $default = '' ) {
	if ( isset( $array[$key] ) ) {
		return $array[$key];
	} else {
		return $default;
	}
}

/**
 * Render the field using the specified render type.
 * @param string $field_contents actual input field to render.*
 * @param array $field_options named options for field.
 * @return string the rendered HTML.
 */
function pfund_render_field_list_item( $field_contents, $field_options ) {
	$content = '<li>';
	$content .= '	<label for="'.$field_options['name'].'">'.$field_options['label'];
	if ( isset( $field_options['required'] ) && $field_options['required'] ) {
		$content .= '<abbr title="'.esc_attr__( 'required', 'pfund' ).'">*</abbr>';
	}
	$content .= '</label>';
	$content .= $field_contents;
	if ( isset( $field_options['render_type'] ) &&  
			$field_options['render_type'] == 'user' &&
			! empty( $field_options['desc'] ) ) {
		$content .= '<div class="pfund-field-desc"><em><small>'.$field_options['desc'].'</small></em></div>';
	}
	$content .= '</li>';
	return $content;
}

/**
 * Render an image input field, including a display of the current image.
 * @param array $field_options named options for field.  Keys are:
 *	--name name of the field
 *	--label label to display with field.
 *	--value link to current image.
 * @return string HTML markup for image file upload/display.
 */
function _pfund_render_image_field( $field_options ) {
	if ( ! empty ( $field_options['value'] ) ) {
		$field_options['additional_content'] = '<img class="pfund-image" width="184" src="'.wp_get_attachment_url( $field_options['value'] ).'">';
	}
	$field_options['class'] = 'pfund-image';
	$field_options['type'] = 'file';
	return _pfund_render_text_field( $field_options );
	
}

/**
 * Render the HTML for a text input field
 * @param array $field_options named options for field.  Keys are:
 *	--name the name/id of the text field
 *	--label The label to display next to the input field.
 *	--class  The class name for the input field.
 *	--value The value for the input field.
 *	--type The type of input field.  Defaults to text.
 *	--additional_content Additional HTML to display.
 * @return string The HTML of a text input field.
 */
function _pfund_render_text_field( $field_options = '') {
	$defaults = array(
		'class' => 'pfund-text',
		'type' => 'text',
		'value' => '',
	);
	$field_options = array_merge( $defaults, $field_options );
	if ( ( isset( $field_options['required'] ) && $field_options['required'] ) ||
			isset( $field_options['custom_validation'] ) ) {
		$field_options['class'] .= ' validate[';
		if ( $field_options['required'] ) {
			$field_options['class'] .= 'required';
			if ( isset( $field_options['custom_validation'] ) ) {
				$field_options['class'] .= ',';
			}
		}
		if ( isset( $field_options['custom_validation'] ) ) {
			$field_options['class'] .=  $field_options['custom_validation'];
		}
		$field_options['class'] .= ']';
	}
	$content = '';
	if ( isset( $field_options['pre_input'] ) ) {
		$content .= $field_options['pre_input'];
	}
	$content .= '	<input class="'.$field_options['class'].'" id="'.$field_options['name'].'"';
	$content .= '		type="'.$field_options['type'].'" name="'.$field_options['name'].'"';
	if ( $field_options['type'] != 'file' ) {
		$content .= ' value="'.esc_attr( $field_options['value'] ).'"';
	}
	$content .= '/>';
	if ( isset( $field_options['additional_content'] ) ) {
		$content .= $field_options['additional_content'];
	}
	return pfund_render_field_list_item( $content, $field_options );
}

/**
 * Sort the specified fields using the fields sortorder.
 * @param mixed $field the original field
 * @param mixed $compare_field the field to compare.
 * @return int indicating if fields are equal, greater than or less than one
 * another.
 */
function _pfund_sort_fields( $field, $compare_field ) {
	$field_order = $field['field']['sortorder'];
	$compare_order = $compare_field['field']['sortorder'];

	if($field_order == $compare_order) {
		return 0;
	} else {
		return ( $field_order < $compare_order ) ? -1 : 1;
	}
	
}
?>
