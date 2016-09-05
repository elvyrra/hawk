<button class="btn {{ $class }} {{ preg_match('/\bbtn\-/', $class) ? '' : 'btn-default' }}"
    {foreach($param as $key => $value)}
        {if(!empty($value))}
            {{$key}}="{{{ $value }}}"
        {/if}
    {/foreach}
    {if(empty($param['title']) && !empty($param['label']))}
        title="{{{ $param['label'] }}}"
    {/if} >
    {if($icon)}
        {icon icon="{$icon}"}
    {/if}
    <span class="btn-label">{{ $label }}</span>
</button>