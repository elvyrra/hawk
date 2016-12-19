<nav id="main-menu" class="navbar navbar-inverse" e-with="{$data : menu, $as : 'menu'}">
	<div class="container-fluid">
		<div class="navbar-header">
			<img class="application-logo" src="{{ $appLogo }}" alt="Application logo"/>
			<h1 class="visible-xs app-title vertical-center text-center">{{ App::conf()->has('db') ? Option::get('main.page-title-' . LANGUAGE) : DEFAULT_HTML_TITLE }}</h1>
			<div class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-menu-collapse" >
		        <span class="sr-only">Toggle navigation</span>
		        {icon icon="bars"}
	      	</div>
		</div>

		<div class="collapse navbar-collapse" id="main-menu-collapse">
			<ul e-each="{$data : Object.keys(items), $item : 'section'}"
				class="nav navbar-nav"
				e-class="{'navbar-right' : $section === 'settings'}">

				<li e-each="$menu.items[$section]" id="main-menu-${id}" class="main-menu" e-class="{dropdown : visibleItems.length}">
					<!-- Main menu with sub items -->
					<div class="dropdown-toggle main-menu-title" type="button" id="main-menu-title-${id}" data-toggle="dropdown" e-if="visibleItems.length">
						<i class="icon icon-${ icon } icon-fw" e-if="icon"></i> <span e-text="label"></span> {icon icon="caret-down"}
					</div>
					<ul class="dropdown-menu" role="menu" id="main-menu-items-${id}" e-if="visibleItems.length">
						<li e-each="visibleItems" role="presentation" class="main-menu-item" id="main-menu-item-${id}" data-item="${name}">
							<a role="menuitem" e-attr="{href : url, target : target}">
								<i class="icon icon-${ icon } icon-fw"></i> <span e-text="label"></span>
							</a>
						</li>
					</ul>

					<!-- Main menus without sub items -->
					<div class="main-menu-title" type="button" id="main-menu-title-${$menu.id}" e-unless="visibleItems.length">
						<a e-attr="{href : url, target : target}">
							<i class="icon icon-${ icon } icon-fw" e-if="icon"></i> <span e-text="label"></span>
						</a>
					</div>
				</li>
			</ul>
		</div>
	</div>
</nav>