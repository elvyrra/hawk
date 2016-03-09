{button href="{uri action='manage-plugins'}" label="{text key='admin.search-plugin-back'}" icon="reply"}
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
                                    {icon icon="plug" size="fw" class="pull-left plugin-logo"}
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
                                {icon icon="{$plugin->rate < $i - 1 + 0.25 ? 'star-o' : ($plugin->rate < $i - 1 + 0.75 ? 'star-half-o' : 'star')}" class="text-success" size="lg"}
                            {/foreach}
                        </span>

                        {if($plugin->installed)}
                            <span class="btn btn-success pull-right">{text key="admin.search-plugin-result-list-installed"}</span>
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