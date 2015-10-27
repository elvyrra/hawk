$("#project-main")

.on("click", ".delete-project", function(){
	if(confirm(Lang.get("ticket.delete-project-confirmation"))){
		$.get(app.getUri("ticket-project-delete", {projectId : $(this).data("project")}), function(){
			app.load(app.getUri("ticket-project-list"), {selector : "#project-main"});
		});
	}
});

$("#ticket")

.on("click", ".delete-ticket", function(){
	if(confirm(Lang.get("ticket.delete-ticket-confirmation"))){
		$.get(app.getUri("ticket-delete", {ticketId : $(this).data("ticket")}), function(){
			app.load(app.getUri("ticket-list"), {selector : "#ticket"});
		});
	}
});



