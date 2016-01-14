$("#staffManager-myAbsence-page")

.on("change", "#myAbsences-filter-form", function(){
	$.cookie('staffManager-myAbsences-filter', app.forms['myAbsences-filter-form'].toString(), {expires : 86400 * 365});
	
	app.lists['my-absences-list'].refresh();
})

.on("change", "#myAbsences-filter-type-form", function(){
	$.cookie('staffManager-myAbsences-type-filter', app.forms['myAbsences-filter-type-form'].toString(), {expires : 86400 * 365});
	
	app.lists['my-absences-list'].refresh();
});

