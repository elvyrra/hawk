<?php

namespace Hawk\Plugins\EShop;

App::router()->setProperties(
    array(
        'namespace' => __NAMESPACE__,
        'prefix' => 'eShop'
    ),
    function(){
        /*** DEFINE HERE THE ROUTES AND EVENT LISTENERS OF YOUR PLUGIN ***/
    }
);