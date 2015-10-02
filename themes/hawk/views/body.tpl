<!-- ko with : notification -->
<div class="app-notification alert" data-bind="css : 'alert-' + level(), 
												style : {
													visibility : display() ? 'visible' : 'hidden', 
													opacity : display() ? 1 : 0 
												}">
	<span data-bind="html: message"></span>
	<span class="close" data-bind="click : $root.hideNotification.bind($root)">&times;</span>
</div>
<!-- /ko -->

<div id="footer">
	{text key='main.hawk-powered'}
	{if(DEV_MODE)}
		<a href="{uri action='clear-cache'}" class="real-link pull-right" title="{text key="main.clear-cache"}"> 
			<i class="icon icon-calendar-times-o icon-lg clear-cache-btn"></i>
		</a>
	{/if}
</div>


<div id='loading' data-bind="visible: loading.display">
	<span class='icon icon-spinner icon-spin icon-5x'></span>
	<div id="loading-bar" data-bind="css: { progressing: loading.progressing}">
		<span id='loading-purcentage' data-bind="style: { width: loading.purcentage() + '%'}"></span>
	</div>
</div>

{widget class="Hawk\Plugins\Main\MainMenuWidget"}

{if(empty($content))}
	<div id="main-content" role="tabpanel" data-bind="with: tabset">
		<!-- Nav tabs -->
		<!-- {if($canAccessApplication)} -->
			<ul class="nav nav-tabs" role="tablist" id="main-nav-tabs">
				<!-- ko foreach: tabs -->
				<li role="presentation" class="main-tab-title corner-top" data-toggle="tooltip" data-placement="bottom" data-bind="attr: { 
																						id : 'main-tab-title-' + id(), 
																						'data-tab' : $index,
																						title : title 
																					}, 
																					click : $parent.clickTab.bind($parent), 
																					style : { 
																						width: 'calc((100% - 25px )/ ' + $parent.tabs().length + ' - 2px )' 
																					}">
					<a role="tab" data-toggle="tab" data-bind="attr: { href: '#main-tab-' + id() }, html : title" ></a>

					<span class="main-tab-close pull-right" data-bind="attr: { 'data-tab' : $index }, visible: $parent.tabs().length > 1, click : function(){ $parent.remove($index()) }">
						<span class="icon icon-times-circle"></span>
					</span>
				</li>
				<!-- /ko -->

				<li class="add-tab-button corner-top-left corner-bottom-right" href="{uri action='new-tab'}" target="newtab" data-bind="visible: tabs().length < Tabset.MAX_TABS_NUMBER">
					<span class="" id="main-tab-add">
						<span class="icon icon-plus open-new-tab" title="{text key='main.open-new-tab'}" ></span>
					</span>
				</li>
			</ul>
		<!-- {/if} -->
		
		<!-- Tab panes -->
		<div class="tab-content" id="main-tab-content" data-bind="foreach: tabs">
			<div role="tabpanel" class="tab-pane main-tab-pane" data-bind="attr : { id : 'main-tab-' + id(), 'data-tab' : $index}, html : content"></div>
		</div>
	</div>
{else}
	{{ $content }}
{/if}

<div class="modal" id="dialogbox"></div>