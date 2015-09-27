<div class="panel-group" id="{{ $id }}" role="tablist" aria-multiselectable="true">
    {foreach($panels as $i => $panel)}
        <div class="panel panel-{{ $panel['type'] ? $panel['type'] : 'default' }}">
            <div class="panel-heading" role="tab" id="{{ $headingId = uniqid() }}">
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#{{$id}}" href="#{{$panel['id']}}" aria-controls="{{$panel['id']}}">
                        {{$panel['title']}}
                        <i class="fa fa-caret-down pull-right"></i>
                    </a>
                </h4>
            </div>
            <div id="{{$panel['id']}}" class="panel-collapse collapse {{ $selected == $i ? 'in' :''}}" role="tabpanel" aria-labelledby="{{$headingId}}">
                <div class="panel-body">
                    {{ $panel['content'] }}
                </div>
            </div>  
        </div>
    {/foreach}
</div>