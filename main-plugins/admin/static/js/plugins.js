(function(){
	window.advertMenuChanged = function(){
		app.notify('warning', Lang.get('admin.plugins-advert-menu-changed'));
		adverted = true;
	}
})();