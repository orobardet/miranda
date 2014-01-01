function partsSelectorOnFieldEnterKey (event) {
	event.preventDefault();
	event.stopPropagation();
	console.log(event);
}

$(function() {
	$('.selectpicker').selectpicker();
	$('.spinner').each(function() {
		var $this = $(this);
		var input = $this.find('input').first();
		var options = {};
		
		var min = input.attr('min');
		if (min) {
			options.min = min;
		}
		var max = input.attr('max');
		if (max) {
			options.max = max;
		}
		var step = input.attr('step');
		if (step) {
			options.step = step;
		}
		$this.spinner(options);
	});
	
	// Composition
	function add_part(part) {
		if (part != '') {
			var listItem = $('#parts-list-item-template').clone().removeAttr('id');
			listItem.find('.part-label').text(part);
			listItem.find('input[name^=part]').val(part).removeAttr('disabled');
			
			$('#parts-list').append(listItem);
		}
	}
	$('#part-add').click(function() {
		var input = $('input[name=parts_selector]');
		var part = $.trim(input.val());
		if (part != '') {
			add_part(part);
			
			$('#input-parts-selector').combobox('toggle');
			input.val('').blur();
		}
	});

	$('#parts-list').delegate('.parts-list-item .btn-remove-part', 'click', function(event) {
		event.preventDefault();
		$(this).parents('.parts-list-item').first().remove();
	});
	
	$('#input-parts-selector').combobox({
		'onFieldEnterKey': function(event) {
			event.preventDefault();
			event.stopPropagation();
			if (event.type == 'keydown') {
				$('#part-add').trigger('click');
				$('input[name=parts_selector]').focus();
			}
			return false;
		}		
	});
	var parts = $('#parts-list').data('values');
	if (parts && parts.length) {
		for (var i = 0; i < parts.length; i++) {
		    add_part(parts[i]);
		}
	}
	
	// Etiquettes
	function add_tag(tag) {
		if (tag != '') {
			var listItem = $('#tags-list-item-template').clone().removeAttr('id');
			listItem.find('.tag-label').text(tag);
			listItem.find('input[name^=tag]').val(tag).removeAttr('disabled');
			
			$('#tags-list').append(listItem);
		}
	}
	$('#tag-add').click(function() {
		var input = $('input[name=tags_selector]');
		var tag = $.trim(input.val());
		if (tag != '') {
			add_tag(tag);
			
			$('#input-tags-selector').combobox('toggle');
			input.val('').blur();
		}
	});

	$('#tags-list').delegate('.tags-list-item .btn-remove-tag', 'click', function(event) {
		event.preventDefault();
		$(this).parents('.tags-list-item').first().remove();
	});
	
	$('#input-tags-selector').combobox({
		'onFieldEnterKey': function(event) {
			event.preventDefault();
			event.stopPropagation();
			if (event.type == 'keydown') {
				$('#tag-add').trigger('click');
				$('input[name=tags_selector]').focus();
			}
			return false;
		}		
	});
	var tags = $('#tags-list').data('values');
	if (tags && tags.length) {
		for (var i = 0; i < tags.length; i++) {
		    add_tag(tags[i]);
		}
	}
});
