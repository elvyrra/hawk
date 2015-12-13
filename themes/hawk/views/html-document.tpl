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

		<script type="text/javascript" src="{{ App::router()->getUri('js-conf') }}"></script>
		
		<!-- Base Less file of the theme -->		
		<link rel="stylesheet/less" type="text/css" href="{{$themeBaseLess}}" />

		<!-- Build the less base file of the theme -->
		<script type="text/javascript" src="{{ Plugin::get('main')->getJsUrl('ext/less.js') }}"></script>
		
		<!-- Customized file of the theme -->
		<link rel="stylesheet" id="theme-custom-stylesheet" href="{{ $themeCustomCss }}" />

		<!-- Bootstrap Colorpicker -->
		<link rel="stylesheet" href="{{ Plugin::get('main')->getCssUrl('bootstrap-colorpicker.min.css') }}" />
		<!-- Bootstrap Datepicker -->
		<link rel="stylesheet" href="{{ Plugin::get('main')->getCssUrl('bootstrap-datepicker.min.css') }}" />

		<script type="text/javascript" src="{{ Plugin::get('main')->getJsUrl('ext/require.js') }}" data-main="{{ Plugin::get('main')->getJsUrl('app.js') }}"></script>

	</head>
	
	<body>
		{{ $body }}
	</body>
</html>