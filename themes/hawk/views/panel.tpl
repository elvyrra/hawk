<div class="panel panel-{{ $type }}" id="{{ $id }}">
    <div class="panel-heading">
        <h4 class="panel-title">
            {if(!empty($icon))}
                {icon icon="{$icon}"}
            {/if}
            {{ $title }}
        </h4>
    </div>
    <div class="panel-body">
        {{ $content }}
    </div>
</div>