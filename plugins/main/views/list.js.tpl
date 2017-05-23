<script type="text/javascript">
    require(['app', 'list'], function(app, List) {
        var list = new List({
            id              : '{{ $list->id }}',
            action          : '{{ $list->action }}',
            target          : '{{ $list->target }}',
            fields          : {{ json_encode(array_keys($list->fields)) }},
            userParam       : {{ json_encode($list->userParam, JSON_FORCE_OBJECT) }},
            htmlResult      : '{{ $htmlResult }}',
            maxPages        : {{ $maxPages }},
            recordNumber    : {{ $list->recordNumber }}
        });

        app.lists['{{ $list->id }}'] = list;
    });
</script>