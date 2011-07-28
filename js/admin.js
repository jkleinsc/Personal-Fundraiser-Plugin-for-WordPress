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

	function deleteField(e) {
		e.preventDefault();
		var field = $(this);
		fieldRowCount = field.closest('tbody').children('tr.pfund-field-row').length;
		if (fieldRowCount == 1) {
			$.proxy(addField, this)();
		}
		field.closest('tr.pfund-field-row').remove();
	}

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

	function editDataField(e) {
		e.preventDefault();
		var sampleField = $(this).closest('.pfund-data-type-sample');
		sampleField.hide();
		sampleField.next('.pfund-data-type-edit').show();
	}

    function fieldDraggerHelper(e, ui) {
        ui.children().each(function() {
            $(this).width($(this).width());
        });
        return ui;
    };

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

	function moveFieldDown(e) {
		e.preventDefault();
		var currentRow = $(this).closest('tr');
		currentRow.insertAfter(currentRow.next());
	}

	function moveFieldUp(e) {
		e.preventDefault();
		var currentRow = $(this).closest('tr');
		currentRow.insertBefore(currentRow.prev());
	}

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

	$('.pfund-add-field').click(addField);
	$('.pfund-delete-field').click(deleteField);
	$('.pfund-data-field-edit').click(editDataField);
	$('.pfund-data-field-update').click(updateDataField);
	$('.pfund-move-dn-field').click(moveFieldDown);
	$('.pfund-move-up-field').click(moveFieldUp)	
	$('.pfund-type-field').change(typeFieldChanged);
	$('.pfund-label-field').change(labelFieldChanged);

});


