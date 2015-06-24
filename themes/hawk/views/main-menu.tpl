<div id="main-menu">	
	{if($logo)}
		<img class="application-logo pull-left" src="{{ $logo }}" />
	{/if}
	{foreach($menus as $menu)}
		<div class="dropdown pull-left" id="main-menu-{{ $menu->id }}">
			<div class="dropdown-toggle main-menu-title" type="button" id="main-menu-title-{{ $menu->id }}" data-toggle="dropdown" aria-expanded="true">
				{{ $menu->label }}
				<i class="fa fa-caret-down"></i>
			</div>
			<ul class="dropdown-menu" role="menu" aria-labelledby="main-menu-title-{{ $menu->id }}" id="main-menu-items-{{ $menu->id }}">
				{foreach($menu->visibleItems as $item)}
					<li role="presentation" id="main-menu-item-{{ $item->id }}">							
						<a role="menuitem" href="{uri action="{$item->action}"}" {if(!empty($item->target))} target="{{ $item->target ? $item->target : '' }}" {/if}> 
							{{ $item->label }}
						</a>
					</li>					
				{/foreach}					
			</ul>
		</div>
	{/foreach}
	
	{if(Session::logged())}
		{if(Session::getUser()->isAllowed('admin'))}
			<!-- Admin menu -->
			<div class="dropdown pull-right" id="main-menu-admin">
				<div class="dropdown-toggle main-menu-title" id="main-menu-title-admin" data-toggle="dropdown" aria-expanded="true">
					{text key="main.menu-admin-title"}
					<i class="fa fa-caret-down"></i>
				</div>
				<ul class="dropdown-menu" role="menu" aria-labelledby="main-menu-title-admin">
					{foreach($adminMenu as $item)}						
						<li role="presentation">							
							<a role="menuitem" href="{{ $item['url'] }}" target="{{ $item['target'] ? $item['target'] : '' }}" ><i class="fa fa-{{ $item['icon'] }}"></i>{{ $item['label'] }}</a>
						</li>
					{/foreach}
				</ul>
			</div>
		{/if}
		
		<!-- User menu -->
		<div class="dropdown pull-right" id="main-menu-user">
			<div class="dropdown-toggle main-menu-title" id="main-menu-title-user" data-toggle="dropdown" aria-expanded="true">
				{{ $user->getUsername() }}
				<i class="fa fa-caret-down"></i>
			</div>
			<ul class="dropdown-menu" role="menu" aria-labelledby="main-menu-title-user">
				{foreach($userMenu as $item)}
					<li role="presentation">						
						<a role="menuitem" href="{{ $item['url'] }}" {if(!empty($item['class']))}class="{{ $item['class'] }}"{/if} target="{{ $item['target'] ? $item['target'] : '' }}" ><i class="fa fa-{{ $item['icon'] }}"></i>{{ $item['label'] }}</a>
					</li>					
				{/foreach}					
			</ul>
		</div>
	{else}
		<div id="main-menu-login-btn" class="main-menu-title pull-right">
			<a href="{uri action="LoginController.login"}" target="dialog">
				<i class="fa fa-sign-in"></i> {text key="main.login"}
			</a>
		</div>
	{/if}
</div>