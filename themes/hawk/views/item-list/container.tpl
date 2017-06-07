{assign name="fullList"}
    {import file="{$list->navigationBarTpl}" }
    <div class="clearfix"></div>
    {import file="{$list->listTpl}" }
{/assign}

{if($list->rebuild)}
    {{ $fullList }}
{else}
    <div class="list-wrapper" id='{{ $list->id }}'>
        {{ $fullList }}
    </div>
{/if}
