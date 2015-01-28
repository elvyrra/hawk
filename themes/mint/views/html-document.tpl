<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf-8" />
		<meta content="text/html; charset=utf-8" />
		<title>{{ Option::get('main.title') }}</title>
		<link rel="shortcut icon" href="/gfx/favicon.ico" />
        <link rel="icon" href="/gfx/favicon.ico" />
		
		<!-- FontAwesome -->
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet">        
		<!-- Bootstrap CSS-->
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css">		
		<!-- Base CSS file of the theme -->
		<link rel="stylesheet" href="{{ $themeBaseCss }}">        
		<!-- Customized file of the theme -->
		{if($themeCustomCss)}
			<link rel="stylesheet" href="{{ $themeCustomCss }}">
		{/if}
				
		<!-- JQuery -->
		<script type="text/javascript" src="//code.jquery.com/jquery-2.1.3.min.js"></script>
		<!-- Bootstrap JS -->
		<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>		
		
		<!-- Load jquery plugin librarires -->
		<script type="text/javascript" src="{{ $mainJsDir }}jquery.addons.js?{{ APP_VERSION }}"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}jquery.mask.min.js?{{ APP_VERSION }}"></script>		
				
		<!-- Load main internal libraries -->
		<script type="text/javascript" src="{{ $mainJsDir }}util.js?{{ APP_VERSION }}"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}date.js?{{ APP_VERSION }}"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}tabs.js?{{ APP_VERSION }}"></script>		
		<script type="text/javascript" src="{{ $mainJsDir }}page.js?{{ APP_VERSION }}"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}form.js?{{ APP_VERSION }}"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}list.js?{{ APP_VERSION }}"></script>		
		<script type="text/javascript" src="{{ $mainJsDir }}lang.js?{{ APP_VERSION }}"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}mint.js?{{ APP_VERSION }}"></script>
		
		<script type="text/javascript">
			page.language = "{{ LANGUAGE }}";		
			jQuery().ready(function(){
				$(window).load(function(){					
					var pages = {{ $pages }};
					for(var i = 0; i< pages.length; i++){
						page.load(pages[i], {newtab : true});
					}				
				});						
			});

			Router.routes = {{ json_encode(Router::getRoutes()) }};
			
			Lang.langs = {{ $langLabels }};
		</script>
	</head>
	
	<body>
		{component class="MainMenuWidget"}
		
		<div id="main-content" role="tabpanel">
			<!-- Nav tabs -->
			<ul class="nav nav-tabs" role="tablist" id="main-nav-tabs">
				<li class="add-tab-button corner-top-left corner-bottom-right" href="{uri action="MainController.newTab"}" target="newtab">
					<span class="" id="main-tab-add">
						<span class='fa fa-plus open-new-tab' title="{text key="main.open-new-tab"}" ></span>
					</span>
				</li>
			</ul>
			
			<!-- Tab panes -->
			<div class="tab-content" id="main-tab-content"></div>			  
		</div>
		
		<div id="footer">
			{text key='main.mint-powered'}
		</div>
		
		{import "dialogbox.tpl"}
	</body>
</html>