{widget class="MainMenuWidget"}
		
<div id="main-content" role="tabpanel">
	<!-- Nav tabs -->
	<ul class="nav nav-tabs" role="tablist" id="main-nav-tabs">
		{if($canAccessApplication)}
			<li class="add-tab-button corner-top-left corner-bottom-right" href="{uri action='MainController.newTab'}" target="newtab" >
				<span class="" id="main-tab-add">
					<span class="fa fa-plus open-new-tab" title="{text key='main.open-new-tab'}" ></span>
				</span>
			</li>
		{/if}
	</ul>
	
	<!-- Tab panes -->
	<div class="tab-content" id="main-tab-content"></div>			  
</div>

<div id="footer">
	{text key='main.mint-powered'}
</div>

<div class="modal fade" id="dialogbox"></div>

<div id='loading'>
	<span class='fa fa-spinner fa-spin fa-5x'></span>
	<div id="loading-bar">
		<span id='loading-purcentage'></span>
	</div>
</div>

<div class="template" id="tab-title-template">
	<li role="presentation" class="main-tab-title corner-top" id="main-tab-title-@@id" data-tab="@@id">
		<a href="#main-tab-@@id" aria-controls="main-tab-@@id" role="tab" data-toggle="tab"></a>
		<i class="main-tabs-close fa fa-times-circle" data-tab="@@id" ></i>
	</li>	
</div>

<div class="template" id="tab-content-template">
	<div role="tabpanel" class="tab-pane main-tab-pane" id="main-tab-@@id" data-tab="@@id"></div>
</div>

<script type="text/javascript">
	mint.ready(function(){		
		$.pages = {{ $pages }};
		$.openLastTab = function(i){
			if (i >= $.pages.length) {
				return;
			}
			
			if ($.pages[i]) {						
				mint.load($.pages[i], {
					newtab : true,
					onload : function(){
						$.openLastTab(++i);
					}
				});
			}
			else{
				$.openLastTab(++i);
			}
		}
		$(window).load(function(){							
			{if(!$canAccessApplication)}
				$("#main-menu-login-btn a").click();
			{else}
				if($.pages){
					$.openLastTab(0);
				}
				else{
					mint.tabset.push();
				}
			{/if}
		});		
	});
</script>