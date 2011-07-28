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
		return $content.$pfund_update_message;
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
	if( $post_to_check->ID != null && in_array( $post_to_check->post_type, $pfund_post_types ) ) {
		return true;
	} else {
		return false;
	}
}
/**
 * Render the input fields for the personal fundraising fields.
 * @param int $postid The id of the campaign that is being edited.
 * @param string $campaign_title The title of the campaign being edited.
 * @param boolean $editing_campaign true if campaign is being edited;false if new campaign.
 * Defaults to true.
 * @return string The HTML for the input fields.
 */
function pfund_render_fields( $postid, $campaign_title, $editing_campaign = true ) {
	global $current_user, $post;

	$metavalues = array();
	if ( isset(  $postid ) ) {
		$metavalues = get_post_custom( $postid );
	}

	$options = get_option( 'pfund_options' );
	$inputfields = array();
	if ( is_admin() ) {
		$render_type = 'admin';
		if ( array_key_exists( 'fields',  $options ) ) {
			foreach ( $options['fields'] as $field_id => $field ) {
				$inputfields['pfund-'.$field_id] = array(
					'field' => $field,
					'value' => $metavalues['_pfund_'.$field_id][0]
				);
			}
		}
		$content = '';
	} else {
		$render_type = 'user';
		get_currentuserinfo();
		$matches = array();
		$result = preg_match_all( '/'.get_shortcode_regex().'/s', pfund_handle_content( $post->post_content ), $matches );
		$tags = $matches[2];
		$attrs = $matches[3];
		$inputfields = array();
		foreach( $tags as $idx => $tag ) {
			$field_id = substr( $tag, 6 );
			if ( array_key_exists( $field_id, $options['fields'] ) ) {				
				$inputfields[$tag] = array(
					'field' => $options['fields'][$field_id],
					'attrs' => $attrs[$idx],
					'value' => $metavalues['_pfund_'.$field_id][0]
				);
			}
		}
		$content = '<ul class="pfund-list">';
	}

	if ( ! array_key_exists( 'pfund-camp-title', $inputfields ) ) {
		$inputfields['pfund-camp-title'] = array(
			'field' => $options['fields']['camp-title'],
			'value' => $campaign_title
		);
	}

	if ( ! array_key_exists( 'pfund-camp-location', $inputfields ) ) {
		$inputfields['pfund-camp-location'] = array(
			'field' => $options['fields']['camp-location']
		);
	}

	if ( ! array_key_exists( 'pfund-gift-goal', $inputfields ) ) {
		$inputfields['pfund-gift-goal'] = array(
			'field' => $options['fields']['gift-goal'],
			'value' => $metavalues['_pfund_gift-goal'][0]
		);
	}

	uasort($inputfields, _pfund_sort_fields);
	$hidden_inputs = '';
	$field_idx = 0;
	foreach( $inputfields as $tag => $field_data ) {
		$field = $field_data['field'];
		$value = $field_data['value'];

		$field_options = array(
			'name' => $tag,
			'desc' => $field['desc'],
			'label' => $field['label'],
			'value' => $value,
			'render_type' => $render_type,
			'field_count' => $field_idx,
			'required' => $field['required'],
		);

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
			case 'date':
				$field_options['class'] = 'pfund-date';
				$content .= _pfund_render_text_field( $field_options );
				$field_idx++;
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
				$content .= _pfund_render_field_list_item( $field_options, $field_content );
				$field_idx++;
				break;
			case 'image':
				$content .= _pfund_render_image_field( $field_options );
				$field_idx++;
				break;
			case 'select':
				$field_content = pfund_render_select_field( $field['data'], $tag, $value );
				$content .= _pfund_render_field_list_item( $field_options, $field_content );
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
	if ( array_key_exists( 'fields',  $options ) ) {
		$fieldname = '';
		foreach ( $options['fields'] as $field_id => $field ) {
			$fieldname = 'pfund-'.$field_id;			
			switch( $field['type'] ) {
				case 'image':
					 _pfund_attach_uploaded_image( $fieldname, $campid, "_pfund_".$field_id );
					break;
				case 'user_goal':
				case 'gift_tally':
					if (array_key_exists( $fieldname, $_REQUEST ) ) {
						update_post_meta( $campid, "_pfund_".$field_id, absint( $_REQUEST[$fieldname] ) );
					}
					break;
				default:
					update_post_meta( $campid, "_pfund_".$field_id, strip_tags( $_REQUEST[$fieldname] ) );
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
	if( is_uploaded_file( $_FILES[$fieldname]['tmp_name'] ) ) {
		$data = media_handle_upload( $fieldname, $post_id, array( 'post_status' => 'private' ) );
		if( is_wp_error( $data ) ) {
			$errors[] = $data;
			error_log("error adding image for personal fundraising:".print_r( $data, true ) );
		} else {
			update_post_meta( $postid, $metaname, $data );
		}
	}
}

/**
 * Render the field using the specified render type.
 * @param array $field_options named options for field.
 * @param string $field_contents actual input field to render.
 * @return string the rendered HTML.
 */
function _pfund_render_field_list_item( $field_options, $field_contents ) {
	$content .= '<li>';
	$content .= '	<label for="'.$field_options['name'].'">'.$field_options['label'];
	if ( $field_options['required'] ) {
		$content .= '<abbr title="'.esc_attr__( 'required', 'pfund' ).'">*</abbr>';
	}
	$content .= '</label>';
	$content .= $field_contents;
	if ( $field_options['render_type'] == 'user' && ! empty( $field_options['desc'] ) ) {
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
	if ( $field_options['required'] || array_key_exists( 'custom_validation', $field_options ) ) {
		$field_options['class'] .= ' validate[';
		if ( $field_options['required'] ) {
			$field_options['class'] .= 'required';
			if ( array_key_exists( 'custom_validation', $field_options ) ) {
				$field_options['class'] .= ',';
			}
		}
		$field_options['class'] .=  $field_options['custom_validation'];
		$field_options['class'] .= ']';
	}
	$field_options = array_merge($defaults, $field_options);
	$content .= $field_options['pre_input'];
	$content .= '	<input class="'.$field_options['class'].'" id="'.$field_options['name'].'"';
	$content .= '		type="'.$field_options['type'].'" name="'.$field_options['name'].'"';
	if ( $field_options['type'] != 'file' ) {
		$content .= ' value="'.esc_attr( $field_options['value'] ).'"';
	}
	$content .= '/>';
	$content .= $field_options['additional_content'];
	return _pfund_render_field_list_item( $field_options, $content);
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
