{widget class="Hawk\Plugins\Main\MainMenuWidget"}
<script type="text/javascript" src="{{ Theme::getSelected()->getFileUrl('js/theme-hawk.js') }}"></script>

{if(empty($content))}
	<div id="main-content" role="tabpanel">
		<!-- Nav tabs -->
		{if($canAccessApplication)}
			<ul class="nav nav-tabs" role="tablist" id="main-nav-tabs">
				<li role="presentation" class="main-tab-title corner-top"
					e-each="tabs"
					e-on="{mousedown : $root.clickTab.bind($root)}"
					id="maint-tab-title-${id}" data-tab="${$index}"
					e-class="{active : $root.activeTab == $this }"
					e-style="{ width: 'calc((100% - 25px )/ ' + $root.tabs.length + ' - 2px )' }"
					data-toggle="tooltip" data-placement="bottom" >
					<a role="tab" data-toggle="tab" href="#main-tab-${id}">
						<i class="icon icon-${ icon }" e-if="icon"></i>
						<img e-attr="{src : favicon}" alt="tab-favicon" class="main-tab-favicon" e-if="favicon"/>
						<span e-text="title">${title}</span>
					</a>

					<span class="main-tab-close pull-right" e-attr="{ 'data-tab' : $index }" e-show="$root.tabs.length > 1" e-click="$root.remove.bind($root)">
						{icon icon="times-circle"}
					</span>
				</li>

				<li class="add-tab-button corner-top-left corner-bottom-right" data-href="{uri action='new-tab'}" data-target="newtab">
					<span class="" id="main-tab-add">
						{icon icon="plus" class="open-new-tab" title="{text key='main.open-new-tab' encoded='true'}"}
					</span>
				</li>
			</ul>
		{/if}

		<!-- Tab panes -->
		<div class="tab-content" id="main-tab-content">
			<div role="tabpanel" class="tab-pane main-tab-pane"
				e-each="tabs"
				id="main-tab-${id}"
				data-tab="${$index}"
				e-html="content"
				e-class="{active : $root.activeTab == $this}"></div>
		</div>
	</div>
{else}
	{{ $content }}
{/if}

<div class="modal fade" id="dialogbox"></div>

<div id="app-notification" class="app-notification alert col-md-6 col-md-offset-3 col-xs-12 alert-${level}"
	e-style="{ visibility : display ? 'visible' : 'hidden', opacity : display ? 1 : 0 }"
	onclick="app.hideNotification()">
	<span e-html="message"></span>
	<span class="close">&times;</span>
</div>


<div id="footer">
	{text key="main.hawk-powered"}
	{if(DEV_MODE)}
		<a href="{uri action='clear-cache'}" class="real-link pull-right" title="{text key="main.clear-cache"}">
			{icon icon="calendat-times-o" size="lg" class="clear-cache-btn"}
		</a>
	{/if}
</div>


<div id='loading' e-show="display">
	{icon icon="spinner" size="5x" class="center icon-spin"}
	<div id="loading-bar" e-class="{processing: processing}">
		<span id='loading-purcentage' e-style="{ width: purcentage + '%'}"></span>
	</div>
</div>