/**********************************************************************
 *    						global.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Mint's project.
 *
 *
 **********************************************************************/
var showmenu = false;

jQuery().ready(function(){     
    $("body").on('click', '[href]:not(.real-link):not([href^="#"])', function(e){
		var url = $(this).attr('href');
		if (url.match(/^javascript\:/)) {
			return true;
		}
		
		e.preventDefault();		
		var data = {};
		
		switch($(this).attr('target')) {
			case 'newtab' :
				// Load the page in a new tab of the application
				data = {newtab : true};
				page.load(url, data);
				break;
			
			case 'dialog' :
				// Open the url in a dialog box
				var options = {
					url : url,
					title : $(this).attr('title'),					
					width : $(this).data('width'),
					height: $(this).data('height'),
					modal : $(this).data('modal')
				}
				page.dialog(options);
				break;
			
			case '_blank' :
				// Load the whole page in a new browser tab
				window.open(url);
				break;
			
			case undefined :
			case '' :
				// Open the url in the current application tab
				page.load(url);
				break;
			
			default :
				// Open the url in a given DOM node, represented by it CSS selector
				page.load(url, {selector : $(this).attr('target')});
				break;
		}	
		
		return false;
	})
	
	.on('click', ".main-tabs-close", function(){
		tabset.remove($(this).data('tab'));
	});
});

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/