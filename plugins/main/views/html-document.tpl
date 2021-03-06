<!DOCTYPE html>
<html lang="fr">
	<head>
		<meta charset="utf8" />
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

		<script type="text/javascript" src="{{ $polyfillUrl }}" ></script>
		<script type="text/javascript" src="{{ $bluebirdUrl }}" ></script>
		<script type="text/javascript" src="{{ $momentUrl }}" ></script>
		<script type="text/javascript" src="{{ $aceUrl }}" ></script>
		<script type="text/javascript" src="{{ $ckeditorUrl }}" ></script>
		<script type="text/javascript" src="{{ Plugin::get('main')->getJsUrl('ext/require.js') }}" ></script>
		<script type="text/javascript" src="{{ $mainJsUrl }}" id="app-main-script"></script>
	</head>

	<body>
		{{ $body }}
	</body>
</html>