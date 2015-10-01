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
		
		<!-- Bootstrap CSS-->
		<link rel="stylesheet" href="{{ Plugin::get('main')->getCssUrl('bootstrap.min.css') }}" />
		<!-- Bootstrap Colorpicker -->
		<link rel="stylesheet" href="{{ Plugin::get('main')->getCssUrl('bootstrap-colorpicker.min.css') }}" />
		<!-- Bootstrap Datepicker -->
		<link rel="stylesheet" href="{{ Plugin::get('main')->getCssUrl('bootstrap-datepicker.min.css') }}" />
		
		<!-- Base CSS file of the theme -->
		<link rel="stylesheet" id="theme-base-stylesheet" href="{{ $themeBaseCss }}" />
		<!-- Customized file of the theme -->
		<link rel="stylesheet" id="theme-custom-stylesheet" href="{{ $themeCustomCss }}" />

		<script type="text/javascript" src="{{ Router::getUri('js-conf') }}"></script>

		<script type="text/javascript" src="{{ Plugin::get('main')->getJsUrl('ext/require.js') }}" data-main="{{ Plugin::get('main')->getJsUrl('app.js') }}"></script>
	</head>
	
	<body>
		{{ $body }}
	</body>
</html>