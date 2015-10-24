<div class="row">
    <div class="col-xs-12">
        {if($plugin->logo)}
            <img src="{{ $plugin->logo }}" alt="{{ $plugin->title }}" class="pull-left plugin-logo"/>
        {else}
            <span class="pull-left plugin-logo icon icon-plug icon-fw"></span>
        {/if}

        <h4 class="pull-left">{{ $plugin->title }}</h4>
    </div>
</div>

{if($plugin->installed)}
	<span class="btn btn-success">{text key="admin.search-plugin-result-list-installed"}</span>
{else}
	{button label="{Lang::get('admin.download-plugin-button')}" icon="download" href="{Router::getUri('download-plugin', array('plugin' => $plugin->name))}" }

	{if($plugin->price)}
		{button label="{Lang::get('admin.buy-plugin-button')}" icon="shopping-cart" class="btn-info" href="{$plugin->detailsUrl}" target="_blank"}                                    
	{/if}
{/if}
