{widget class="Hawk\Plugins\Main\MainMenuWidget"}

{if(empty($content))}
	<div id="main-content" role="tabpanel" ko-with="tabset">
		<!-- Nav tabs --> 
		{if($canAccessApplication)}
			<ul class="nav nav-tabs" role="tablist" id="main-nav-tabs">
				<!-- ko foreach: tabs -->
				<li role="presentation" class="main-tab-title corner-top" 
					ko-click="function(data, event){ $parent.clickTab($index(), event) }" 
					ko-attr="{ id : 'main-tab-title-' + id(), 'data-tab' : $index }"
					ko-class="{active : $parent.activeTab() == $data }"
					ko-style="{ width: 'calc((100% - 25px )/ ' + $parent.tabs().length + ' - 2px )' }"
					data-toggle="tooltip" data-placement="bottom" >
					<a role="tab" data-toggle="tab" ko-attr="{ href: '#main-tab-' + id() }" ko-html="title" ></a>

					<span class="main-tab-close pull-right" ko-attr="{ 'data-tab' : $index }" ko-visible="$parent.tabs().length > 1" ko-click="function(){ $parent.remove($index()) }">
						<span class="icon icon-times-circle"></span>
					</span>
				</li>
				<!-- /ko -->

				<li class="add-tab-button corner-top-left corner-bottom-right" href="{uri action='new-tab'}" target="newtab">
					<span class="" id="main-tab-add">
						<span class="icon icon-plus open-new-tab" title="{text key='main.open-new-tab'}" ></span>
					</span>
				</li>
			</ul>
		{/if}
		
		<!-- Tab panes -->
		<div class="tab-content" id="main-tab-content" ko-foreach="tabs">
			<div role="tabpanel" class="tab-pane main-tab-pane" ko-attr="{ id : 'main-tab-' + id(), 'data-tab' : $index}" ko-html="content" ko-class="{active : $parent.activeTab() == $data}"></div>
		</div>
	</div>
{else}
	{{ $content }}
{/if}

<div class="modal fade" id="dialogbox"></div>

<!-- ko with : notification -->
<div class="app-notification alert" ko-class="'alert-' + level()" ko-style="{ visibility : display() ? 'visible' : 'hidden', opacity : display() ? 1 : 0 }" ko-click="$root.hideNotification.bind($root)">
	<span ko-html="message"></span>
	<span class="close">&times;</span>
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


<div id='loading' ko-visible="loading.display">
	<span class='icon icon-spinner icon-spin icon-5x'></span>
	<div id="loading-bar" ko-class="{processing: loading.processing}">
		<span id='loading-purcentage' ko-style="{ width: loading.purcentage() + '%'}"></span>
	</div>
</div>