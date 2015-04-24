/**
 * Edit a language
 * */
$(".edit-lang").click(function(){
	var tag = $("#language-filter-form [name='tag']").val();
	mint.dialog(mint.getUri('LanguageController.editLanguage', {tag : tag}));
});

/**
 * Delete a language
 */
$(".delete-lang").click(function(){
	if (confirm(Lang.get('language.confirm-delete-lang'))) {
		var tag = $("#language-filter-form [name='tag']").val();
		$.get(mint.getUri('LanguageController.deleteLanguage', {tag : tag}), function(response){
			mint.load(mint.getUri('LanguageController.index'));
		});
	}
});

/**
 * Edit a translation key
 */
$("#language-manage-page")

/**
 * Delete a translation key
 */
.on("click", ".delete-key", function(){
	if (confirm(Lang.get('language.confirm-delete-key'))) {		
		$.get(mint.getUri('LanguageController.deleteKey', {keyId : $(this).data('key')}), function(response){
			mint.lists["language-key-list"].refresh();
		});
	}

});

var form = mint.forms["language-filter-form"];
	
form.submit = function(){
	var data = JSON.stringify($(this.node).serializeObject());
	mint.load(mint.getUri('LanguageController.listKeys') + '?filters=' + data, {selector : $("#language-key-list").parent()});
	return false;
};

form.node
.on("change", "[name='tag'], [name='keys'], [name='selected']", function(){
	form.submit();		
});