<div class="clearfix"></div>
<div class="row container-fluid">
    {if(count($list->results))}
        {foreach($list->results as $id => $plugin)}
            <div class="col-md-6 col-lg-4 plugin-item-wrapper">
                <div class="plugin-item">
                    <div class="plugin-item-header">
                        <div class="plugin-logo-container pull-left">
                            <a href="{{ $plugin->detailsUrl }}" target="_blank">
                                {if($plugin->logo)}
                                    <img src="{{ $plugin->logo }}" alt="{{ $plugin->title }}" class="pull-left plugin-logo" />
                                {else}
                                    <span class="pull-left plugin-logo icon icon-plug icon-fw"></span>
                                {/if}
                            </a>
                        </div>

                        <div class="plugin-description-container pull-left">
                            <span class="plugin-title">{{ $plugin->title }}</span>
                            <p class="plugin-description">{{ $plugin->description }}</p>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="plugin-item-footer">
                        <span class="plugin-rate" title="{{ $plugin->rate }}">
                            {foreach(range(1, 5) as $i)}
                                <i class="icon icon-lg icon-{{ $plugin->rate < $i - 1 + 0.25 ? 'star-o' : ($plugin->rate < $i - 1 + 0.75 ? 'star-half-o' : 'star') }} text-success"></i>
                            {/foreach}
                        </span>

                        {if($plugin->installed)}
                            {if(Utils::getSerializedVersion($plugin->availableVersion) > Utils::getSerializedVersion($plugin->currentVersion))}
                                {button label="{text key='admin.update-plugin-button'}" icon="refresh" href="{uri action='update-plugin' plugin='{$plugin->name}'}" class="pull-right update-plugin btn-warning"}
                            {else}
                                <span class="btn btn-success pull-right">{text key="admin.search-plugin-result-list-installed"}</span>
                            {/if}
                        {else}
                            {button label="{text key='admin.download-plugin-button'}" icon="download" href="{uri action='download-plugin' plugin='{$plugin->name}'}" class="pull-right download-plugin"}
                        {/if}
                        <div class="clearfix"></div>
                        {text key="admin.search-plugin-downloads" downloads="{$plugin->downloads}"}
                    </div>
                </div>
            </div>
        {/foreach}
    {else}
        <h3 class="text-danger text-center">{text key="admin.search-plugin-no-result"}</h3>
    {/if}
</div>