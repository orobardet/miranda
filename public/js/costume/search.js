$(function() {
	$('#more-search-form').click(function(e) {
		e.preventDefault();

		var $fullForm = $('#full-search-form');
		var $moreSearchButton = $('#more-search-form');
		
		if ($fullForm.is(':visible')) {
			$fullForm.hide();
			$moreSearchButton.removeClass('active');
		} else {
			$fullForm.show();
			$moreSearchButton.addClass('active');
		}
		$moreSearchButton.blur();
	});

	var costumeCompletion = new Bloodhound({
		remote: {
			url: costumeSearchCmplUrl+'?q=%QUERY&max=8',
			filter: function(parsedResponse) {
				return parsedResponse.results;
			},
			ajax: {
				beforeSend: function() {
					$('#small-search-form-completion-loader').show();
				},
				complete: function() {
					$('#small-search-form-completion-loader').hide();
				}
			}
		},
		limit: 8,
		datumTokenizer: function(d) { 
		    return Bloodhound.tokenizers.whitespace(d.val); 
		},
		queryTokenizer: Bloodhound.tokenizers.whitespace
	});

	costumeCompletion.initialize();

	$('#input-q').typeahead({
			minLength: 2
		}, {
			name: 'costume-completion',
			source: costumeCompletion.ttAdapter(),
		 	displayKey: 'label',
			templates: {
				suggestion: Handlebars.compile([
				    '<div class="costume-suggestion">',
					'<div class="costume-code">{{code}}</div>',
					'<div class="costume-label">{{label}}</div>',
					'</div>'
					].join(''))
		}
	}).on('typeahead:selected', function(typeahead, costume, name) {
		window.location.href = costumeShowUrl+'/'+costume.id;
	});
	$('#small-search-form-completion-loader').appendTo($('#small-search-form > .twitter-typeahead'));

	$('.selectpicker').selectpicker();
	
	$('#input-parts-selector').multipleTagSelector({
		isSelectPicker: true,
		name: 'parts[]',
		containerId: 'search-parts-list'
	});
	$('#input-tags-selector').multipleTagSelector({
		isSelectPicker: true,
		name: 'tags[]',
		containerId: 'search-tags-list'
	});
	
	$('#costume-search-form').submit(function(e) {
		console.log('Desactivation des input vides');
		$(this).find(":input").attr('readonly', 'readonly');
		$(this).find("button[type=submit]").attr('disabled', 'disabled').children('i').removeClass().addClass('icon-refresh icon-spin');
		$(this).find(":input").filter(function(){ return !this.value; }).attr('disabled', 'disabled');
		
		return true;
	});
});
