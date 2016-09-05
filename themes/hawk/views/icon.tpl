<i class="icon icon-{{ $icon }} {{ $size ? 'icon-' . $size : '' }} {{ $class }}"
    {foreach($param as $key => $value)}
        {if(!empty($value))}
            {{$key}}="{{{ $value }}}"
        {/if}
    {/foreach}></i>