<?php

namespace Hawk\Plugins\StockManager;

App::router()->setProperties(
    array(
        'namespace' => __NAMESPACE__,
        'prefix' => 'stockManager'
    ),
    function(){
        /*** DEFINE HERE THE ROUTES AND EVENT LISTENERS OF YOUR PLUGIN ***/
    }
);