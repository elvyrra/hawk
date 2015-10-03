<div class="box">
    {if($title || $icon)}
        <div class="box-header corner-top">
            <i class="icon icon-{{ $icon }} box-icon"></i> {{ $title }}
        </div>
    {/if}
    <div class="box-content">
        {{ $content }}
    </div>
</div>