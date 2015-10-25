{if($list->recordNumber)}
    {foreach($data as $id => $line)}
        <tr class="list-line list-line-{{ $list->id }} {{ $linesParameters[$id]['class'] }}" value="{{ $id }}" >                    
            {foreach($line as $name => $cell)}
                {{ $cell }}                     
            {/foreach}
        </tr>
    {/foreach}
{else}
    <tr><td class="list-no-result" colspan="100%"><center class="text-error"> {{ $list->emptyMessage }} </center></td></tr>
{/if}

<script type="text/javascript">
    app.ready(function(){
        if(list = app.lists["{{ $list->id }}"]){
            list.selected = {{ $list->selected !== false ? "'$list->selected'" : "null" }};
            list.maxPages({{ $pages }});
            list.recordNumber({{ $list->recordNumber }});
        }
    });
</script>
