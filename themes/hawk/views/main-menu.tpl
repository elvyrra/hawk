<nav id="main-menu" class="navbar navbar-inverse">	
	{if($logo)}
		<img class="application-logo pull-left" src="{{ $logo }}" alt="Application logo"/>
	{/if}
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu-collapse" >
		        <span class="sr-only">Toggle navigation</span>
		        <span class="fa fa-bars"></span>
	      	</button>
		</div>

		<div class="collapse navbar-collapse" id="main-menu-collapse">
			<ul class="nav navbar-nav">
				{foreach($menus as $menu)}					
					<li class="dropdown main-menu" id="main-menu-{{ $menu->id }}" data-menu="{{ $menu->name }}">
						<span class="dropdown-toggle main-menu-title" type="button" id="main-menu-title-{{ $menu->id }}" data-toggle="dropdown">
							{{ $menu->label }}
							<i class="fa fa-caret-down"></i>
						</span>
						<ul class="dropdown-menu" role="menu" aria-labelledby="main-menu-title-{{ $menu->id }}" id="main-menu-items-{{ $menu->id }}">
							{foreach($menu->visibleItems as $item)}
								<li role="presentation" class="main-menu-item" id="main-menu-item-{{ $item->id }}" data-item="{{ $item->name }}">
									<a role="menuitem" href="{{ $item->url }}" {if(!empty($item->target))} target="{{ !empty($item->target) ? $item->target : '' }}" {/if}> 
										{{ $item->label }}
									</a>
								</li>					
							{/foreach}					
						</ul>
					</li>
				{/foreach}
			</ul>

			<ul class="nav navbar-nav navbar-right">
				{if(Session::isConnected())}
					{foreach($userMenus as $menu)}						
						<li class="dropdown main-menu" id="main-user-menu-{{ $menu->name }}" data-menu="{{ $menu->name }}">
							<span class="dropdown-toggle main-menu-title" type="button" id="main-user-menu-title-{{ $menu->name }}" data-toggle="dropdown">
								{{ $menu->label }}
								<i class="fa fa-caret-down"></i>
							</span>
							<ul class="dropdown-menu" role="menu" aria-labelledby="main-user-menu-title-{{ $menu->name }}" id="main-menu-items-{{ $menu->name }}">
								{foreach($menu->visibleItems as $item)}
									<li role="presentation" class="main-menu-item" id="main-user-menu-item-{{ $item->name }}" data-item="{{ $item->name }}">
										<a role="menuitem" href="{{ $item->url }}" {if(!empty($item->target))} target="{{ !empty($item->target) ? $item->target : '' }}" {/if}> 
											{{ $item->label }}
										</a>
									</li>					
								{/foreach}					
							</ul>
						</li>
					{/foreach}
				{else}
					<li id="main-menu-login-btn" class="main-menu-title">
						<a href="{uri action="LoginController.login"}" target="dialog">
							<i class="fa fa-sign-in"></i> {text key="main.login"}
						</a>
					</li>
				{/if}
			</ul>
		</div>
	</div>
</nav>