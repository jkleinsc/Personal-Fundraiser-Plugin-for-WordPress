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

/**
 * Handles registering a new user.
 *
 * @param array $value_array Array of fields to create user.
 * @return int|WP_Error Either user's ID or error on failure.
 */
function pfund_register_user($value_array=array()) {
    if (empty($value_array)) {
        $value_array = $_POST;
    }
    $user_login = $value_array['pfund_user_login'];
    $user_pass = $value_array['pfund_user_pass'];
    $user_email = $value_array['pfund_user_email'];
    $first_name = $value_array['pfund_user_first_name'];
    $last_name = $value_array['pfund_user_last_name'];

    $errors = new WP_Error();

    $sanitized_user_login = sanitize_user( $user_login );
    $user_email = apply_filters( 'user_registration_email', $user_email );

    // Check the username
    if ( $sanitized_user_login == '' ) {
        $errors->add( 'empty_username', 'true');
    } elseif ( ! validate_username( $user_login ) ) {
        $errors->add( 'invalid_username', 'true');
        $sanitized_user_login = '';
    } elseif ( username_exists( $sanitized_user_login ) ) {
        $errors->add( 'username_exists', 'true');
    }

    // Check the e-mail address
    if ( $user_email == '' ) {
        $errors->add( 'empty_email', 'true');
    } elseif ( ! is_email( $user_email ) ) {
        $errors->add( 'invalid_email', 'true');
        $user_email = '';
    } elseif ( email_exists( $user_email ) ) {
        $errors->add( 'email_exists', 'true');
    }

    do_action( 'register_post', $sanitized_user_login, $user_email, $errors );

    $errors = apply_filters( 'registration_errors', $errors, $sanitized_user_login, $user_email );

    if ( $errors->get_error_code() )
        return $errors;


    $user_login = $sanitized_user_login;
    $userdata = compact('user_login', 'user_email', 'user_pass','first_name','last_name');
    $user_id = wp_insert_user($userdata);
    if (! $user_id ) {
        $errors->add( 'registerfail', 'true' );
        return $errors;
    }


    wp_new_user_notification($user_id, $user_pass);

    return $user_id;
}

$registerResult = pfund_register_user();
if (is_wp_error($registerResult) ) {
    echo json_encode($registerResult); 
} else {
    echo '{"success":true}';
}

?>