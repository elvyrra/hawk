$("#project-main")

.on("click", ".delete-project", function(){
	if(confirm(Lang.get("ticket.delete-project-confirmation"))){
		$.get(app.getUri("ticket-project-delete", {projectId : $(this).data("project")}), function(){
			app.lists['ticket-project-list'].refresh();
		});
	}
});

$("#tickets-page")

.on("click", ".delete-ticket", function(){
	if(confirm(Lang.get("ticket.delete-ticket-confirmation"))){
		$.get(app.getUri("ticket-delete", {ticketId : $(this).data("ticket")}), function(){
			app.lists['ticket-list'].refresh();			
		});
	}
})

.on("change", "#ticket-filter-form", function(){
	$.cookie('ticket-filter', app.forms['ticket-filter-form'].toString(), {expires : 86400 * 365});
	
	app.lists['ticket-list'].refresh();
});
