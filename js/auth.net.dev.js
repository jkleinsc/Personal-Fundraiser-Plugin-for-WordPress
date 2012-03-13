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
jQuery(function($) {

    function showAuthNetDonateForm(e) {
        e.preventDefault();
        $('.pfund-auth-net-form').slideDown();
        $(this).hide();
    }
    
	function submitAuthNetDonation() {
        $form = $(this);
        if ($form.validationEngine({returnIsValid:true})) {
            $form.find('.error').remove();
            $('#pfund_donate_button').attr("disabled", "disabled").html(pfund.processing_msg);

            var data = $(this).serialize() + '&action=pfund_auth_net_donation';
            var url = "/wp-admin/admin-ajax.php";

            $.post(url, data, function(json) {
                if(json.success) {
                    $form.find('#pfund_donate_button').after('<div class="success">'+pfund.thank_you_msg+'</div>');
                    setTimeout(function() {
                        window.location.href=window.location.href;
                    }, 2500);
                } else {
                    $form.find('#pfund_donate_button').after('<div class="error">' + json.error + '</div>');
                    $('#pfund_donate_button').removeAttr("disabled").html('Donate');
                }
            }, 'json');
        }

        return false;
    }
    
    function showAuthNetSecurityMessage(e) {
        e.preventDefault();
        $('.pfund-auth-net-secure-donations-text').slideDown();
    }	   
    
    $('.pfund-auth-net-donate a').click(showAuthNetDonateForm);
    $('form.pfund-auth-net-form').submit(submitAuthNetDonation);
    $('a.pfund-auth-net-secure-donations-link').click(showAuthNetSecurityMessage);
});