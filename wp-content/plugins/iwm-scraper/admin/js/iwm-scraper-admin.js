(function( $ ) {
	'use strict';
	$(document).ready( function(){
		$('#iwm_importCategories').on( 'click', function(){
			$(this).attr('disabled', 'disabled');
			iwm_importCategories();
		} );
	} );

	function iwm_importCategories(){
		$.ajax({
			url : categories.ajaxurl,
			data : { action: "iwm_import_categories" },
			success: function( response ) {
				iwm_displayCategories( response );
			}
		});
	}

	function iwm_displayCategories( response ){
		if( response.error == true ){
			$('#iwm_categories_response').html( '<div class="error settings-error notice is-dismissible"><p>Error retrieving categories. Error message is: <strong>'+response.message+'</strong></p></div>' );
		} else {
			//add html form
			var categories_form = '<form method="post" name="iwm_save_categories" action="options.php">';

			categories_form += '<table class="wp-list-table widefat fixed striped tags">';
			categories_form += '<thead>';
			categories_form += '<tr>';
			categories_form += '<td class="manage-column" scope="col">Name</td>';
			categories_form += '<td class="manage-column" scope="col">Slug</td>';
			categories_form += '<td class="manage-column" scope="col">Type</td>';
			categories_form += '<td class="manage-column" scope="col">Actions</td>';
			categories_form += '</tr>';
			categories_form += '</thead>';

			$.each( response.categories, function( slug, category ){
				categories_form += iwm_displayCategoryItem( category, slug, 0, 0 );
			} );

			categories_form += '</table>';
			categories_form += '<input type="button" name="save_categories" id="save_categories" class="button button-primary" value="Save categories">';
			categories_form += '</form>';
			$('#iwm_categories_response').html( categories_form );

			iwm_enableCategoryButtons();
		}
	}

	function iwm_saveCategories(){
		var categories_list = {};
		$('tr.iwm-category-item').each( function(index){
			var category_item = {};
			category_item['orig_url'] = $(this).attr('fullurl');
			category_item['name'] = $('td.iwm-category-name', $(this)).html();
			category_item['slug'] = $('td.iwm-category-slug', $(this)).html();
			category_item['type'] = $('td.iwm-category-type', $(this)).html();
			category_item['parent'] = '';
			category_item['parent_identifier'] = '';
			if( $(this).data('parent') != '0' ){
				if( $('tr#'+$(this).data('parent')).length > 0 ){
					var parent = $(this).data('parent');
					category_item['parent'] = $('td.iwm-category-slug', $('tr#'+parent) ).html();

					category_item['parent_identifier'] = $('tr#'+parent).attr('fullurl');
				}
			}
			categories_list[category_item['orig_url']] = category_item;
		} );
		$.ajax({
			url : categories.ajaxurl,
			method : 'POST',
			data : { action: "iwm_save_categories", ctgs : JSON.stringify(categories_list) },
			success: function( response ) {
				iwm_displaySaveCategoriesStatus( response );
			}
		});
		console.log(categories);
	}

	function iwm_displaySaveCategoriesStatus( response ){
		if( response.error == true ){

		} else {
			$.each( response.success_saved, function( id, orig_url){
				console.log(orig_url);
				$("tr[fullurl='"+orig_url+"']").data('saved', 1).addClass('iwm_item_saved');
			} );

			$.each( response.failed_saved, function( id, orig_url){
				$("tr[fullurl='"+orig_url+"']").data('saved', 0).addClass('iwm_item_failed');
			} );
		}

	}

	function iwm_enableCategoryButtons(){
		$('#save_categories').on( 'click', function(){
			$(this).attr('disabled', 'disabled');
			var isGood=confirm("Are you sure you want to save everything? Make sure you won't have duplicates");
			if( isGood ) {
				iwm_saveCategories();
			}
		});
		$('.iwm-edit-btn').on( 'click', function(){
			var edit_type = $(this).data('type');

			if( edit_type == 'edit' ){
				$(this).data('type', 'save');
				$(this).html('Save');
				iwm_categoryEditStart( $(this).data('slug') );
			} else {
				if( iwm_categoryEditStop( $(this).data('slug') )){
					$(this).data('type', 'edit');
					$(this).html('Edit');
				}
			}
		} );
		$('.iwm-delete-btn').on( 'click', function(){
			var isGood=confirm("Are you sure you want to delete "+$(this).data('slug')+" and all it's subcategories?");
			if( isGood ){
				var slug = $(this).data('slug');
				var tr_item = $('tr#'+slug);
				var start_fullurl = tr_item.attr('fullurl');
				$('tr#'+$(this).data('slug')).remove();
				$("tr[fullurl^='"+start_fullurl+"']").remove();
			}
		});
	}

	function iwm_categoryEditStop( slug ){
		var tr_item = $('tr#'+slug);
		if( tr_item.length <1 ) return;

		var category_name = $('input[name=iwm-category-name]', tr_item).val();
		if( category_name.length < 1 ) return false; //must make it red
		var category_slug = $('input[name=iwm-category-slug]', tr_item).val();
		if( category_slug.length < 1 ) return false; //must make it red

		var category_type = $('select[name=iwm-category-type]', tr_item).val();

		$('td.iwm-category-name', tr_item).html(category_name);
		$('td.iwm-category-slug', tr_item).html(category_slug);
		$('td.iwm-category-type', tr_item).html(ucFirst(category_type));

		var start_fullurl = tr_item.attr('fullurl');

		$("tr[fullurl^='"+start_fullurl+"']").each(function( index ){
			$('td.iwm-category-type', this).html(ucFirst(category_type));
		})

		tr_item.data('open',0);

		return true;
	}

	function iwm_categoryEditStart( slug ){
		var tr_item = $('tr#'+slug);
		if( tr_item.length <1 ) return;

		$('td.iwm-category-name', tr_item).html( '<input type="text" name="iwm-category-name" value="'+$('td.iwm-category-name', tr_item).html()+'" />' );
		$('td.iwm-category-slug', tr_item).html( '<input type="text" name="iwm-category-slug" value="'+$('td.iwm-category-slug',tr_item).html()+'" />' );

		var iwm_select = $('<select>').attr('name', 'iwm-category-type');
		iwm_select.append( $('<option>').attr('value','page').text('Page') );
		iwm_select.append( $('<option>').attr('value','category').text('Category') );

		var iwm_selected_item = $('td.iwm-category-type', tr_item).html();
		iwm_select.val( iwm_selected_item.toLowerCase() );
		$('td.iwm-category-type', tr_item).html( iwm_select );

		tr_item.data('open',1);

	}

	function iwm_displayCategoryItem( category, slug, type, parent_slug ){
		var html = '';
		var is_subcategory = false;
		var category_type_class = 'iwm-category';
		if( type > 0 ){
			is_subcategory =true;
			category_type_class = 'iwm-subcategory';
		} else {
			type = 1;
		}
		html += '<tr class="iwm-category-item '+category_type_class+' iwm-category-level-'+type+'" data-parent="'+parent_slug+'" id="'+slug+'" data-open="0" fullurl="'+category.details.url+'">';
		html += '<td class="iwm-category-name">'+category.details.name+'</td>';
		html += '<td class="iwm-category-slug">'+slug+'</td>';
		html += '<td class="iwm-category-type">Category</td>';
		html += '<td class="iwm-category-buttons"><a class="iwm-edit-btn" data-type="edit" data-slug="'+slug+'">Edit</a> | <a class="iwm-delete-btn" data-slug="'+slug+'">Delete</a></td>';
		html += '</tr>';

		if( typeof category.subcategories != 'undefined' ){
			$.each( category.subcategories, function( s_slug, s_category ){
				html += iwm_displayCategoryItem( s_category, s_slug, type+1, slug )
			});
		}

		return html;
	}

	function ucFirst(string) {
		return string.substring(0, 1).toUpperCase() + string.substring(1).toLowerCase();
	}

})( jQuery );
