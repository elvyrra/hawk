<html>
    <head>      
        <link rel="stylesheet" id="theme-base-stylesheet" href="{{ $themeBaseCss }}" />
        <link rel="stylesheet" id="theme-custom-stylesheet" href="{{ $themeCustomCss }}" />
    </head>

    <body>
        <div class="header">
            <img src="{{ $logoUrl }}" class="application-logo pull-left" alt="Application logo" />
        </div>
        <h1>{text key="main.reset-pwd-email-title" sitename="{$sitename}"}</h1>

        <div class="container">
            <p>{text key="main.reset-pwd-email-content-into" sitename="{$sitename}"}.</p>

            <p>{text key="main.reset-pwd-email-content-fill-code"}</p>

            <b>{{ $code }}</b>

            <p>{text key="main.reset-pwd-email-thanks" sitename="{$sitename}"}</p>            
        </div>
    </body>
</html>