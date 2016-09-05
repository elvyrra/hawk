{widget class="Hawk\Plugins\Main\MainMenuWidget"}
<script type="text/javascript" src="{{ Theme::getSelected()->getFileUrl('js/theme-hawk.js') }}"></script>

{if(empty($content))}
	<div id="main-content" role="tabpanel" ko-with="tabset">
		<!-- Nav tabs -->
		{if($canAccessApplication)}
			<ul class="nav nav-tabs" role="tablist" id="main-nav-tabs">
				<!-- ko foreach: tabs -->
				<li role="presentation" class="main-tab-title corner-top"
					ko-event="{mousedown : function(data, event){ $parent.clickTab($index(), event) }}"
					ko-attr="{ id : 'main-tab-title-' + id(), 'data-tab' : $index }"
					ko-class="{active : $parent.activeTab() == $data }"
					ko-style="{ width: 'calc((100% - 25px )/ ' + $parent.tabs().length + ' - 2px )' }"
					data-toggle="tooltip" data-placement="bottom" >
					<a role="tab" data-toggle="tab" ko-attr="{ href: '#main-tab-' + id() }">
						<!-- ko if: icon -->
						<i class="icon" ko-class="'icon-' + icon()"></i>
						<!-- /ko -->
						<!-- ko if: favicon -->
						<img ko-attr="{src : favicon}" alt="tab-favicon" class="main-tab-favicon" />
						<!-- /ko -->
						<span ko-text="title"></span>
					</a>

					<span class="main-tab-close pull-right" ko-attr="{ 'data-tab' : $index }" ko-visible="$parent.tabs().length > 1" ko-click="function(){ $parent.remove($index()) }">
						{icon icon="times-circle"}
					</span>
				</li>
				<!-- /ko -->

				<li class="add-tab-button corner-top-left corner-bottom-right" data-href="{uri action='new-tab'}" data-target="newtab">
					<span class="" id="main-tab-add">
						{icon icon="plus" class="open-new-tab" title="{text key='main.open-new-tab' encoded='true'}"}
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
<div class="app-notification alert col-md-6 col-md-offset-3 col-xs-12" ko-class="'alert-' + level()" ko-style="{ visibility : display() ? 'visible' : 'hidden', opacity : display() ? 1 : 0 }" ko-click="$root.hideNotification.bind($root)">
	<span ko-html="message"></span>
	<span class="close">&times;</span>
</div>
<!-- /ko -->

<div id="footer">
	{text key="main.hawk-powered"}
	{if(DEV_MODE)}
		<a href="{uri action='clear-cache'}" class="real-link pull-right" title="{text key="main.clear-cache"}">
			{icon icon="calendat-times-o" size="lg" class="clear-cache-btn"}
		</a>
	{/if}
</div>


<div id='loading' ko-visible="loading.display">
	{icon icon="spinner" size="5x" class="center icon-spin"}
	<div id="loading-bar" ko-class="{processing: loading.processing}">
		<span id='loading-purcentage' ko-style="{ width: loading.purcentage() + '%'}"></span>
	</div>
</div>