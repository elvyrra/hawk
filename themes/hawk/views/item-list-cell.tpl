<td class="list-cell-{{ $field->list->id }}-{{ $field->name }} {{ $field->onclick || $field->href ? 'list-cell-clickable' : '' }} {{ $field->hidden ? 'list-cell-hidden' : '' }} {{ $cell->class }}"
    title="{{ $cell->title }}"
    style="{{ $cell->style }}"
    onclick="{{ $cell->onclick }}"
    href="{{ $cell->href }}"
    target="{{ $cell->target }}" >
        
        {{ $cell->content }}
</td>