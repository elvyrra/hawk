(function(){
	window.advertMenuChanged = function(){
		app.advert('warning', Lang.get('admin.plugins-advert-menu-changed'));
		adverted = true;
	}
})();