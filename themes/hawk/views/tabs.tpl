<div role="tabpanel" id="{{ $id }}">
    <ul class="nav nav-tabs" role="tablist">
        {foreach($tabs as $tab)}
            <li role="presentation"><a href="#{{ $tab['id'] }}" aria-controls="{{ $tab['id'] }}" role="tab" data-toggle="tab">{{ $tab['title'] }}</a></li>
        {/foreach}
    </ul>

    <div class="tab-content">
        {foreach($tabs as $tab)}
            <div role="tabpanel" class="tab-pane" id="{{ $tab['id'] }}">
                {{ $tab['content'] }}
            </div>
        {/foreach}
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#{{ $id }} .nav-tabs a:nth-child({{ $selected }})').tab('show');
    });
</script>