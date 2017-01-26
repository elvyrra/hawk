{button href="{uri action='manage-plugins'}" label="{text key='admin.search-plugin-back'}" icon="reply"}
<div class="clearfix"></div>
<div class="row container-fluid" id="search-plugins-list">
    <div class="col-md-6 col-lg-4 plugin-item-wrapper" e-each="{$data : plugins, $item : 'plugin'}">
        <div class="plugin-item">
            <div class="plugin-item-header">
                <div class="plugin-logo-container pull-left">
                    <a target="_blank" e-attr="{href : detailsUrl}">
                        <img class="pull-left plugin-logo" e-attr="{src : logo, alt : title}" e-if="logo"/>
                        {icon icon="plug" size="fw" class="pull-left plugin-logo" e-unless="logo"}
                    </a>
                </div>

                <div class="plugin-description-container pull-left">
                    <span class="plugin-title">${title}</span>
                    <p class="plugin-description" e-html="description"></p>
                </div>
            </div>

            <div class="clearfix"></div>
            <div class="plugin-item-footer">
                <span class="plugin-rate" title="${rate}">
                    <i class="icon text-success icon-lg"
                        e-each="{$data : [1,2,3,4,5], $item : 'i'}"
                        e-class="{
                            'icon-star-o' : plugin.rate < i - 0.75,
                            'icon-star-half-o' : plugin.rate >= i - 0.75 && plugin.rate < i - 0.25,
                            'icon-star' : plugin.rate >= i - 0.25
                        }"></i>
                </span>


                <span class="btn btn-success pull-right" e-if="installed">{text key="admin.search-plugin-result-list-installed"}</span>
                {button e-unless="installed"
                        e-click="$root.downloadPlugin(plugin)"
                        label="{text key='admin.download-plugin-button'}"
                        icon="download"
                        class="pull-right download-plugin"
                }

                <div class="clearfix"></div>
                ${$root.downloadsLabel(plugin)}
            </div>
        </div>
    </div>

    <h3 class="text-danger text-center" e-unless="plugins.length">{text key="admin.search-plugin-no-result"}</h3>

    <input type="hidden" name="search-result" value="{{{ $searchResult }}}" />
</div>