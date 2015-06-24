$("#manage-themes-page")

.on("click", ".select-theme", function(){
    if(confirm(Lang.get("admin.theme-update-reload-page-confirm"))){
    	$.get(app.getUri("select-theme", {name : $(this).data("theme")}), function(){
            location.reload();
    	});
    }        
})

.on("click", ".delete-theme", function(){
	if(confirm(Lang.get("admin.theme-delete-confirm"))){
		$.get(app.getUri("delete-theme", {name : $(this).data("theme")}), function(){
			app.load(app.getUri("available-themes"), {selector: "#admin-themes-select-tab"});
		})
	}
})

.on("change", "#custom-theme-form", function(){
    var name = $(this).attr('name');
})

.on("click", ".delete-theme-media", function(){
	if(confirm(Lang.get("admin.theme-delete-media-confirm"))){
		$.get(app.getUri("delete-theme-media", {filename : $(this).data('filename')}), function(){
			app.load(app.getUri("theme-medias"), {selector : "#admin-themes-medias-tab"});
		});
	}
})

.on("focus", ".theme-media-url", function(){
    $(this).select();
});


/***
 * Ace editor for Css editing tab
 */
zrequire(["ace/ace.js"], function(){
	ace.config.set("modePath", app.jsBaseUrl + "ace/");
	ace.config.set("workerPath", app.jsBaseUrl + "ace/") ;
	ace.config.set("themePath", app.jsBaseUrl + "ace/"); 

	var editor = ace.edit("theme-css-edit");
	editor.setTheme("ace/theme/chrome");
	editor.getSession().setMode("ace/mode/css");
	editor.setShowPrintMargin(false);

	editor.getSession().on("change", function(event){
		var value = editor.getValue();
		$('#editing-css-computed').text(value);
		$("#theme-css-form [name='css'").val(value);
	});	
});

$("#theme-css-form").on("success", function(event, data){
	$("#theme-custom-stylesheet").attr('href', data.href);
});
