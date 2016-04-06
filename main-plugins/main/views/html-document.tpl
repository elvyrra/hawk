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
		<link rel="stylesheet" type="text/css" href="{{ Theme::getSelected()->getBaseCssUrl() }}" id="theme-base-stylesheet"/>

		<!-- Customized file of the theme -->
		<link rel="stylesheet" href="{{ Theme::getSelected()->getCustomCssUrl() }}" id="theme-custom-stylesheet"/>

		<script type="text/javascript" src="{{ Plugin::get('main')->getJsUrl('ext/require.js') }}" ></script>
		<script type="text/javascript" src="{{ Plugin::get('main')->getJsUrl(DEV_MODE ? 'app.js' : 'app.min.js') }}" id="app-main-script"></script>
	</head>

	<body>
		{{ $body }}
	</body>
</html>