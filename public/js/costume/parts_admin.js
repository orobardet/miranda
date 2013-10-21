$(function() {
	var gCostumePartXhrRequest = null;
	
	function costume_part_show_edit_part(row)
	{
		if (!row) {
			return;
		}
		
		var cellName = row.find('td.name');
		
		if (cellName) {
			var partName = row.attr('data-name');
			var partId = row.attr('data-id');
			var partForm = $('form#part-edit-form-template').clone();
			partForm.removeAttr('id');
			$('input[name="name"]', partForm).val(partName);
			$('input[name="id"]', partForm).val(partId);
			cellName.empty().prepend(partForm);
			partForm.show();
			$('input[name="name"]', partForm).select();
		}
	}
	
	function costume_part_hide_edit_part(row)
	{
		if (gCostumePartXhrRequest) {
			gCostumePartXhrRequest.abort();
			gCostumePartXhrRequest = null;
		}
		if (!row) {
			return;
		}
		
		var cellName = row.find('td.name');
		var form = row.find('.part-edit-form');
		
		if (cellName && form) {
			form.remove();
			cellName.text(row.attr('data-name'));
		}
	}

	function costume_part_hide_all_edit_part()
	{
		$('#parts-list .part-row').each(function() {
			costume_part_hide_edit_part($(this));
		});
	}
	
	$('#parts-list').delegate('.part-edit-form', 'submit', function(event) {
		event.preventDefault();
		var row = $(this).parents('.part-row').first();
		$('.edit-error', row).text('');
		gCostumePartXhrRequest = $.ajax({
			url: '/costume-admin/part/edit/'+$('input[name=id]', $(this)).val(),
			type:'POST',
			data:$(this).serialize(),
			dataType:'json',
			beforeSend: function () {
				$(this).mask(gTransLoading, 100);
			},
			complete: function () {
				$(this).unmask();
				gCostumePartXhrRequest = null;
			},
			success: function(data, textStatus) {
				if (data.status) {
					costume_part_hide_edit_part(row);
					var name = data.part.name;
					row.find('.name').text(name);
					row.attr('data-name', name);
				} else {
					var errorMsg = '<strong>'+data.message+'</strong><br/>';
					for (var field in data.errors) {
						errorMsg += field+'<ul>';
						for(var errmsg in data.errors[field]) {
							errorMsg += '<li>'+data.errors[field][errmsg]+'</li>';
						}
						errorMsg += '</ul>';
					}
					$('.edit-error', row).html(errorMsg);
				}
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('.edit-error', row).text(textStatus + ' ' + errorThrown).show();
			}
		});
		
		return false;
	});

	$('#parts-list').delegate('.part-edit-form button[name="cancel"]', 'click', function() {
		var row = $(this).parents('.part-row').first();
		costume_part_hide_edit_part(row);
		return false;
	});

	$('#parts-list').delegate('.part-edit-form input', 'keydown', function(event) {
		if (event.which == 27) {  // Esc
			var row = $(this).parents('.part-row').first();
			costume_part_hide_edit_part(row);
			return false;
		}
	});
	
	$('#parts-list .part-row').dblclick(function() {
		costume_part_hide_all_edit_part();
		costume_part_show_edit_part($(this));
	});


	$('#delete-part-dialog a.button-yes').click(function () {
		var part_id = $('#delete-part-dialog input[name=part_delete_id]').val();
		$.ajax({
			url: '/costume-admin/part/delete/'+part_id,
			type:'GET',
			dataType:'json',
			beforeSend: function () {
				$("#delete-part-dialog").mask(gTransLoading, 100);
			},
			complete: function () {
				$("#delete-part-dialog").unmask();
			},
			success: function(data, textStatus) {
				if (data.status) {
					var id = data.deleted_part_id;
					var row = $('#parts-list .part-row[data-id='+id+']');
					row.remove();
				}
				$('#delete-part-dialog').modal('hide');
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('#delete-part-dialog').modal('hide');
			}
		});

		return false;
	});
	$('#delete-part-dialog').modal({
		'show' : false
	});
	$('#parts-list').delegate('.delete-part', 'click', function() {
		$('#delete-part-dialog input[name=part_delete_id]').val($(this).parents('.part-row').first().attr('data-id'));
		$('#delete-part-dialog .delete-part-name').text($(this).parents('.part-row').first().attr('data-name'));
		$('#delete-part-dialog').modal('show');
		return false;
	});

});
