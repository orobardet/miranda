!function($) {

	"use strict";

	var MultipleTagSelector = function(element, options) {
		this.select = element;
		this.options = $.extend({}, $.fn.multipleTagSelector.defaults, $(this.select).data(), options);
		this.suspendOnChange = false;
		this.listContainer = null;
		this.init();
	};

	MultipleTagSelector.prototype = {
		constructor : MultipleTagSelector,
		init : function() {
			var that = this;
			
			this.listContainer = $(this.options.containerTemplate);
			if (this.options.containerId) {
				this.listContainer.attr('id', this.options.containerId); 
			}
			this.listContainer.insertBefore(this.select);
			
			$(this.select).on('change', function() {
				var $this = $(this);
				if (that.suspendOnChange) {
					return;
				}

				var value = $this.val();
				var label = $this.find("option:selected").text();

				var listContainer = $this.siblings('.search-list-container')
						.first();

				that.addItem(label, value);
				
				listContainer.append(label);

				that.suspendOnChange = true;
				if (that.options.isSelectPicker) {
					$this.data('selectpicker').val('').blur();
				} else {
					$this.val('').blur();
				}
				that.suspendOnChange = false;
			});
			
			this.listContainer.on('click', '.multiple-tag-selector-list-remove', function(e) {
				e.preventDefault();
				$(this).parent('.multiple-tag-selector-list-item').first().remove();
			});
			
			if (this.options.values && this.options.values.length) {
				$.each(this.options.values, function(index, data) {
					if (typeof data == 'object' && data.label && data.value) {
						that.addItem(data.label, data.value);
					} else {
						var option = that.getOption(data);
						if (option) {
							that.addItem(option.text(), data);
						}
					}
				});
			}
		},
	
		getOption: function(value) {
			var option = $(this.select).find('option[value='+value+']').first();
			if (option.length) {
				return option;
			}
			return false;
		},
		
		getTag: function(value) {
			var tag = this.listContainer.find('input[type=hidden][value='+value+']').first();
			if (tag.length) {
				return tag;
			}
			return false;
		},
		
		addItem: function(label, value) {
			if (!this.options.allowDuplicates) {
				if (this.getTag(value)) {
					return;
				}
			}
			var item = $(this.options.itemTemplate);
			if (this.options.name) {
				item.find('>input[type=hidden]').attr('name', this.options.name);
			}
			item.find('>input[type=hidden]').val(value);
			item.find('>span').text(label);
			
			item.appendTo(this.listContainer);
		}
	};

	$.fn.multipleTagSelector = function(option) {
		return this.each(function() {
			var $this = $(this);
			var data = $this.data('multipleTagSelector');
			var options = typeof option == 'object' && option;
			if (!data) {
				$this.data('multipleTagSelector',
						(data = new MultipleTagSelector(this, options)));
			}
			if (typeof option == 'string') {
				data[option]();
			}
		});
	};

	$.fn.multipleTagSelector.defaults = {
		isSelectPicker: false,
		name: null,
		containerId: null,
		values: null,
		allowDuplicates: false,
		containerTemplate: '<div class="multiple-tag-selector-list-container"></div>',
		itemTemplate: '<div class="multiple-tag-selector-list-item"><input type="hidden"/><span></span><a href="#" class="multiple-tag-selector-list-remove">x</a></div>'
	};
}(window.jQuery);