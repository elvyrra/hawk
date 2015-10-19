<div class="panel panel-{{ $type }}" id="{{ $id }}">
    <div class="panel-heading">
        <h4 class="panel-title">
            <i class="icon icon-{{ !empty($icon) ? $icon : '' }}"></i>
            {{ $title }}            
        </h4>
    </div>
    <div class="panel-body">
        {{ $content }}
    </div>  
</div>