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
 * Process a PayPal transaction using PayPal's Payment Data Transfer method.
 * @return array with the following keys:
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
 */
function pfund_process_paypal_pdt() {
	$return_array = array( 'success' => false );
	try {
		$options = get_option( 'pfund_options' );

		$auth_token = $options['paypal_pdt_token'];
		$tx_token = $_GET['tx'];
		$request_body = array(
			'cmd' => '_notify-synch',
			'tx' => $tx_token,
			'at' => $auth_token,
		);

		$response = wp_remote_post( 'https://www.sandbox.paypal.com/cgi-bin/webscr',
				array( 'body' => $request_body, 'timeout' => 10 )
		);
		//https://www.paypal.com

		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
			$lines = explode( "\n", $response['body'] );
			$keyarray = array();
			if ( strcmp( $lines[0], "SUCCESS" ) == 0 ) {
				for ( $i=1; $i<count( $lines );$i++ ){
					list( $key, $val ) = explode( "=", $lines[$i] );
					$keyarray[urldecode( $key )] = urldecode( $val );
				}
				$return_array['success'] = true;
				_pfund_map_paypal_fields( $keyarray, &$return_array );
			} else {
				$return_array['error_code'] = 'paypal_returned_failure';
				$return_array['error_msg'] = 'PayPal did not return a successful result.  Response was'.$response['body'];
			}
		} else {
			_pfund_handle_paypal_error( $response, &$return_array );
		}
	} catch ( Exception $e ) {
		$return_array['error_code'] = 'exception_encountered';
		$return_array['error_msg'] = 'pfund_process_paypal_pdt throw the following exception:'.$e->getMessage();
	}
	return $return_array;
}


/**
 * Process a PayPal transaction using PayPal's Instant Payment Notification method.
 * @return array with the following keys:
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
 */
function pfund_process_paypal_ipn() {
	$return_array = array( 'success' => false );
	try {
		$request_body = $_POST;
		$request_body['cmd'] = '_notify-validate';
		$response = wp_remote_post( 'https://www.sandbox.paypal.com/cgi-bin/webscr',
				array( 'body' => $request_body, 'timeout' => 10 )
		);
		if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
			if ( strcmp( $response['body'], "VERIFIED") == 0 ) {
				$return_array['success'] = true;
				_pfund_map_paypal_fields( $_POST, &$return_array );
			} else {
				$return_array['error_code'] = 'paypal_returned_failure';
				$return_array['error_msg'] = 'PayPal did not return a successful result.  Response was'.$response['body'];
			}
		} else {
			_pfund_handle_paypal_error( $response, &$return_array );
		}
		//https://www.paypal.com
	} catch ( Exception $e ) {
		$return_array['error_code'] = 'exception_encountered';
		$return_array['error_msg'] = 'pfund_process_paypal_ipn throw the following exception:'.$e->getMessage();
	}
	return $return_array;
}

/**
 * Pull values from PayPal's response and put them in the return array.
 *
 * @param array $paypal_response The response from PayPal.
 * @param array $return_array The array that will be returned from processing
 * the transaction.
 */
function _pfund_map_paypal_fields( $paypal_response, $return_array ) {
	$return_array['amount'] = $paypal_response['mc_gross'];
	$return_array['donor_first_name'] = $paypal_response['first_name'];
	$return_array['donor_last_name'] = $paypal_response['last_name'];
	$return_array['donor_email'] = $paypal_response['payer_email'];
	if ( strpos( $paypal_response['custom'], 'anon' ) === 0) {
		$return_array['anonymous'] = true;
	}
}

/**
 * Handle an error when calling PayPal
 * @param mixed $response The response from the PayPal call.
 * @param array $return_array The array that will be returned from processing
 * the transaction.
 */
function _pfund_handle_paypal_error( $response,  $return_array ) {
	if ( is_wp_error( $response ) ) {
		$return_array['wp_error'] = $response;
		$return_array['error_code'] = 'wp_error';
	} else {
		$return_array['error_code'] = 'no_response_returned';
		$return_array['error_msg'] = 'PayPal did not return a response.  Orginating request was:'.print_r( $request_body, true );
	}
}
?>