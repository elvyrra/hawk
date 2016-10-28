<!DOCTYPE html>
<html lang="{{ LANGUAGE }}">
    <head>
        <meta charset="utf-8" />
        <title> {{ $title }} </title>
        <link rel="stylesheet" type="text/css" href="{{ Theme::getSelected()->getBaseCssUrl() }}" />
        <link rel="stylesheet" type="text/css" href="{{ Theme::getSelected()->getCustomCssUrl() }}" />
    </head>
    <body id="pdf-page">
        <div id="pdf-header">
            <img class="application-logo pull-left" src="{{ Plugin::get('main')->getUserfilesUrl(Option::get('main.logo')) }}" alt="Application logo" height="60"/>
            <h1> {{ $title }} </h1>
        </div>

        <div id="pdf-content">
            {{ $content }}
        </div>

        <div id="pdf-footer">

        </div>
    </body>
</html>