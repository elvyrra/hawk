/**
 * Edit a language
 * */
$(".edit-lang").click(function(){
	var tag = $("#language-filter-form [name='tag']").val();
	app.dialog(app.getUri('LanguageController.editLanguage', {tag : tag}));
});

/**
 * Delete a language
 */
$(".delete-lang").click(function(){
	if (confirm(Lang.get('language.confirm-delete-lang'))) {
		var tag = $("#language-filter-form [name='tag']").val();
		$.get(app.getUri('LanguageController.deleteLanguage', {tag : tag}), function(response){
			app.load(app.getUri('LanguageController.index'));
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
.on("click", ".delete-translation", function(){
	var tag = $("#language-filter-form [name='tag']").val();
	var data = $(this).data('key').split('.');
	var plugin = data[0];
	var key = data[1];
	debugger;
	$.get(app.getUri('delete-translation', {plugin : plugin, key : key, tag : tag}), function(response){
		app.lists["language-key-list"].refresh();
	});
});

var form = app.forms["language-filter-form"];
	
form.submit = function(){
	var data = JSON.stringify($(this.node).serializeObject());
	app.load(app.getUri('LanguageController.listKeys') + '?filters=' + data, {selector : $("#language-key-list").parent()});
	return false;
};

form.node
.on("change", "[name='tag'], [name='keys'], [name='selected']", function(){
	form.submit();		
});