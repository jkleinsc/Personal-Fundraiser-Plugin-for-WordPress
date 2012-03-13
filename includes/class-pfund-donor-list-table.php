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
 * Personal Fundraiser donation list table class.
 * Used to display the list of donations to an administrator.
 *
 * @see WP_Post_Comments_List_Table
 */
class PFund_Donor_List_Table extends WP_Post_Comments_List_Table {

    var $currency_symbol;

    function __construct() {
        $options = get_option( 'pfund_options' );
        $this->currency_symbol = $options['currency_symbol'];
		parent::__construct();
    }

    function get_column_info() {
		$this->_column_headers = array(
			array(
			'author'   => __( 'Donor Information', 'pfund' ),
			'comment'  => __( 'Comment', 'pfund' ),
            'amount'  => __( 'Amount', 'pfund' ),
			),
			array(),
			array(),
		);

		return $this->_column_headers;
	}

    function column_amount( $comment ) {
        $trans_amount = get_comment_meta($comment->comment_ID, 'pfund_trans_amount',true);
        echo $this->currency_symbol . number_format_i18n( floatval( $trans_amount ), 2 );
    }
    
}
?>
