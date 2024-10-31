(function($){
	
	$('body.pages_page_navz-page-tree .dashicons-plus-link').on('click', function(){
		var child;
		child = $(this).data('child');
		$('body.pages_page_navz-page-tree #item-' + child).slideToggle('fast');
		if( $(this).find('span').hasClass('dashicons-plus') ){
			$(this).find('span').removeClass('dashicons-plus');
			$(this).find('span').addClass('dashicons-minus');
		} else {
			$(this).find('span').removeClass('dashicons-minus');
			$(this).find('span').addClass('dashicons-plus');
		}
		return false;
	});	
		
	$('body.pages_page_navz-page-tree .subsubsub .button-primary').on('click', function(){
			items = $('body.pages_page_navz-page-tree ul.navz-parent').sortable('serialize');
			$('body.pages_page_navz-page-tree .subsubsub .button-primary').attr('disabled', true).text('Saving...');
			$.post(ajaxurl, {'action': 'ajax_navzPageTreeUpdateSortOrder', 'items': items}, function( data ){
				console.log( data );
				$('body.pages_page_navz-page-tree .subsubsub .button-primary').removeAttr('disabled').text('Save Changes');
			});	
			return false;
	});

 	$('body.pages_page_navz-page-tree ul.navz-parent').sortable({
  	axis: "y",
		containment: "parent",
		forcePlaceholderSize: true,
		items: " li",
		placeholder: "sortable-placeholder",
		revert: true,
		scroll: false,
		tolerance: 'pointer',
		cursorAt: { left: 5 },
		cursor: 'move',
		sort: function(e){
			$('body.pages_page_navz-page-tree .subsubsub .button-primary').removeAttr('disabled');
		},
	});
		
	$('body.pages_page_navz-page-tree .dashicons-trash-link').on('click', function(){
		var url, id;
		url = $(this).attr('href');
		id = $(this).attr('data-id');
		$('body.pages_page_navz-page-tree ul.navz-parent li#item_' + id).slideUp('fast').remove();
		$.post( url );
		return false;
	});
	
	$('button#navz-add-more-title').on('click', function(){
		$('#navz-multiple-fat-group').append('<div class="navz-multiple-fat-field-group"><input type="text" class="navz-multiple-fat-field" name="page-titles[]" placeholder="Enter title"/></div>');
		return false;
	});		
	
	$('#page-tree-add-multiple form').on('submit', function(){
		var form, data;
		form = $(this).serializeArray();
		$.post(ajaxurl, form, function( data ){
			alert( data );
		});
		return false;
	});
			
})( jQuery );





















