<?php
/**
 * start.php
 *
 * This file is launched for each request. It initialize the plugin routes and event listeners
 */

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