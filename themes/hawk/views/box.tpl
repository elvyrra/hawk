<div class="box">
    {if($title || $icon)}
        <div class="box-header corner-top">
            {icon icon="{$icon}" class="box-icon"} {{ $title }}
        </div>
    {/if}
    <div class="box-content">
        {{ $content }}
    </div>
</div>