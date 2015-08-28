<div role="tabpanel" id="{{ $id }}">
    <ul class="nav nav-tabs" role="tablist">
        {foreach($tabs as $i => $tab)}
            <li role="presentation" {if($i == $selected)}class="active"{/if}><a href="#{{ $tab['id'] }}" aria-controls="{{ $tab['id'] }}" role="tab" data-toggle="tab">{{ $tab['title'] }}</a></li>
        {/foreach}
    </ul>

    <div class="tab-content">
        {foreach($tabs as $i => $tab)}
            <div role="tabpanel" class="tab-pane {if($i == $selected)}active{/if}" id="{{ $tab['id'] }}">
                {{ $tab['content'] }}
            </div>
        {/foreach}
    </div>
</div>