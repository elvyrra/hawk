<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="{{ ENCODING }}" />
		<meta http-equiv="Content-Type" content="text/html; charset={{ ENCODING }}" />
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title>{{ $title }}</title>		
		<meta name="description" content="{{ $description }}" />
		<meta name="keywords" content="{{$keywords}}" />
		
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

		<script type="text/javascript" id="app-script" src="{{ $mainJsUrl }}app.js"></script>

		<script type="text/javascript">
			app.setLanguage("{{ LANGUAGE }}");
			app.setRootUrl("{{ ROOT_URL }}");
			app.setRoutes({{ json_encode($routes) }});
				
			app.ready(function(){		
				Lang.langs = {{ $langLabels }};
			});
		</script>
	</head>
	
	<body>
		{{ $body }}
	</body>
</html>