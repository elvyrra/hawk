(function(){
	window.advertMenuChanged = function(){
		app.notify('warning', Lang.get('admin.plugins-advert-menu-changed'));
		adverted = true;
	}

    $(".delete-plugin").click(function(){
        if(!confirm(Lang.get('admin.confirm-delete-plugin'))){
            return false;
        }
    });

    $(".uninstall-plugin").click(function(){
       if(!confirm(Lang.get('admin.confirm-uninstall-plugin'))){
            return false;
        } 
    })
})();