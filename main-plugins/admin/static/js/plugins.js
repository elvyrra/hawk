(function(){
	window.advertMenuChanged = function(){
		app.notify('warning', Lang.get('admin.plugins-advert-menu-changed'));
		adverted = true;
	}

    /**
     * Remove a plugin
     */
    $(".delete-plugin").click(function(){
        if(!confirm(Lang.get('admin.confirm-delete-plugin'))){
            return false;
        }
    });

    /**
     * Uninstall a plugin
     */
    $(".uninstall-plugin").click(function(){
       if(!confirm(Lang.get('admin.confirm-uninstall-plugin'))){
            return false;
        } 
    });


    /**
     * Search plugins from the sidebar widget
     */
    app.forms["search-plugins-form"].submit = function(){
        if(this.isValid()){
            app.load(app.getUri('search-plugins') + '?' + $(this.node).serialize());
        }
        else{
            this.displayErrorMessage(Lang.get('form.error-fill'));
        }
        return false;
    };
})();