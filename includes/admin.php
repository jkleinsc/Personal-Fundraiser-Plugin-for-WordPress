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
 * Add the admin css to the page if applicable.
 */
function pfund_admin_css() {
	if ( pfund_is_pfund_post() ) {
		wp_enqueue_style( 'pfund_admin', PFUND_URL.'css/admin.css', array(), PFUND_VERSION );
	}
}

/**
 * Initialize administrator functionality.
 */
function pfund_admin_init() {
	$options = get_option( 'pfund_options' );	
	register_setting( 'pfund_options', 'pfund_options' );
	add_settings_section( 'pfund_main_options', __( 'Personal Fundraiser Options', 'pfund' ), 'pfund_main_section_text', 'pfund' );
	add_settings_field(
		'pfund_campaign_slug',
		__( 'Campaign Slug', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_main_options',
		array(
			'name' => 'campaign_slug',
			'value' => $options['campaign_slug']
		)
	);
	add_settings_field(
		'pfund_cause_slug',
		__( 'Cause Slug', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_main_options',
		array(
			'name' => 'cause_slug',
			'value' => $options['cause_slug']
		)
	);
	add_settings_field(
		'pfund_currency_symbol',
		__( 'Currency Symbol', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_main_options',
		array(
			'name' => 'currency_symbol',
			'value' => $options['currency_symbol']
		)
	);
	add_settings_field(
		'pfund_date_format',
		__( 'Date Format', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_main_options',
		array(
			'name' => 'date_format',
			'value' => pfund_get_value($options, 'date_format', 'm/d/y' )
		)
	);
	add_settings_section(
		'pfund_permission_options',
		__( 'Campaign Creation Options', 'pfund' ),
		'pfund_permissions_section_text',
		'pfund'
	);
	add_settings_field(
		'pfund_login_required',
		__( 'Login Required to Create', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_permission_options',
		array(
			'name' => 'login_required',
			'type' => 'checkbox',
			'value' => $options['login_required']
		)
	);
	add_settings_field(
		'pfund_allow_registration',
		__( 'Allow Users To Register', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_permission_options',
		array(
			'name' => 'allow_registration',
			'type' => 'checkbox',
			'value' => $options['allow_registration']
		)
	);
	add_settings_field(
		'pfund_approval_required',
		__( 'Campaigns Require Approval', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_permission_options',
		array(
			'name' => 'approval_required',
			'type' => 'checkbox',
			'value' => $options['approval_required']
		)
	);
	add_settings_field(
		'pfund_submit_role',
		__( 'User Roles that can submit campaigns', 'pfund' ),
		'pfund_role_select_field',
		'pfund',
		'pfund_permission_options',
		array(
			'name' => 'submit_role',
			'value' => $options['submit_role']
		)
	);
	add_settings_section(
		'pfund_paypal_options',
		__( 'PayPal Options', 'pfund' ),
		'pfund_paypal_section_text',
		'pfund'
	);
	add_settings_field(
		'pfund_paypal_donate_btn',
		__( 'Donate Button Code', 'pfund' ),
		'pfund_option_text_area',
		'pfund',
		'pfund_paypal_options',
		array(
			'name' => 'paypal_donate_btn',
			'value' => pfund_get_value( $options, 'paypal_donate_btn' )
		)
	);
	add_settings_field(
		'pfund_paypal_pdt_token',
		__( 'Payment Data Transfer Token', 'pfund' ),
		'pfund_option_text_area',
		'pfund',
		'pfund_paypal_options',
		array(
			'name' => 'paypal_pdt_token',
			'value' => pfund_get_value( $options, 'paypal_pdt_token' )
		)
	);
	$paypal_sandbox = pfund_get_value( $options , 'paypal_sandbox',  false );
	add_settings_field(
		'pfund_paypal_sandbox',
		__( 'Use PayPal Sandbox', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_paypal_options',
		array(
			'name' => 'paypal_sandbox',
			'type' => 'checkbox',
			'value' => $paypal_sandbox
		)
	);

	add_settings_section(
		'pfund_mailchimp_options',
		__( 'MailChimp Options', 'pfund' ),
		'pfund_mailchimp_section_text',
		'pfund'
	);
	$use_mailchimp = pfund_get_value( $options, 'mailchimp', false );
	add_settings_field(
		'pfund_use_mailchimp',
		__( 'Use MailChimp to send emails', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_mailchimp_options',
		array(
			'name' => 'mailchimp',
			'type' => 'checkbox',
			'value' => $use_mailchimp
		)
	);
	add_settings_field(
		'pfund_mailchimp_key',
		__( 'MailChimp API key', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_mailchimp_options',
		array(
			'name' => 'mc_api_key',
			'value' => pfund_get_value( $options, 'mc_api_key' )
		)
	);

	add_settings_field(
		'pfund_mc_email_publish_id',
		__( 'Campaign Approval Email ID', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_mailchimp_options',
		array(
			'name' => 'mc_email_publish_id',
			'value' => pfund_get_value( $options, 'mc_email_publish_id' )
		)
	);
	add_settings_field(
		'pfund_mc_email_donate_id',
		__( 'Campaign Donation Email ID', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_mailchimp_options',
		array(
			'name' => 'mc_email_donate_id',
			'value' => pfund_get_value( $options, 'mc_email_donate_id' )
		)
	);
	add_settings_field(
		'pfund_mc_email_goal_id',
		__( 'Goal Reached Email ID', 'pfund' ),
		'pfund_option_text_field',
		'pfund',
		'pfund_mailchimp_options',
		array(
			'name' => 'mc_email_goal_id',
			'value' => pfund_get_value( $options, 'mc_email_goal_id' )
		)
	);
	add_settings_section(
		'pfund_field_options',
		__( 'Personal Fundraiser Fields', 'pfund' ),
		'pfund_field_section_text',
		'pfund'
	);
}

/**
 * Add admin specific javascript
 */
function pfund_admin_js() {
	wp_enqueue_script( 'pfund_admin',PFUND_URL.'js/admin.js', array( 'jquery','jquery-ui-sortable' ), PFUND_VERSION, true );
}

/**
 * Initialize admin
 */
function pfund_admin_setup() {
	$menu = add_menu_page( __( 'Personal Fundraiser Settings', 'pfund' ), __( 'Personal Fundraiser', 'pfund' ),
			'manage_options', 'personal-fundraiser-settings', 'pfund_options_page');
	add_action( 'load-'.$menu, 'pfund_admin_js' );
	add_meta_box( 'pfund-options', __( 'Personal Fundraising fields', 'pfund' ), 'pfund_campaign_meta', 'pfund_campaign', 'normal', 'high' );
	add_meta_box( 'pfund-options', __( 'Personal Fundraising fields', 'pfund' ), 'pfund_cause_meta', 'pfund_cause', 'normal', 'high' );

}

/**
 * Display the meta fields for the specified campaign.
 * @param mixed $post The campaign to display meta fields for.
 */
function pfund_campaign_meta( $post ) {
?>	
	<ul>
		<?php echo pfund_render_fields( $post->ID, $post->post_title ); ?>
	</ul>
<?php
}

/**
 * Add custom columns to the campaign listing in admin.
 * @param array $columns The currently defined columns.
 * @return array The list of columns to display.
 */
function pfund_campaign_posts_columns( $columns ) {
    $columns['cause'] = __( 'Cause', 'pfund' );
	$columns['user'] = __( 'User', 'pfund' );
	$columns['goal'] = __( 'Goal', 'pfund' );
	$columns['tally'] = __( 'Raised', 'pfund' );
    return $columns;
}

/**
 * Get the data for the custom columns in the campaign listing in admin.
 * @param string $column_name the name of the column to retrieve data for.
 * @param string $campaign_id the id of the campaign to retrieve data for.
 */
function pfund_campaign_posts_custom_column( $column_name, $campaign_id ) {
	switch ( $column_name ) {
		case 'cause':
			$cause_id = get_post_meta( $campaign_id, '_pfund_cause_id', true );
			$cause = get_post( $cause_id );

			$edit_link = get_edit_post_link( $cause_id );
			$post_type_object = get_post_type_object( $cause->post_type );
			$can_edit_post = current_user_can( $post_type_object->cap->edit_post,  $cause_id  );

			echo '<strong>';
			if ( $can_edit_post && $cause->post_status != 'trash' ) {
?>
				<a class="row-title" href="<?php echo $edit_link; ?>" title="<?php echo esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $cause->post_title ) ); ?>"><?php echo $cause->post_title ?></a>
<?php

			} else {
				echo $cause->post_title;

			}
			echo '</strong>';
			break;
		case 'goal':
			echo get_post_meta( $campaign_id, '_pfund_gift-goal', true );
			break;
		case 'tally':
			echo get_post_meta( $campaign_id, '_pfund_gift-tally', true );
			break;
		case 'user':
			global $post;
			$author = get_userdata( $post->post_author );
			if ( $author ) {
				echo strip_tags( $author->display_name );
			}
			break;
	}
}

/**
 * Add custom sortable columns to the campaign listing in admin.
 * @param array $columns The currently defined sortable columns.
 * @return array The list of sortable columns.
 */
function pfund_campaign_sortable_columns( $columns ) {
	$columns['cause'] = 'cause';
	$columns['user'] = 'user';
	return $columns;
}

/**
 * Display the meta fields for the specified cause.
 * @param mixed $post The cause to display meta fields for.
 */
function pfund_cause_meta( $post ) {
	$metavalues = get_post_custom( $post->ID );
	$cause_description = pfund_get_value( $metavalues,
			'_pfund_cause_description', array('') );
?>
	<ul>
		<li>
			<label for="pfund-cause-description"><?php _e( 'Cause Description', 'pfund' );?></label>
			<textarea class="pfund-textarea" id="pfund-cause-description" name="pfund-cause-description" rows="10" cols="50"><?php echo $cause_description[0];?></textarea>
		</li>
<?php
		$cause_image = pfund_get_value( $metavalues, '_pfund_cause_image', array('') );
		echo _pfund_render_image_field( array(
			'name' => 'pfund-cause-image',
			'label' =>__( 'Cause Image', 'pfund' ),
			'value' => $cause_image[0]
		) );
?>
	</ul>
<?php
}

/**
 * Modify admin form to allow file uploads.
 */
function pfund_edit_form_tag() {
	global $post;
	if ( pfund_is_pfund_post() ){
		echo ' enctype="multipart/form-data"';
	}
}

/**
 * Text to display in personal fundraising settings in the Personal Fundraiser Fields
 * section.
 */
function pfund_field_section_text() {
	echo '<p>'.__( 'Define your fields for personal fundraisers', 'pfund' ).'</p>';
}

/**
 * Send an email when a campaign gets published (approved).
 * @param int $post_id Id of the campaign.
 * @param mixed $post the post object containing the campaign
 */
function pfund_handle_publish( $post_id, $post ) {
	$sent_mail = get_post_meta( $post_id, '_pfund_emailed_published', true );
	if ( apply_filters( 'pfund_mail_on_publish', true, $post, $author_data, $campaignUrl ) && empty( $sent_mail ) ) {
		$options = get_option( 'pfund_options' );
		$author_data = pfund_get_contact_info( $post, $options );
		$campaignUrl = get_permalink( $post );
		if ( $options['mailchimp'] ) {
			$merge_vars = array(
				'NAME'=>$author_data->display_name,
				'CAMP_TITLE'=> $post->post_title,
				'CAMP_URL'=> $campaignUrl
			);
			pfund_send_mc_email( $author_data->user_email, $merge_vars, $options['mc_email_publish_id'] );
		} else {
			$pub_message = sprintf( __( 'Dear %s,', 'pfund' ), $author_data->display_name ).PHP_EOL;
			$pub_message .= sprintf( __( 'Your campaign, %s has been approved.', 'pfund' ), $post->post_title).PHP_EOL;
			$pub_message .= sprintf( __( 'You can view your campaign at: %s.', 'pfund' ), $campaignUrl ).PHP_EOL;
			wp_mail( $author_data->user_email, __( 'Your campaign has been approved', 'pfund' ) , $pub_message );
		}
		add_post_meta( $post_id, '_pfund_emailed_published', true );
	}
}

/**
 * Text to display in personal fundraising settings in the MailChimp section.
 */
function pfund_mailchimp_section_text() {
	echo '<p>'.__( 'MailChimp settings for personal fundraiser', 'pfund' ).'</p>';
	$options = get_option( 'pfund_options' );
}

/**
 * Text to display in personal fundraising settings in the main section. 
 */
function pfund_main_section_text() {
	echo '<p>'.__( 'General settings for personal fundraiser', 'pfund' ).'</p>';
}

/**
 * Render a textarea field in the personal fundraising settings.
 * @param array $config Array containing the name and value of to use to
 * render the textarea field.
 */
function pfund_option_text_area( $config ) {
	$value = $config['value'];
	$name = $config['name'];
	echo "<textarea class='large-text code' name='pfund_options[$name]'>$value</textarea>";
}

/**
 * Render an input text field in the personal fundraising settings.
 * @param array $config Array containing the name and value of to use to
 * render the input text field.
 */
function pfund_option_text_field( $config ) {
	$value = $config['value'];
	$name = $config['name'];
	$type = pfund_get_value( $config, 'type', 'text' );
	if ( $type == 'checkbox' ) {
		$checked = checked($value, true, false);
		$value = "true";
	} else {
		$checked = '';
	}

	// echo the field
	echo "<input id='$name' name='pfund_options[$name]' type='$type' value='$value' $checked/>";
}

/**
 * Render the personal fundraising options page.
 */
function pfund_options_page() {
?>
	<div class="wrap">
	<?php screen_icon(); ?>
		<h2><?php _e( 'Personal Fundraiser', 'pfund' );?></h2>
		<form action="options.php" method="post">
		<?php
			settings_fields( 'pfund_options' );
			do_settings_sections( 'pfund' );
			_pfund_option_fields();

		?>
		<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes', 'pfund' );?>">
		</form>
		<table style="display:none;">
		<?php
			_pfund_render_option_field( '_pfund-template-row', array( 'type' => 'text' ) );
		?>
		</table>

	</div>
<?php
}

/**
 * Text to display in personal fundraising settings in the PayPal section.
 */
function pfund_paypal_section_text() {
	echo '<p>'.__( 'PayPal settings for personal fundraiser', 'pfund' ).'</p>';
}

/**
 * Text to display in personal fundraising settings in the permissions section.
 */
function pfund_permissions_section_text() {
	echo '<p>'.__( 'Settings to determine who can create or submit campaigns', 'pfund' ).'</p>';
}

/**
 * Add a settings link to the plugin listing
 * @param array $links Array of links for plugin listing
 * @param string $file Name of plugin file
 * @return array the array of links for plugin listing.
 */
function pfund_plugin_action_links( $links, $file ) {
	if( PFUND_BASENAME == $file ) {
		$links[] = sprintf( '<a href="admin.php?page=personal-fundraiser-settings">%s</a>', __('Settings') );
	}
	return $links;
}

/**
 * Render an role drop down field in the personal fundraising settings.
 * @param array $config Array containing the name and current value of to use
 * to render the drop down field.
 */
function pfund_role_select_field( $config ) {
	$value = $config['value'];
	$name = $config['name'];
	global $wp_roles;
	$avail_roles = $wp_roles->get_names();
	echo '<fieldset>';
	$i=0;
	foreach ( $avail_roles as $key => $desc ) {
		echo '<label for="pfund_options['.$name.']_'.$i.'">';
		echo '<input type="checkbox" value="'.$key.'"'.checked( in_array( $key, $value ), true, false ).' name="pfund_options['.$name.']['.$i.']"/>';
		echo $desc;
		echo '</label><br/>';
		$i++;
	}
	echo '</fieldset>';
}

/**
 * Save the personal fundraising fields when saving the post.
 * @param string $post_id The id of the post to save the fields for.
 */
function pfund_save_meta( $post_id, $post ) {
	switch ($post->post_type) {
		case 'pfund_cause':
			_pfund_save_cause_fields( $post_id );
			break;
		case 'pfund_campaign':
			pfund_save_campaign_fields( $post_id );
			break;
	}		
}

/**
 * Render a hidden type field in the admin options page.
 * @param string $field_id the id of the field this field type is for.
 * @param string $field_type the field type value.
 */
function _pfund_hidden_type_field( $field_id, $field_type ) {
?>
	<input name="pfund_options[fields][<?php echo $field_id; ?>][type]" type="hidden" value="<?php echo $field_type;?>">
<?php
	
}

/**
 * Display the personal fundraising fields
 */
function _pfund_option_fields() {
?>
	<table id="pfund-fields-table" class="widefat page">
		<thead>
			<tr>
				<th scope="col"><?php _e( 'Label', 'pfund' ) ?></th>
				<th scope="col"><?php _e( 'Description', 'pfund' ) ?></th>
				<th scope="col"><?php _e( 'Type', 'pfund' ) ?></th>
				<th scope="col"><?php _e( 'Data', 'pfund' ) ?></th>
				<th scope="col"><?php _e( 'Required', 'pfund' ) ?></th>
				<th scope="col"><?php _e( 'Shortcode', 'pfund' ) ?></th>
				<th scope="col"><?php _e( 'Actions', 'pfund' ) ?></th>
			</tr>
		</thead>
        <tbody>
<?php
	$options = get_option( 'pfund_options' );
	$fields = pfund_get_value( $options, 'fields', array() );
	if ( count( $fields ) == 0 ) {
		$fields[1] = array(
			'type' => 'text'
		);
	}
	foreach ( $fields as $field_id => $field ) {		
		_pfund_render_option_field( $field_id, $field );
	}
?>
			<tr class="pfund-add-row">
				<td colspan="5" style="text-align: right;">
					<a href="#" class="pfund-add-field"><?php _e( 'Add New Field', 'pfund' ) ?></a>
				</td>
			</tr>
		</tbody>
	</table>
<?php
}

/**
 * Render a row on the personal fundraiser fields section of the settings.
 * @param string $field_id The id of the field to add.
 * @param array $field the definition of the field.
 */
function _pfund_render_option_field( $field_id, $field ) {
	$fieldtypes = array(
		'date' => __( 'Date Selector', 'pfund' ),
		'textarea' => __( 'Large Text Input (textarea)', 'pfund' ),
		'image' => __( 'Image', 'pfund' ),
		'select' => __( 'Select Dropdown', 'pfund' ),
		'text' => __( 'Text Input', 'pfund' ),
		'fixed' => __( 'Fixed Input', 'pfund' ),
		'user_email' => __( 'User Email', 'pfund' ),
		'user_displayname' => __( 'User Display Name', 'pfund' )
	);
	$field_label = pfund_get_value( $field, 'label' );
	$field_desc = pfund_get_value( $field, 'desc' );
?>
	<tr class="form-table pfund-field-row" id="<?php echo $field_id; ?>">
		<td>
			<input class="pfund-label-field"  name="pfund_options[fields][<?php echo $field_id; ?>][label]" type='text' value="<?php echo $field_label;?>"/>
		</td>
		<td>
			<textarea class="pfund-desc-field"  name="pfund_options[fields][<?php echo $field_id; ?>][desc]"><?php echo $field_desc;?></textarea>
		</td>
		<td>
<?php
		switch( $field['type'] ) {
			case 'camp_location':
				_e( 'Campaign URL slug', 'pfund' );
				$can_delete_field = false;
				_pfund_hidden_type_field( $field_id, $field['type'] );
				break;
			case 'camp_title':
				_e( 'Campaign Title', 'pfund' );
				$can_delete_field = false;
				_pfund_hidden_type_field( $field_id, $field['type'] );
				break;
			case 'end_date':
				_e( 'End Date', 'pfund' );
				$can_delete_field = false;
				_pfund_hidden_type_field( $field_id, $field['type'] );
				break;
			case 'user_goal':
				_e( 'User Goal', 'pfund' );
				$can_delete_field = false;
				_pfund_hidden_type_field( $field_id, $field['type'] );
				break;
			case 'gift_tally':
				_e( 'Total Raised', 'pfund' );
				$can_delete_field = false;
				_pfund_hidden_type_field( $field_id, $field['type'] );
				break;
			default:
				$can_delete_field = true;
?>
				<select id="pfund-field-select-<?php echo $field_id; ?>" class="pfund-type-field" name="pfund_options[fields][<?php echo $field_id; ?>][type]">
<?php
					foreach( $fieldtypes as $type => $label ) {
?>
						<option value="<?php echo $type ?>"<?php selected( $field['type'], $type );?>><?php echo $label ?></option>
<?php
					}
?>
				</select>
<?php
		}
?>
		</td>
		<td>
<?php
			$content = '';
			$sample_style = "display:none;";
			$field_data = pfund_get_value( $field, 'data' );
			switch( $field['type'] ) {
				case 'select':
					$sample_style = "";
					$content .= pfund_render_select_field( $field_data );
					$content .= '<br/>';
					break;
			}
?>
			<div class="pfund-data-type-sample" style="<?php echo $sample_style;?>">
				<div class="pfund-data-sample-view">
					<?php echo $content; ?>
				</div>
				<a href="#" class="pfund-data-field-edit"><?php _e( 'Edit', 'pfund' );?></a>
			</div>
			<div class="pfund-data-type-edit" style="display:none;">
				<textarea class="large-text code" name="pfund_options[fields][<?php echo $field_id; ?>][data]"><?php echo $field_data; ?></textarea>
				<br/><a href="#" class="pfund-data-field-update"><?php _e( 'Update', 'pfund' );?></a>
			</div>

		</td>
		<td>
<?php
			if ( $can_delete_field || $field['type'] == 'end_date') {
				$required = pfund_get_value( $field, 'required', false );
?>
				<input class="pfund-required-field"  name="pfund_options[fields][<?php echo $field_id; ?>][required]" type='checkbox' value="true" <?php checked( $required, 'true' );?> />
<?php
			} else {
				_e( 'Yes', 'pfund' );
?>
				<input name="pfund_options[fields][<?php echo $field_id; ?>][required]" type='hidden' value="true">
<?php
			}
?>

		</td>
		<td class="pfund-shortcode-field">
<?php

			if ( isset ( $field['label'] ) ) {
				echo pfund_determine_shortcode( $field_id, $field['type'] );
			}
?>
		</td>
		<td>
<?php
			if ( $can_delete_field ) {
?>
				<a class="pfund-delete-field" href="#" field-id="<?php echo $field_id; ?>">Delete</a><br/>
<?php
			}
?>
				<a class="pfund-move-up-field" href="#" field-id="<?php echo $field_id; ?>">Move Up</a><br/>
				<a class="pfund-move-dn-field" href="#" field-id="<?php echo $field_id; ?>">Move Down</a><br/>
		</td>

	</tr>
<?php
}

/**
 * Save the meta fields for the specified cause.
 * @param string $cause_id The id of the cause to save meta fields for.
 */
function _pfund_save_cause_fields( $cause_id ) {
	_pfund_attach_uploaded_image( 'pfund-cause-image', $cause_id, '_pfund_cause_image' );
	if ( isset( $_REQUEST['pfund-cause-description'] ) ) {
		update_post_meta( $cause_id, "_pfund_cause_description",
				strip_tags( $_REQUEST['pfund-cause-description'] ) );
	}
}



?>