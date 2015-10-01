<html>
	<head>
		<link rel="stylesheet" href="{{ $mainCssUrl }}bootstrap.min.css" />
		<link rel="stylesheet" id="theme-base-stylesheet" href="{{ $themeBaseCss }}" />
		<link rel="stylesheet" id="theme-custom-stylesheet" href="{{ $themeCustomCss }}" />
	</head>

	<body>
		<div class="header">
			<img src="{{ $logoUrl }}" class="application-logo pull-left" alt="Application logo" />
		</div>
		<h1>{text key="main.register-email-title" sitename="{$sitename}"}</h1>

		<div class="content">
			{text key="main.register-email-content" url="{$url}" sitename="{$sitename}"}
		</div>
	</body>
</html>