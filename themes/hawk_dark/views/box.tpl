<div class="box">
    {if($title || $icon)}
        <div class="box-header">
            <i class="fa fa-{{ $icon }} box-icon"></i> {{ $title }}
        </div>
    {/if}
    <div class="box-content">
        {{ $content }}
    </div>
</div>