<div class="plugin-details-actions col-md-3 col-lg-2">
    {foreach($buttons as $button)}
        <div>{{ $button }}</div>
    {/foreach}

    <div class="plugin-description">
        {if($plugin->getLogoUrl())}
            <img src="{{ $plugin->getLogoUrl() }}" class="plugin-logo"/>
        {else}
            {icon icon="plug" class="plugin-logo" size="5x"}
        {/if}
    </div>
</div>