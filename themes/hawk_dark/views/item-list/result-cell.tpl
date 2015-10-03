<td class="list-cell-{{ $field->list->id }}-{{ $field->name }} {{ $field->onclick || $field->href ? 'list-cell-clickable' : '' }} {{ $field->hidden ? 'list-cell-hidden' : '' }} {{ $cell->class }}"
    {if($cell->title)} title="{{ $cell->title }}" {/if}
    {if($cell->style)} style="{{ $cell->style }}" {/if}
    {if($cell->onclick)} onclick="{{ $cell->onclick }}" {/if}
    {if($cell->href)} href="{{ $cell->href }}" {/if}
    {if($cell->target)} target="{{ $cell->target }}" {/if}>
        {{ $cell->content }}
</td>