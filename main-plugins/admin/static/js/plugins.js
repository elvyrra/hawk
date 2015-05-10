(function(){
	window.advertMenuChanged = function(){
		mint.advert('warning', Lang.get('admin.plugins-advert-menu-changed'));
		adverted = true;
	}
})();