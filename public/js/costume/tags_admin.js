$(function() {
	var gCostumeTagXhrRequest = null;
	
	function costume_tag_show_edit_tag(row)
	{
		if (!row) {
			return;
		}
		
		var cellName = row.find('td.name');
		
		if (cellName) {
			var tagName = row.attr('data-name');
			var tagId = row.attr('data-id');
			var tagForm = $('form#tag-edit-form-template').clone();
			tagForm.removeAttr('id');
			$('input[name="name"]', tagForm).val(tagName);
			$('input[name="id"]', tagForm).val(tagId);
			cellName.empty().prepend(tagForm);
			tagForm.show();
			$('input[name="name"]', tagForm).select();
		}
	}
	
	function costume_tag_hide_edit_tag(row)
	{
		if (gCostumeTagXhrRequest) {
			gCostumeTagXhrRequest.abort();
			gCostumeTagXhrRequest = null;
		}
		if (!row) {
			return;
		}
		
		var cellName = row.find('td.name');
		var form = row.find('.tag-edit-form');
		
		if (cellName && form) {
			form.remove();
			cellName.text(row.attr('data-name'));
		}
	}

	function costume_tag_hide_all_edit_tag()
	{
		$('#tags-list .tag-row').each(function() {
			costume_tag_hide_edit_tag($(this));
		});
	}
	
	$('#tags-list').delegate('.tag-edit-form', 'submit', function(event) {
		event.preventDefault();
		var row = $(this).parents('.tag-row').first();
		$('.edit-error', row).text('');
		gCostumeTagXhrRequest = $.ajax({
			url: '/costume-admin/tag/edit/'+$('input[name=id]', $(this)).val(),
			type:'POST',
			data:$(this).serialize(),
			dataType:'json',
			beforeSend: function () {
				$(this).mask(gTransLoading, 100);
			},
			complete: function () {
				$(this).unmask();
				gCostumeTagXhrRequest = null;
			},
			success: function(data, textStatus) {
				if (data.status) {
					costume_tag_hide_edit_tag(row);
					var name = data.tag.name;
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

	$('#tags-list').delegate('.tag-edit-form button[name="cancel"]', 'click', function() {
		var row = $(this).parents('.tag-row').first();
		costume_tag_hide_edit_tag(row);
		return false;
	});

	$('#tags-list').delegate('.tag-edit-form input', 'keydown', function(event) {
		if (event.which == 27) {  // Esc
			var row = $(this).parents('.tag-row').first();
			costume_tag_hide_edit_tag(row);
			return false;
		}
	});
	
	$('#tags-list .tag-row').dblclick(function() {
		costume_tag_hide_all_edit_tag();
		costume_tag_show_edit_tag($(this));
	});


	$('#delete-tag-dialog a.button-yes').click(function () {
		var tag_id = $('#delete-tag-dialog input[name=tag_delete_id]').val();
		$.ajax({
			url: '/costume-admin/tag/delete/'+tag_id,
			type:'GET',
			dataType:'json',
			beforeSend: function () {
				$("#delete-tag-dialog").mask(gTransLoading, 100);
			},
			complete: function () {
				$("#delete-tag-dialog").unmask();
			},
			success: function(data, textStatus) {
				if (data.status) {
					var id = data.deleted_tag_id;
					var row = $('#tags-list .tag-row[data-id='+id+']');
					row.remove();
				}
				$('#delete-tag-dialog').modal('hide');
			},
			error: function(jqXHR, textStatus, errorThrown ) {
				$('#delete-tag-dialog').modal('hide');
			}
		});

		return false;
	});
	$('#delete-tag-dialog').modal({
		'show' : false
	});
	$('#tags-list').delegate('.delete-tag', 'click', function() {
		$('#delete-tag-dialog input[name=tag_delete_id]').val($(this).parents('.tag-row').first().attr('data-id'));
		$('#delete-tag-dialog .delete-tag-name').text($(this).parents('.tag-row').first().attr('data-name'));
		$('#delete-tag-dialog').modal('show');
		return false;
	});

});
