<div class="plugin-controls">
    <div class="plugin-definition">
        <a href="{uri action='plugin-details' plugin='{$plugin->getName()}'}">
            {if($plugin->getLogoUrl())}
                <img src="{{ $plugin->getLogoUrl() }}" class="plugin-logo"/>
            {else}
                {icon icon="plug" class="plugin-logo" size="5x"}
            {/if}
            <span class="plugin-name"> {{ $plugin->getDefinition("title") }} ({{ $status }}) </span>
        </a>
    </div>

    <div class="plugin-actions pull-left">
        {foreach($buttons as $button)}
            {{ $button }}
        {/foreach}
    </div>
</div>
