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
});
