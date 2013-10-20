$(function() {
	var gCostumeMaterialXhrRequest = null;
	
	function costume_material_show_edit_material(row)
	{
		if (!row) {
			return;
		}
		
		var cellName = row.find('td.name');
		
		if (cellName) {
			var materialName = row.attr('data-name');
			var materialId = row.attr('data-id');
			var materialForm = $('form#material-edit-form-template').clone();
			materialForm.removeAttr('id');
			$('input[name="name"]', materialForm).val(materialName);
			$('input[name="id"]', materialForm).val(materialId);
			cellName.empty().prepend(materialForm);
			materialForm.show();
			$('input[name="name"]', materialForm).select();
		}
	}
	
	function costume_material_hide_edit_material(row)
	{
		if (gCostumeMaterialXhrRequest) {
			gCostumeMaterialXhrRequest.abort();
			gCostumeMaterialXhrRequest = null;
		}
		if (!row) {
			return;
		}
		
		var cellName = row.find('td.name');
		var form = row.find('.material-edit-form');
		
		if (cellName && form) {
			form.remove();
			cellName.text(row.attr('data-name'));
		}
	}

	function costume_material_hide_all_edit_material()
	{
		$('#materials-list .material-row').each(function() {
			costume_material_hide_edit_material($(this));
		});
	}
	
	$('#materials-list').delegate('.material-edit-form', 'submit', function(event) {
		event.preventDefault();
		var row = $(this).parents('.material-row').first();
		$('.edit-error', row).text('');
		gCostumeMaterialXhrRequest = $.ajax({
			url: '/costume-admin/material/edit/'+$('input[name=id]', $(this)).val(),
			type:'POST',
			data:$(this).serialize(),
			dataType:'json',
			beforeSend: function () {
				$(this).mask(gTransLoading, 100);
			},
			complete: function () {
				$(this).unmask();
				gCostumeMaterialXhrRequest = null;
			},
			success: function(data, textStatus) {
				if (data.status) {
					costume_material_hide_edit_material(row);
					var name = data.material.name;
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

	$('#materials-list').delegate('.material-edit-form button[name="cancel"]', 'click', function() {
		var row = $(this).parents('.material-row').first();
		costume_material_hide_edit_material(row);
		return false;
	});

	$('#materials-list').delegate('.material-edit-form input', 'keydown', function(event) {
		if (event.which == 27) {  // Esc
			var row = $(this).parents('.material-row').first();
			costume_material_hide_edit_material(row);
			return false;
		}
	});
	
	$('#materials-list .material-row').dblclick(function() {
		costume_material_hide_all_edit_material();
		costume_material_show_edit_material($(this));
	});


	$('#delete-material-dialog a.button-yes').click(function () {
		var material_id = $('#delete-material-dialog input[name=material_delete_id]').val();
		$.ajax({
			url: '/costume-admin/material/delete/'+material_id,
			type:'GET',
			dataType:'json',
			beforeSend: function () {
				$("#delete-material-dialog").mask(gTransLoading, 100);
			},
			complete: function () {
				$("#delete-material-dialog").unmask();
			},
			success: function(data, textStatus) {
				if (data.status) {
					var id = data.deleted_material_id;
					var row = $('#materials-list .material-row[data-id='+id+']');
					row.remove();
				}
				$('#delete-material-dialog').modal('hide');
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('#delete-material-dialog').modal('hide');
			}
		});

		return false;
	});
	$('#delete-material-dialog').modal({
		'show' : false
	});
	$('#materials-list').delegate('.delete-material', 'click', function() {
		$('#delete-material-dialog input[name=material_delete_id]').val($(this).parents('.material-row').first().attr('data-id'));
		$('#delete-material-dialog .delete-material-name').text($(this).parents('.material-row').first().attr('data-name'));
		$('#delete-material-dialog').modal('show');
		return false;
	});

});
