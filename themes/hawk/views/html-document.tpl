<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="{{ ENCODING }}" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>{{ $title }}</title>
		{if($favicon)}
			<link rel="shortcut icon" href="{{ $favicon }}" />
			<link rel="icon" href="{{ $favicon }}" />
		{/if}
		
		<!-- FontAwesome -->
		<link href="{{ $mainCssUrl }}font-awesome.min.css" rel="stylesheet" />        
		<!-- Bootstrap CSS-->
		<link rel="stylesheet" href="{{ $mainCssUrl }}bootstrap.min.css" />
		<!-- Bootstrap Colorpicker -->
		<link rel="stylesheet" href="{{ $mainCssUrl }}bootstrap-colorpicker.min.css" />
		<!-- Bootstrap Datepicker -->
		<link rel="stylesheet" href="{{ $mainCssUrl }}bootstrap-datepicker.min.css" />
		
		<!-- Base CSS file of the theme -->
		<link rel="stylesheet" id="theme-base-stylesheet" href="{{ $themeBaseCss }}" />
		<!-- Customized file of the theme -->
		<link rel="stylesheet" id="theme-custom-stylesheet" href="{{ $themeCustomCss }}" />

						
		<script type="text/javascript" src="{{ $mainJsUrl }}jquery-2.1.3.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}jquery.addons.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}jquery.cookie.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}jquery.mask.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}bootstrap.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}bootstrap-colorpicker.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}bootstrap-datepicker.min.js"></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}knockout-3.3.0.js"></script>
		<script type="text/javascript" src="//cdn.ckeditor.com/4.4.7/full/ckeditor.js"></script>
		
		<script type="text/javascript" id="main-js-script" src="{{ $mainJsUrl }}app.js"></script>
		
		<script type="text/javascript">
			app.setLanguage("{{ LANGUAGE }}");
			app.setRootUrl("{{ ROOT_URL }}");
			app.setRoutes({{ json_encode(Router::getRoutes()) }});
				
			app.ready(function(){		
				Lang.langs = {{ $langLabels }};
			});
		</script>
	</head>
	
	<body>
		{{ $body }}
	</body>
</html>