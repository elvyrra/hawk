<button class="btn {{ $class }} {{ preg_match('/\bbtn\-/', $class) ? '' : 'btn-default' }}"
    {foreach($param as $key => $value)}
        {if(!empty($value))}
            {{$key}}="{{ htmlentities($value, ENT_COMPAT) }}"
        {/if}
    {/foreach}
    {if(empty($param['title']) && !empty($param['label']))}
        title="{{ htmlentities($param['label'], ENT_COMPAT) }}"
    {/if} >
    {if($icon)}
        {icon icon="{$icon}"}
    {/if}
    <span class="btn-label">{{ $label }}</span>
</button>