{button href="{uri action='manage-themes'}" label="{text key='admin.search-theme-back'}" icon="reply"}
<div class="clearfix"></div>
<div class="row container-fluid">
    {if(count($list->results))}
        {foreach($list->results as $id => $theme)}
            <div class="col-md-6 col-lg-4 theme-item-wrapper">
                <div class="theme-item">
                    <div class="theme-item-header">
                        <div class="theme-logo-container pull-left">
                            <a href="{{ $theme->detailsUrl }}" target="_blank">
                                {if($theme->logo)}
                                    <img src="{{ $theme->logo }}" alt="{{ $theme->title }}" class="pull-left theme-logo" />
                                {else}
                                    {icon icon="plug" class="pull-left theme-logo" size="fw"}
                                {/if}
                            </a>
                        </div>

                        <div class="theme-description-container pull-left">
                            <span class="theme-title">{{ $theme->title }}</span>
                            <p class="theme-description">{{ $theme->description }}</p>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="theme-item-footer">
                        <span class="theme-rate" title="{{ $theme->rate }}">
                            {foreach(range(1, 5) as $i)}
                                {icon icon="{ $theme->rate < $i - 1 + 0.25 ? 'star-o' : ($theme->rate < $i - 1 + 0.75 ? 'star-half-o' : 'star') }" size="lg" class="text-success"}
                            {/foreach}
                        </span>

                        {if($theme->installed)}
                            {if(Utils::getSerializedVersion($theme->availableVersion) > Utils::getSerializedVersion($theme->currentVersion))}
                                {button label="{text key='admin.update-theme-button'}" icon="refresh" class="pull-right update-theme btn-warning" data-theme="{$theme->name}"}
                            {else}
                                <span class="btn btn-success pull-right">{text key="admin.search-theme-result-list-installed"}</span>
                            {/if}
                        {else}
                            {button label="{text key='admin.download-theme-button'}" icon="download" href="{uri action='download-theme' theme='{$theme->name}'}" class="pull-right download-theme"}
                        {/if}
                        <div class="clearfix"></div>
                        {text key="admin.search-theme-downloads" downloads="{$theme->downloads}"}
                    </div>
                </div>
            </div>
        {/foreach}
    {else}
        <h3 class="text-danger text-center">{text key="admin.search-theme-no-result"}</h3>
    {/if}
</div>