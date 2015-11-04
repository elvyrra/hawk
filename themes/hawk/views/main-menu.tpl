<nav id="main-menu" class="navbar navbar-inverse">	
	{if($logo)}
		<img class="application-logo pull-left" src="{{ $logo }}" alt="Application logo"/>
	{/if}
	<div class="container-fluid">
		<div class="navbar-header">
			<div class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu-collapse" >
		        <span class="sr-only">Toggle navigation</span>
		        <span class="icon icon-bars"></span>
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
									<i class="icon icon-caret-down"></i>
								</div>
								<ul class="dropdown-menu" role="menu" aria-labelledby="main-menu-title-{{ $menu->id }}" id="main-menu-items-{{ $menu->id }}">
									{foreach($menu->visibleItems as $item)}
										<li role="presentation" class="main-menu-item" id="main-menu-item-{{ $item->id }}" data-item="{{ $item->name }}">
											<a role="menuitem" href="{{ htmlentities($item->url, ENT_QUOTES) }}" {if(!empty($item->target))} target="{{ $item->target }}" {/if}> 
												{{ $item->label }}
											</a>
										</li>					
									{/foreach}					
								</ul>
							</li>
						{else}
							<li class="main-menu" id="main-menu-{{ $menu->id }}">
								<div class="main-menu-title" type="button" id="main-menu-title-{{ $menu->id }}">
									<a href="{{ htmlentities($menu->url, ENT_QUOTES) }}" {if(!empty($menu->target))} target="{{ $menu->target }}" {/if}>
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