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
jQuery(function($) {
    /**
     * Add a personal fundraising field.
     */
	function addField(e) {
		if (e) {
			e.preventDefault();
		}
		var insertBefore = $(this).closest('tbody').find('tr.pfund-add-row');
		var newRow = $('#_pfund-template-row').clone(true);
		newRow.find('.pfund-data-type-edit textarea').val('');
		updateId(newRow, "***", true);
		newRow.find('.pfund-shortcode-field').html('');
		newRow.find('.pfund-data-sample-view').html('');
		insertBefore.before(newRow);
		var typeField = newRow.find('.pfund-type-field');
		typeField.val('text');
		$.proxy(typeFieldChanged, typeField)();
	}

    /**
     * Delete a personal fundraising field.
     */
	function deleteField(e) {
		e.preventDefault();
		var field = $(this);
		fieldRowCount = field.closest('tbody').children('tr.pfund-field-row').length;
		if (fieldRowCount == 1) {
			$.proxy(addField, this)();
		}
		field.closest('tr.pfund-field-row').remove();
	}

    /**
     * Display a sample rendering of the specified field.
     */
	function displayDataTypeSample(dataValues, fieldType, sampleField) {
		var values = dataValues.split("\n");
		var sample = "";
		var i=0;
		if (fieldType === 'select') {
			sample += '<select>';
			for (i=0; i< values.length; i++) {
				sample += '<option>' + values[i] +'</option>';
			}
			sample += '</select><br/>';
		}
		sampleField.html(sample);
	}

    /**
     * Edit the current field's data
     */
	function editDataField(e) {
		e.preventDefault();
		var sampleField = $(this).closest('.pfund-data-type-sample');
		sampleField.hide();
		sampleField.next('.pfund-data-type-edit').show();
	}

    /**
     * When a field's label changes, update the rest of the field data to
     * reflect that change.
     */
	function labelFieldChanged() {
		var label = $(this).val().toLowerCase();
		label = label.replace(/\s/g, '-');
		var currentRow = $(this).closest('tr');
		var fieldType = currentRow.find('select.pfund-type-field');
		if (fieldType.length) {
			var shortCodeField = currentRow.find('.pfund-shortcode-field');
			var shortCode = '';
			if (label.length > 0) {
				shortCode += '[pfund-' + label;
				if (fieldType.val() === 'fixed') {
					shortCode += ' value="?"';
				}
				shortCode += ']';
			}
			shortCodeField.html(shortCode);
			updateId(currentRow, label);
		}
	}

    /**
     * Handler to move field down in display order.
     */
	function moveFieldDown(e) {
		e.preventDefault();
		var currentRow = $(this).closest('tr');
		currentRow.insertAfter(currentRow.next());
	}

    /**
     * Handler to move field up in display order.
     */
	function moveFieldUp(e) {
		e.preventDefault();
		var currentRow = $(this).closest('tr');
		currentRow.insertBefore(currentRow.prev());
	}

    /**
     * Use ajax call to display donations in campaign edit screen.
     */
	function showDonations(e) {
        var showLink = $(this);
		var st = showLink.data('pfund-donation-start');
        var num = 20;

		showLink.data('pfund-donation-start', st+num);
		
		$('#commentsdiv img.waiting').show();

		var data = {
			'action' : 'pfund_get_donations_list',
            'mode' : 'single',
			'_ajax_nonce' : $('#pfund_get_donations_nonce').val(),
			'p' : $('#post_ID').val(),
			'start' : st,
			'number' : num
		};

		$.post(ajaxurl, data,
			function(r) {
				r = wpAjax.parseAjaxResponse(r);
                console.log("response:");
                console.dir(r);
				$('#commentsdiv .widefat').show();
				$('#commentsdiv img.waiting').hide();

				if ( 'object' == typeof r && r.responses[0] ) {
					$('#the-comment-list').append( r.responses[0].data );

					theList = theExtraList = null;
					$("a[className*=':']").unbind();

					if ( showLink.data('pfund-donation-start') > showLink.data('pfund-donation-total') )
						$('#pfund-show-donations').hide();
					else
						$('#pfund-show-donations').html(pfund.show_more_donations);
					return;
				} else if ( 1 == r ) {
					$('#show-donations').parent().html(pfund.no_more_donations);
					return;
				}

				$('#the-comment-list').append('<tr><td colspan="2">'+wpAjax.broken+'</td></tr>');
			}
		);
		e.preventDefault();
		return false;
	}


    /**
     * Handler when a personal fundraiser field's type is changed.
     */
	function typeFieldChanged() {
		var typeField = $(this);
		var fieldType = typeField.val();
		var currentRow = typeField.closest('tr');
		var dataField = currentRow.find('.pfund-data-type-edit');
		var dataVal = dataField.find('textarea').first().val();
		var sampleField = currentRow.find('.pfund-data-type-sample');
		switch (fieldType) {
			case 'select':
				if (dataVal === '') {
					dataField.show();
					sampleField.hide();
				} else {
					dataField.hide();
					displayDataTypeSample(dataVal, fieldType, sampleField.find('.pfund-data-sample-view'));
					sampleField.show();
				}
				break;
			default:
				dataField.hide();
				sampleField.hide();
		}
		var labelField = currentRow.find('.pfund-label-field');
		$.proxy(labelFieldChanged, labelField)();
	}

    /**
     * Apply the changes from the data textarea to the current field.
     */
	function updateDataField(e) {
		e.preventDefault();
		var dataField = $(this).closest('.pfund-data-type-edit');
		var dataVal = dataField.find('textarea').first().val();
		var sampleField = dataField.prev('.pfund-data-type-sample');
		var fieldType = $(this).closest('tr').find('select.pfund-type-field').val();
		dataField.hide();
		displayDataTypeSample(dataVal, fieldType, sampleField.find('.pfund-data-sample-view'));
		sampleField.show();
	}

    /**
     * Update the field id for the specified field.
     */
	function updateId(aRow, newId, clearValues) {		
		var oldId = aRow[0].id;
		aRow.find('[name^="pfund_options"]').each(function() {
			var name = $(this).attr('name');
			name = name.replace('['+oldId+']','['+newId+']');
			$(this).attr('name',name);
			if (clearValues) {
				$(this).val('');
			}
		});
		aRow.attr('id',newId);
	}

    /**
     * Validate the add donation form to ensure that the required fields are
     * filled in.
     */
    function validateDonationAdd(e) {
        if (e.currentTarget.id == 'pfund-add-donation') {            
            if (!$('#pfund-add-donation-fields').validationEngine({returnIsValid:true})) {
                e.preventDefault();
            }
        }
    }

	$('.pfund-add-field').click(addField);
	$('.pfund-delete-field').click(deleteField);
	$('.pfund-data-field-edit').click(editDataField);
	$('.pfund-data-field-update').click(updateDataField);
	$('.pfund-move-dn-field').click(moveFieldDown);
	$('.pfund-move-up-field').click(moveFieldUp)	
	$('.pfund-type-field').change(typeFieldChanged);
	$('.pfund-label-field').change(labelFieldChanged);

	$('#pfund-show-donations').click(showDonations);

    $('#post').delegate(':submit', 'click', validateDonationAdd);



});