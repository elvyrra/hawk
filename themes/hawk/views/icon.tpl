<i class="icon icon-{{ $icon }} {{ $size ? 'icon-' . $size : '' }} {{ $class }}"
    {foreach($param as $key => $value)}
        {if(!empty($value))}
            {{$key}}="{{ addcslashes($value, '"') }}"
        {/if}
    {/foreach}></i>