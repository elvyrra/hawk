<script type="text/javascript">
require(["app"], function(){
{if($list->isRefreshing())}
    if(list = app.lists['{{ $list->id }}']){
        list.maxPages({{ $pages }});
        list.recordNumber({{ $list->recordNumber }});
    }
{else}
    var list = new List({
        id              : '{{ $list->id }}',
        action          : '{{ $list->action }}',
        target          : '{{ $list->target }}',
        fields          : {{ json_encode(array_keys($list->fields)) }},
        userParam       : {{ json_encode($list->userParam, JSON_FORCE_OBJECT) }}
    });

    list.maxPages({{ $pages }});
    list.recordNumber({{ $list->recordNumber }});

    app.lists['{{ $list->id }}'] = list;
{/if}
});
</script>