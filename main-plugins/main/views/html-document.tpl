<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="{{ ENCODING }}" />
		<meta content="text/html; charset=utf-8" />
		<title>{{ Option::get('main.title') }}</title>
		{if($favicon)}
			<link rel="shortcut icon" href="{{ $favicon }}" />
			<link rel="icon" href="{{ $favicon }}" />
		{/if}
		
		<!-- FontAwesome -->
		<link href="{{ $mainCssDir }}font-awesome.min.css" rel="stylesheet" />        
		<!-- Bootstrap CSS-->
		<link rel="stylesheet" href="{{ $mainCssDir }}bootstrap.min.css" />
		<!-- Bootstrap Colorpicker -->
		<link rel="stylesheet" href="{{ $mainCssDir }}bootstrap-colorpicker.min.css" />
		<link rel="stylesheet" href="{{ $mainCssDir }}bootstrap-datepicker.min.css" />
		
		<!-- Base CSS file of the theme -->
		<link rel="stylesheet" id="theme-base-stylesheet" href="{{ $themeBaseCss }}" />
		<!-- Customized file of the theme -->
		<link rel="stylesheet" id="theme-custom-stylesheet" href="{{ $themeCustomCss }}" />

						
		<script type="text/javascript" src="{{ $mainJsDir }}jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}jquery.addons.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}jquery.cookie.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}jquery.mask.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}bootstrap.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}bootstrap-colorpicker.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}bootstrap-datepicker.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsDir }}angular.min.js"></script>
		<script type="text/javascript" src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>
		
		<script type="text/javascript" id="main-js-script" src="{{ $mainJsDir }}mint.js"></script>
		
		<script type="text/javascript">
			mint.setLanguage("{{ LANGUAGE }}");
			mint.setRootUrl("{{ ROOT_URL }}");
			mint.isConnected = {{ $logged }};
			mint.setRoutes({{ json_encode(Router::getRoutes()) }});
				
			mint.ready(function(){		
				Lang.langs = {{ $langLabels }};
			});
		</script>
	</head>
	
	<body>
		{{ $body }}
	</body>
</html>