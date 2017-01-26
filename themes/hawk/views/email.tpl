<!DOCTYPE html>
<html lang="{{ LANGUAGE }}">
    <head>
        <meta chartset="utf8" />
    </head>
    <body style="width:100%; font-size: {{ $css['font-size-base'] }}">
        <div style="width:100%; border: none; height: 6rem;margin-bottom: .5rem; background: {{ $css['header-bg'] }}; color: {{ $css['header-color'] }};position:relative">
            <a href="{{ App::router()->getUrl('index') }}" style="float: left">
                <img style="height:4rem;width:auto;position:absolute;top:50%; transform: translateY(-50%);"
                    src="{{ $logoUrl }} " alt="Application logo"/>
            </a>
        </div>

        <div style="width:98%; max-width: 1200px;margin-left: auto;margin-right: auto;padding: 1rem;">

            <h1 style="text-align: center;font-size:1.6rem;margin-bottom:.5rem;color: {{ $css['state-info-text'] }};font-weight: normal"> {{ $title }} </h1>

            <div style="font-size:1.2rem;">
                {{ $content }}
            </div>
        </div>
    </body>
</html>