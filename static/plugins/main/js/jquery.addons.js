(function($) {
	$.fn.serializeObject = function(){
		var o = {};
		var a = this.serializeArray();
		$.each(a, function() {
			if (o[this.name] !== undefined) {
				if (!o[this.name].push) {
					o[this.name] = [o[this.name]];
				}
				o[this.name].push(this.value || '');
			} else {
				o[this.name] = this.value || '';
			}
		});
		return o;
	};	

	$.fn.serializeObject = function(){
		var result = {};
		var array = this.serializeArray();
		$.each(array, function() {
			var match = /^(.+?)\[(.*?)\]+/.exec(this.name);
			debugger;
			// if (object[this.name] !== undefined) {
			// 	if (!o[this.name].push) {
			// 		o[this.name] = [o[this.name]];
			// 	}
			// 	o[this.name].push(this.value || '');
			// } 
			// else {
			// 	o[this.name] = this.value || '';
			// }
		});
		return result;
	};	
})(jQuery);
