define('tabs', ['jquery', 'ko'], function($, ko){

	/**
	 * This class describes the behavior of a tab
	 * @param int id The unique tab id
	 */
	window.Tab = function(id){
		this.id = ko.observable(id);
		this.uri = ko.observable("");
		this.content = ko.observable('');
		this.route = ko.observable("");

		this.title = ko.computed({
			read : function(){
				return $(".page-name", this.content()).first().val();
			}.bind(this)
		});

		this.history = [];

		var self = this;
	};


	var Tabset = function(){
		this.tabs = ko.observableArray([]);

		this.activeId = ko.observable();

		this.activeTab = ko.computed({
			read : function(){
				for(var i = 0; i< this.tabs().length; i++){
					if(this.tabs()[i].id() === this.activeId()){
						return this.tabs()[i];
					}
				}
			}.bind(this),
			write : function(tab){
				this.activeId(tab.id());
			}.bind(this)
		});

		this.activeTab.subscribe(function(tab){
			history.replaceState({}, "", '#!' + tab.history[tab.history.length - 1]);
		}.bind(this));
	};

	/**
	 * This index is incremented each time a tab is created, to generate a unique id for each tab
	 */
	Tabset.index = 0;


	/**
	 * Push a new tab in the tabset
	 */
	Tabset.prototype.push = function(){
		/* Create the tab */
		var tab = new Tab(Tabset.index ++);
		this.tabs.push(tab);

		/*** Activate the new tab ***/
		this.activeId(tab.id());
	};


	/**
	 * Remove a tab by it index in the tabset
	 * @param int index The tab index in the tabset
	 */
	Tabset.prototype.remove = function(index){
		if(this.tabs().length > 1){
			if (this.activeTab() == this.tabs()[index]){
				var next = index == this.tabs().length - 1 ? this.tabs()[index - 1] : this.tabs()[index + 1];
				if(next){
					/* Activate the next tab */
					this.activeId(next.id());
				}
			}

			/* Delete the tab nodes */
			this.tabs.splice(index, 1);

			/* Register the new list of tabs */
			this.registerTabs();
		}
	};


	/**
	 * Save the tabs last urls in a cookie
	 */
	Tabset.prototype.registerTabs = function(){
		var data = [];
		for(var i = 0; i < this.tabs().length; i++){
			data.push(this.tabs()[i].uri());
		}
		$.cookie('open-tabs', JSON.stringify(data), {expires : 365, path : '/'});
	};


	/**
	 * Perform click action on tab title
	 * @param int $index The tab index in the tabset
	 * @param Event event The triggered event
	 */
	Tabset.prototype.clickTab = function($index, event){
		if(event.which === 2){
			this.remove($index);
		}
		else{
			this.activeId(this.tabs()[$index].id());
		}
		return false;
	};

	return Tabset;
});