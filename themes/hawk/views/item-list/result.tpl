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
        if(app.lists["{{ $list->id }}"]){
            app.lists["{{ $list->id }}"].selected = {{ $list->selected !== false ? "'$list->selected'" : "null" }};
            app.lists["{{ $list->id }}"].maxPages({{ $pages }});
        }
    });
</script>
