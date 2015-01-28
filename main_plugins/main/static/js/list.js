/**********************************************************************
 *    						ItemList.js
 *
 *
 * Author:   Julien Thaon & Sebastien Lecocq 
 * Date: 	 Jan. 01, 2014
 * Copyright: ELVYRRA SAS
 *
 * This file is part of Beaver's project.
 *
 *
 **********************************************************************/
page.lists = {};

/****** RAFRAICHISSEMENT DE LA LISTE AVEC LES NOUVELLES DONNÉES **************************/
/*** param: 	
		file-> fichier de recherche, le fichier courant
		nom -> le nom de la liste
		condition -> les recherches tapées 
		tri -> les tris dans les différentes colonnes
		nbLignes -> le nombre de lignes à afficher
		noPage -> le numéro de la page des résultats à afficher
****/

$(document)

/*_______________________________________________________________
		
		Select / unselect all the checkboxes of a list
_______________________________________________________________*/
.on("change", ".list-checkbox-all",function(){
	var list = $(this).data('list');
	$("#"+list).find(".list-checkbox").attr('checked',$(this).is(":checked")).trigger("change");
})

.on("change", ".list-checkbox", function(){
	var list = $(this).data('list');
	$("#"+list).find(".list-checkbox-all").attr("checked", $("#"+list).find(".list-checkbox").are(":checked"));
})


/*_______________________________________________________________
		
		Change the number of displayed lines
_______________________________________________________________*/
.on("change", ".list-max-lines",function(){
	// récupération du nom de la liste et du fichier de recherche de la liste	
	page.lists[$(this).data('list')].set({lines : $(this).val()});	
})


/*_______________________________________________________________
		
		Change the number of the displayed page
_______________________________________________________________*/
.on("change", ".list-page-number", function(){
	var list = $(this).data('list');
	
	var number = $(this).val();
	if(!isNaN(number) && number >= 1  && number <= parseInt($(".list-max-pages[data-list='"+list+"']").text())){
		page.lists[list].set({page : number});		
	}
	else
		$(this).focus();
})

/*_______________________________________________________________
		
		Go to the previous page
_______________________________________________________________*/
.on("mousedown", ".list-previous-page",function(event){	
	var list = $(this).attr('data-list');
	$(".list-page-number[data-list='"+list+"']")
		.val(function(){return parseInt($(this).val()) - 1 ;})
		.trigger("change");	
})

/*_______________________________________________________________
		
		Go to the next page
_______________________________________________________________*/
.on("mousedown", ".list-next-page", function(){
	var list = $(this).attr('data-list');
	$(".list-page-number[data-list='"+list+"']")
		.val(function(){return parseInt($(this).val()) + 1 ;})
		.trigger("change");	
})


/*_______________________________________________________________
		
		Sort the list
_______________________________________________________________*/
.on("click", ".list-sort-column", function(){
	/*** récupération du nom de la liste, du champ de tri, et du type de tri demandé ***/
	var list = $(this).data('list');
	var field = $(this).data('field');
	var value = $(this).attr('value');
	if(value == "0")
		delete(page.lists[list].sorts[field]);
	else
		page.lists[list].sorts[field] = value;
		
	page.lists[list].refresh();
})


/*_______________________________________________________________
		
		Type a research
_______________________________________________________________*/
.on("click", ".list-go-search", function(){
	$(this).next(".list-search-input").focus();
})

.on("keyup",".list-search-input", function(event){	
	/*** 
		When a user types a research, he has 400ms left to send the request, 
		to avoid multiple request for one research
	***/
	clearTimeout(page.lists._timer);// Stop the previous research timeout		
	page.lists._active = $(this);
	
	page.lists._timer = setTimeout(function(){// attente de lancement de la recherche, au cas où l'utilisateur tape un autre caractère
		var list = $(page.lists._active).data("list");
		var field = $(page.lists._active).data('field');
		
		page.lists[list].searches[field] = $(page.lists._active).val();
		page.lists._active = ".list-search-input[data-list='"+list+"'][data-field='"+field+"']";
		page.lists[list].refresh();		
	},300);    
})

/*_______________________________________________________________
		
		Empty a search input
_______________________________________________________________*/
.on("click", ".list-search-empty", function(){
	$(this).prev(".list-search-input").val("").trigger("keyup");
})


/*_______________________________________________________________
		
		highlight a line of the list on hover
_______________________________________________________________*/
.on("mouseover",".list-line", function(){
	var weight = $(this).css("font-weight");
	$(this).addClass("ui-state-focus").css("font-weight", weight);
})

.on("mouseout", ".list-line", function(){
	var weight = $(this).css("font-weight");
	$(this).removeClass("ui-state-focus").css("font-weight", weight)
})

.on("dblclick", ".list-line", function(){
	var script = $(this).find(".list-cell-clickable").first().attr("onclick");
	if(script)
		eval(script);
})

var ItemList = function(data){
	for(var i in data)
		this[i] = data[i];	
};

/**** Display a list ****/
ItemList.prototype.display = function(force){	
	var data = {
		searches: JSON.stringify(this.searches),
		sort : JSON.stringify(this.sorts),
		linesNumber: this.lines,
		pageNumber: this.page,		
	};	
	
	if(force){
		if((typeof(force) == "array" && !force.length) || (typeof(force) == "object" && !Object.keys(force).length))
			force = "";
		data["set-"+this.id] = force;	
	}
	
	this.file += this.selected ? ((this.file.match(/\?/) ? "&" : "?")+"selected="+this.selected) : "";
	var list = this;
    $.ajax({
        async : false,
        url: this.file,
        type: 'POST',
        data: data,
        cache : false,
        success:function(response){            
            $("#"+list.id).parent().html(response);
            if($(page.lists._active).length){				
				$(page.lists._active).focus();
				$(page.lists._active)[0].setSelectionRange($(page.lists._active).val().length,$(page.lists._active).val().length);	// Place the caret at the end of the input
				delete(page.lists._active);
            }			
        },
        error:function(XMLHttpRequest,textStatus, errorThrown){
            page.advert("error", Lang.get("main.refresh-list-error"));		
        }
    });
    return false;
};

ItemList.prototype.refresh = function(){
	var forced = $("#"+this.id).find(".list-forced-result");
	if(forced.length)
		return this.display(JSON.parse(forced.val()));
	else
		return this.display();		
};

ItemList.prototype.set = function(data){
	for(var i in data)
		this[i] = data[i];
	this.refresh();
};

/******************* (C) COPYRIGHT 2014 ELVYRRA SAS *********************/