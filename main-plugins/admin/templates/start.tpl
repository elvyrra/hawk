<?php

namespace {{ $namespace }};

App::router()->prefix('/{{ $name }}', function(){
    App::router()->get(
        '{{ $name }}-index',
        '',
        array(
            'action' => 'BaseController.index'
        )
    );
});