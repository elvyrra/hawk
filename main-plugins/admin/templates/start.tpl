<?php

namespace {{ $namespace }};

Router::setProperties(
    array(
        'namespace' => '{{ $namespace }}',
        'prefix' => '{{ $name }}'
    ),
    function(){
        /*** DEFINE HERE THE ROUTES AND EVENT LISTENERS OF YOUR PLUGIN ***/
    }
);