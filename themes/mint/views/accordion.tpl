<div class="panel-group" id="{{ $id }}" role="tablist" aria-multiselectable="true">
    {foreach($panels as $panel)}
        <div class="panel panel-{{ $panel['type'] ? $panel['type'] : 'default' }}">
            <div class="panel-heading" role="tab" >
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#{{$id}}" href="#{{$panel['id']}}" aria-expanded="true" aria-controls="{{$panel['id']}}">
                        {{$panel['title']}}
                    </a>
                </h4>
            </div>
            <div id="{{$panel['id']}}" class="panel-collapse collapse in" role="tabpanel">
                <div class="panel-body">
                    {{ $panel['content'] }}
                </div>
            </div>  
        </div>
    {/foreach}
</div>