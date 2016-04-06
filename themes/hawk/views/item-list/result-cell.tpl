<td class="list-cell list-cell-{{ $field->list->id }}-{{ $field->name }} {{ $field->onclick || $field->href ? 'list-cell-clickable' : '' }} {{ $field->hidden ? 'list-cell-hidden' : '' }} {{ $cell->class }}"
    data-field="{{ $field->name }}"
    {if($cell->title)} title="{{ htmlentities($cell->title, ENT_QUOTES) }}" {/if}
    {if($cell->style)} style="{{ $cell->style }}" {/if}
    {if($cell->onclick)} onclick="{{ htmlentities($cell->onclick, ENT_QUOTES) }}" {/if}
    {if($cell->href)} href="{{ $cell->href }}" {/if}
    {if($cell->target)} target="{{ $cell->target }}" {/if}>
        {{ $cell->content }}
</td>