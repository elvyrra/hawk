(function(){
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

    /** 
     * Download a plugin from the platform
     */
    $(".download-plugin").click(function(){
        app.loading.start();

        $.get($(this).attr('href')).success(function(response){
            app.load(location.hash.substr(2));
        })

        .error(function(xhr, status, error){
            app.loading.stop();
            app.notify('error', error.message);
        });

        return false;
    });    
})();