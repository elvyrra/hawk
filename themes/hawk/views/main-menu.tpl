<nav id="main-menu" class="navbar navbar-inverse">
	<div class="container-fluid">
		<div class="navbar-header">
			<img class="application-logo" src="{{ $logo ? $logo : Plugin::get('main')->getStaticUrl('img/hawk-logo.png') }}" alt="Application logo"/>
			<h1 class="visible-xs app-title vertical-center text-center">{{ App::conf()->has('db') ? Option::get('main.page-title-' . LANGUAGE) : DEFAULT_HTML_TITLE }}</h1>
			<div class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu-collapse" >
		        <span class="sr-only">Toggle navigation</span>
		        {icon icon="bars"}
	      	</div>
		</div>

		<div class="collapse navbar-collapse" id="main-menu-collapse">
			{foreach($menus as $name => $groups)}
				<ul class="nav navbar-nav {if($name=='user')} navbar-right {/if}">
					{foreach($groups as $menu)}
						{if($menu->visibleItems)}
							<li class="dropdown main-menu" id="main-menu-{{ $menu->id }}">
								<div class="dropdown-toggle main-menu-title" type="button" id="main-menu-title-{{ $menu->id }}" data-toggle="dropdown">
									{{ $menu->label }}
									{icon icon="caret-down"}
								</div>
								<ul class="dropdown-menu" role="menu" aria-labelledby="main-menu-title-{{ $menu->id }}" id="main-menu-items-{{ $menu->id }}">
									{foreach($menu->visibleItems as $item)}
										<li role="presentation" class="main-menu-item" id="main-menu-item-{{ $item->id }}" data-item="{{ $item->name }}">
											<a role="menuitem" href="{{{ $item->url }}}" {if(!empty($item->target))} target="{{ $item->target }}" {/if}>
												{{ $item->label }}
											</a>
										</li>
									{/foreach}
								</ul>
							</li>
						{else}
							<li class="main-menu" id="main-menu-{{ $menu->id }}">
								<div class="main-menu-title" type="button" id="main-menu-title-{{ $menu->id }}">
									<a href="{{{ $menu->url }}}" {if(!empty($menu->target))} target="{{ $menu->target }}" {/if}>
										{{ $menu->label }}
									</a>
								</div>
							</li>
						{/if}
					{/foreach}
				</ul>
			{/foreach}
		</div>
	</div>
</nav>