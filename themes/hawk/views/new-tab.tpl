<div>
    <input type="hidden" class="page-name" value="{{ htmlentities(Lang::get('main.new-tab-page-name'), ENT_QUOTES) }}" />

    {if($custom)}
        {{ $custom }}
    {else}
        <div class="new-tab-default-content">
            <!-- Dear developers, if you want to custom the default "new tab" content for your theme, That's here -->
            <h1>{{ Option::get('main.page-title-' . LANGUAGE) ? Option::get('main.page-title-' . LANGUAGE) : DEFAULT_HTML_TITLE }}</h1>
            {if(Option::get('main.logo'))}
                <img src="{{ Plugin::get('main')->getUserfilesUrl(Option::get('main.logo')) }}" />
            {/if}        
        </div>
    {/if}
</div>