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
	
	$('#part-add').click(function() {
		var input = $('input[name=parts_selector]');
		var part = $.trim(input.val());
		if (part != '') {
			var listItem = $('#parts-list-item-template').clone().removeAttr('id');
			listItem.find('.part-label').text(part);
			listItem.find('input[name^=part]').val(part).removeAttr('disabled');
			
			$('#parts-list').append(listItem).show();
			
			$('#input-parts-selector').combobox('toggle');
			input.val('').blur();
		}
	});

	$('#parts-list').delegate('.parts-list-item .btn-remove-part', 'click', function(event) {
		event.preventDefault();
		$(this).parents('.parts-list-item').first().remove();
		if (!$('#parts-list').children().size()) {
			$('#parts-list').hide();
		}
	});
});
