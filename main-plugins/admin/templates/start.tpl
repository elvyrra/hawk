<?php

namespace {{ $namespace }};

App::router()->setProperties(
    array(
        'namespace' => __NAMESPACE__,
        'prefix' => '/{{ $name }}'
    ),
    function(){
        /*** DEFINE HERE THE ROUTES AND EVENT LISTENERS OF YOUR PLUGIN ***/
    }
);