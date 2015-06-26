$("#settings-form-tabs .nav a:first").tab('show');
	
$("#settings-form [name='main.home-page-type']").change(function(){
	if($(this).is(':checked')){
		if ($(this).val() == 'custom') {
			$("#home-page-html").parent().slideDown();
			$("#home-page-item").parent().slideUp();
		}
		else{
			$("#home-page-html").parent().slideUp();
			$("#home-page-item").parent().slideDown();
		}
	}
}).trigger('change');

$("#settings-form [name='main.open-register']").change(function(){
	var nodes = $("#settings-form").find("[name='main.confirm-register-email'], [name='main.confirm-register-terms']");
	if($(this).is(':checked')){
		if ($(this).val() == '0') {
			nodes.parent().slideUp();
		}
		else{
			nodes.parent().slideDown();
		}
	}
}).trigger('change');