$(function() {
	var gCostumeColorXhrRequest = null;
	var gCostumeColorReorderXhrRequest = null;
	
	function costume_color_show_edit_color(color_row) {
		costume_color_hide_add_color();
		$("#edit-color").unmask();
		$('#edit-color .edit-error').hide().html('');
		$('#edit-color input[name=id]').val(color_row.attr('data-id')).focus();
		$('#edit-color input[name=name]').val(color_row.attr('data-name'));
		$('#edit-color-picker').colorpicker('setValue', '#'+color_row.attr('data-color'));
		$('#edit-color-picker').colorpicker('update');
		$('#edit-color').show();
		$('#edit-color input[name=name]').focus();
	}
	
	function costume_color_hide_edit_color() {
		$('#colors-list .color-row').removeClass('info');
		if (gCostumeColorXhrRequest) {
			gCostumeColorXhrRequest.abort();
			gCostumeColorXhrRequest = null;
		}
		$('#edit-color').hide();
		$('#edit-color input[name=id]').val('');
		$('#edit-color input[name=name]').val('');
		$('#edit-color-picker').colorpicker('setValue', '#000000');
		$('#edit-color .edit-error').hide().html('');
	}
	
	function costume_color_show_add_color() {
		costume_color_hide_edit_color();
		$("#add-color").unmask();
		$('#add-color .add-error').hide().html('');
		$('#add-color input[name=name]').val('');
		$('#add-color-picker').colorpicker('setValue', '#000000');
		$('#add-color-picker').colorpicker('update');
		$('#add-color').show();
		$('#add-color input[name=name]').focus();
	}
	
	function costume_color_hide_add_color() {
		if (gCostumeColorXhrRequest) {
			gCostumeColorXhrRequest.abort();
			gCostumeColorXhrRequest = null;
		}
		$('#add-color').hide();
		$('#add-color input[name=name]').val('');
		$('#add-color-picker').colorpicker('setValue', '');
		$('#add-color .add-error').hide().html('');
	}

	$('.color-picker').colorpicker();
	// Mise à jour du colorpicker si on modifie la valeur à la main dans le champ
	$('.color-picker input').each(function() {
		   var elem = $(this);
		   elem.data('oldVal', elem.val());
		   elem.bind("propertychange keyup input paste", function(event){
		      if (elem.data('oldVal') != elem.val()) {
		       elem.data('oldVal', elem.val());
		       colorpicker = elem.parents('.color-picker').first();
		       colorpicker.colorpicker('setValue', '#'+elem.val().replace(/^#/, ''));
		       colorpicker.colorpicker('update');

		     }
		   });
		 });
	
	$('#colors-list').sortable({ 
		axis: 'y',
		containment: 'parent',
		handle: '.sort-handle',
		items: ' > tbody > tr',
		placeholder: 'table-sort-placeholder',
		tolerance: 'pointer',
		helper: function(event, element) {
			var helper = element.clone().addClass('table-sort-helper');
			helper.find('td.name').width(element.find('td.name').width());
			return helper;
		},
		update: function( event, ui ) {
			var color_order = [];
			$('#colors-list .color-row').each(function () {
				color_order.push($(this).attr('data-id'));
			});
			if (gCostumeColorReorderXhrRequest) {
				gCostumeColorReorderXhrRequest.abort();
				gCostumeColorReorderXhrRequest = null;
			}
			gCostumeColorReorderXhrRequest = $.ajax({
				url: '/costume-admin/color/reorder',
				type:'POST',
				data:{order: JSON.stringify(color_order)},
				dataType:'json',
				beforeSend: function () {
					$("#colors-list").mask(gTransLoading, 100);
				},
				complete: function () {
					$("#colors-list").unmask();
					gCostumeColorReorderXhrRequest = null;
				}
			});
		}
	});

	$('#colors-list').delegate('.color-row', 'click', function() {
		if ($(this).hasClass('info')) {
			costume_color_hide_edit_color();
		} else {
			$('#colors-list .color-row.info').removeClass('info');
			$(this).addClass('info');
			costume_color_show_edit_color($(this));
		}
	});
	
	$('#edit-color a.cancel-link').click(function() {
		costume_color_hide_edit_color();
	});
	
	$('#edit-color').submit(function() {
		$('#edit-color .edit-error').hide().html('');
		gCostumeColorXhrRequest = $.ajax({
			url: '/costume-admin/color/edit/'+$('#edit-color input[name=id]').val(),
			type:'POST',
			data:$('#edit-color').serialize(),
			dataType:'json',
			beforeSend: function () {
				$("#edit-color").mask(gTransLoading, 100);
			},
			complete: function () {
				$("#edit-color").unmask();
				gCostumeColorXhrRequest = null;
			},
			success: function(data, textStatus) {
				if (data.status) {
					costume_color_hide_edit_color();
					var id = data.color.id;
					var name = data.color.name;
					var color = data.color.color;
					var row = $('#colors-list .color-row[data-id='+id+']');
					row.find('.name').html(name);
					row.find('.color').css('background-color', '#'+color).attr('title', '#'+color);
					row.attr('data-name', name);
					row.attr('data-color', color);
				} else {
					var errorMsg = '<strong>'+data.message+'</strong><br/>';
					for (var field in data.errors) {
						errorMsg += field+'<ul>';
						for(var errmsg in data.errors[field]) {
							errorMsg += '<li>'+data.errors[field][errmsg]+'</li>';
						}
						errorMsg += '</ul>';
					}
					$('#edit-color .edit-error').html(errorMsg).show();
				}
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('#edit-color .edit-error').html(textStatus + ' ' + errorThrown).show();
			}
		});
		return false;
	});
	
	$('#add-color-link').click(function () {
		costume_color_show_add_color();
		return false;
	});
	
	$('#add-color a.cancel-link').click(function() {
		costume_color_hide_add_color();
	});
	
	$('#delete-color-dialog a.button-yes').click(function () {
		var color_id = $('#delete-color-dialog input[name=color_delete_id]').val();
		$.ajax({
			url: '/costume-admin/color/delete/'+color_id,
			type:'GET',
			dataType:'json',
			beforeSend: function () {
				$("#delete-color-dialog").mask(gTransLoading, 100);
			},
			complete: function () {
				$("#delete-color-dialog").unmask();
			},
			success: function(data, textStatus) {
				if (data.status) {
					var id = data.deleted_color_id;
					var row = $('#colors-list .color-row[data-id='+id+']');
					row.remove();
				}
				$('#delete-color-dialog').modal('hide');
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('#delete-color-dialog').modal('hide');
			}
		});

		return false;
	});
	$('#delete-color-dialog').modal({
		'show' : false
	});
	$('#colors-list').delegate('.delete-color', 'click', function() {
		$('#delete-color-dialog input[name=color_delete_id]').val($(this).parents('.color-row').first().attr('data-id'));
		$('#delete-color-dialog .delete-color-name').text($(this).parents('.color-row').first().attr('data-name'));
		$('#delete-color-dialog .delete-color-preview').css('background-color', '#'+$(this).parents('.color-row').first().attr('data-color'));
		$('#delete-color-dialog').modal('show');
		return false;
	});
	
	$('#add-color').submit(function() {
		$('#add-color .edit-error').hide().html('');
		gCostumeColorXhrRequest = $.ajax({
			url: '/costume-admin/color/add',
			type:'POST',
			data:$('#add-color').serialize(),
			dataType:'json',
			beforeSend: function () {
				$("#add-color").mask(gTransLoading, 100);
			},
			complete: function () {
				$("#add-color").unmask();
				gCostumeColorXhrRequest = null;
			},
			success: function(data, textStatus) {
				if (data.status) {
					costume_color_hide_add_color();
					var id = data.color.id;
					var name = data.color.name;
					var color = data.color.color;
					var row = $(gColorRowTemplate);
					row.attr('data-id', id);
					row.attr('data-name', name);
					row.attr('data-color', color);
					row.find('.name').html(name);
					row.find('.color').css('background-color', '#'+color).attr('title', '#'+color);
					$('#colors-list > tbody').append(row);
				} else {
					var errorMsg = '<strong>'+data.message+'</strong><br/>';
					for (var field in data.errors) {
						errorMsg += field+'<ul>';
						for(var errmsg in data.errors[field]) {
							errorMsg += '<li>'+data.errors[field][errmsg]+'</li>';
						}
						errorMsg += '</ul>';
					}
					$('#add-color .add-error').html(errorMsg).show();
				}
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('#add-color .add-error').html(textStatus + ' ' + errorThrown).show();
			}
		});
		return false;
	});
});
