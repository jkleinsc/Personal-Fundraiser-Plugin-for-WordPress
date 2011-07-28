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

//Based on http://www.unwrongest.com/projects/show-password/
(function($){
	$.fn.extend({
		maskPassword: function(maskAction) {
			return this.each(function() {
				var createMask = function(el){
					var el = $(el);
					var masked = $("<input type='password' />");
					masked.insertAfter(el).attr({
						'id':el.attr('id')+'_mask',
						'class':el.attr('class'),
						'style':el.attr('style')
					});
					masked.hide();
					return masked;
				};

				var update = function($this,$that){
					$that.val($this.val());
				};

				var maskPassword = function(anEvent) {
					if (!$maskAction.data('masked')) {
						$maskAction.html(pfund.unmask_passwd);
						$unmasked.hide();
						$masked.show();
						update($masked,$unmasked);
						$maskAction.data('masked',true);
					} else {
						$maskAction.html(pfund.mask_passwd);
						$masked.hide();
						$unmasked.show();
						update($unmasked,$masked);
						$maskAction.data('masked',false);
					}
					return false;
				};

				var $masked = createMask(this),
					$unmasked = $(this),
					$maskAction = $(maskAction);
				$maskAction.toggle(maskPassword,maskPassword);
				$unmasked.keyup(function(){update($unmasked,$masked);});
				$masked.keyup(function(){update($masked,$unmasked);});
			});
		}
	});
})(jQuery);

jQuery(function($) {
	var campaignValid = false;
	var dialogSettings = {
		modal:true,
		resizable: false,
		close: closeDialog,
		width: 600,
		buttons: [
			{
				text: pfund.ok_btn,
				click:  updateCampaign
			},
			{
				text: pfund.cancel_btn,
				click:  closeDialog
			}
		]
	};

	/**
	 * Before the window is unloaded , check if any of the form elements
	 * have been modified and if so warn the user.
	 * @param e window.onbeforeunload event
	 */
	function checkForDirtyForm(e) {
		if (!$('#pfund-form').data('dirty')) {
			return;
		}
		var e = e || window.event;
		var warningMsg = pfund.save_warning;			
		// For IE and Firefox prior to version 4
		if (e) {
			e.returnValue = warningMsg;
		}
		// For Safari
		return warningMsg;
	};

	/**
	 * Close the currently open dialog.
	 */
	function closeDialog(e) {
		if (e.type != 'dialogclose') {
			$(this).dialog('close');
		}
		$.validationEngine.closePrompt('.formError',true);
	}

	/**
	 * Handler to mark the form as dirty when a user modifies a field.
	 */
	function inputChanged() {
		campaignValid = false;
		$('#pfund-form').data('dirty',true);
	}

	/**
	 * Handler when XHR fails to register user.
	 */
	function registerFail() {
		$('#pfund-wait-dialog').dialog('close');
		$.validationEngine.buildPrompt('#pfund-register-email',pfund.register_fail,'error');
	}

	/**
	 * Handler for XHR register user.  If there are errors registering the user, the
	 * error will be displayed;otherwise the register form will close and login the
	 * user.
	 */
	function registerResult(data) {
		if (data.success) {
			$('#pfund-user-login').val($('#pfund-register-username').val());
			$('#pfund-user-pass').val($('#pfund-register-pass').val());
			$('#pfund-login-form').submit();			
		} else {
			$('#pfund-wait-dialog').dialog('close');
			if (data.errors) {
				if (data.errors['invalid_email']) {
					$.validationEngine.buildPrompt('#pfund-register-email',pfund.invalid_email,'error');
				}
				if (data.errors['email_exists']) {
					$.validationEngine.buildPrompt('#pfund-register-email',pfund.email_exists,'error');
				}
				if (data.errors['username_exists']) {
					$.validationEngine.buildPrompt('#pfund-register-username',pfund.username_exists,'error');
				}
				if (data.errors['registerfail']) {
					registerFail();
				}
			} else {
				registerFail();
			}
		}
	}

	function registerUser() {
		var registerForm = $('#pfund-create-account-form');
        if (registerForm.validationEngine({returnIsValid:true,scroll: false})) {
			$('#pfund-wait-dialog').html(pfund.reg_wait_msg);
			showWaitDialog();
	        registerForm.ajaxSubmit({
	            dataType: 'json',
	            success:  registerResult,
	            error: registerFail
	        });
        }
		
	}

	/**
	 * Display the proper edit dialog in a modal window.
	 */
	function showEditDialog() {
		var editDialog = $('#pfund-edit-dialog');
		if (editDialog.length > 0) {
			editDialog.dialog(dialogSettings);
		} else {
			$('#pfund-add-dialog').dialog(dialogSettings);
		}
	}

	function showRegister() {
		$('#pfund-update-dialog').dialog('close');
		$('#pfund-register-dialog').dialog({		
			modal:true,
			width: 300,
			resizable: false,
			buttons: [{
				text: pfund.register_btn,
				click:  registerUser
			},
			{
				text: pfund.cancel_btn,
				click:  closeDialog
			}]
		});
		$("#pfund-register-pass").maskPassword("#pfund-mask-pass");
	}

	/**
	 * Display the please wait dialog
	 */
	function showWaitDialog() {
		$('#pfund-wait-dialog').dialog({
			closeOnEscape: false,
            draggable: false,
			modal:true,
			resizable: false
		});
	}

	/**
	 * Perform the actual submit to persist the campaign.
	 */
	function submitForm() {
		$('.ui-dialog').dialog('close');
		$('#pfund-form').data('dirty',false);
		showWaitDialog();
		document.pfund_form.submit();
	}

	/**
	 * Handler for Ok button on dialog.  This function kicks off the validation
	 * process of the form and if that is successful, the form will be submitted.
	 */
	function updateCampaign() {
		if ($('.pfund-camp-locationformError').hasClass('ajaxed')) {
			$('.pfund-camp-locationformError').removeClass('ajaxed');
		}
		var campaignForm = $('#pfund-form');
			campaignForm .unbind('ajaxSuccess');
			campaignForm.bind('ajaxSuccess',{
				errId: 'pfund-camp-locationformError'
			},validateAjax);

		campaignValid = campaignForm.validationEngine({
			scroll: false,
			returnIsValid: true
		});
	}

	/**
	 * Handler for ajax validation.  If the ajax validation is successful,
	 * submit the form.
	 * @param event the ajaxSuccess event.
	 * @param xhr the XHR object used for the ajax validation.
	 * @param settings object used to configure ajax validation.
	 */
	function validateAjax(event, xhr, settings) {
		if (settings.url.indexOf('validate-slug') > -1) {
			var errorMsg = $('.'+event.data.errId);
			if ((errorMsg.length == 0 || !errorMsg.hasClass('ajaxed')) && campaignValid) {
				submitForm();
			}
		}
	}

	$('#pfund-form input').change(inputChanged);
	$('#pfund-add-dialog').dialog(dialogSettings);
	$('.pfund-edit-btn').click(showEditDialog);
	$('.pfund-date').datepicker();
	$('#pfund-update-dialog').dialog({
		modal: true,
		resizable: false,
		buttons: [
			{
				text: pfund.ok_btn,
				click:  function() {
					$(this).dialog('close')
				}
			}
		]
	});
	$('#pfund-register-link').click(showRegister);
	
	window.onbeforeunload = checkForDirtyForm;
});


