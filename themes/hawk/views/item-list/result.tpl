{if($list->recordNumber)}
    {foreach($data as $id => $line)}
        <tr class="list-line list-line-{{ $list->id }} {{ $linesParameters[$id]['class'] }}" value="{{ $id }}" >
            {if($list->selectableLines)}
                <td>
                    <input type="checkbox" class="list-select-line" value="{{ $id }}" id="{{ $list->id }}-list-select-line-{{ $id}}" />
                    <label for="{{ $list->id }}-list-select-line-{{ $id}}" class="checkbox-icon"></label>
                </td>
            {/if}
            {foreach($line as $name => $cell)}
                {{ $cell }}
            {/foreach}
        </tr>
    {/foreach}
{else}
    <tr><td class="list-no-result" colspan="100%"><center class="text-error"> {{ $list->emptyMessage }} </center></td></tr>
{/if}