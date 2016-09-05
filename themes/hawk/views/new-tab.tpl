<div>
    <input type="hidden" class="page-name" value="{text key='main.new-tab-page-name' encoded="true"}" />

    {if($custom)}
        {{ $custom }}
    {else}
        <div class="new-tab-default-content center">
            <!-- Dear developers, if you want to custom the default "new tab" content for your theme, That's here -->
            <h1>{{ Option::get('main.page-title-' . LANGUAGE) ? Option::get('main.page-title-' . LANGUAGE) : DEFAULT_HTML_TITLE }}</h1>
            <img src="{{Option::get('main.logo') ? Plugin::get('main')->getUserfilesUrl(Option::get('main.logo')) : Plugin::get('main')->getStaticUrl('img/hawk-logo.png') }}" class="application-logo"/>
        </div>
    {/if}
</div>