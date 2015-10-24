<div class="plugin-details">
    <p>
        {{ $plugin->description }}

        {if($plugin->price)}
            <span class="pull-right btn btn-info">{{ number_format($plugin->price, 2, ',', ' ') }} €</span>
        {else}
            <span class="pull-right btn btn-success">{text key="admin.search-plugin-result-free"}</span>
        {/if}
    </p>

    <span class="meta-data">    
        <span class="plugin-rate" title="{{ $plugin->rate }}">
            {foreach(range(1, 5) as $i)}
                <i class="icon icon-lg icon-{{ $plugin->rate < $i - 1 + 0.25 ? 'star-o' : ($plugin->rate < $i - 1 + 0.75 ? 'star-half-o' : 'star') }} text-success"></i>
            {/foreach}
        </span>
        {text key="admin.search-plugin-result-meta-data-version" version="{$plugin->currentVersion}"}

        {if($plugin->author)}
            | {text key="admin.search-plugin-result-meta-data-author" author="{$plugin->author}"}
        {/if}

        {if($plugin->detailsUrl)}
            | {text key="admin.search-plugin-result-meta-data-details" detailsUrl="{$plugin->detailsUrl}"}
        {/if}
    </span>
</div>
