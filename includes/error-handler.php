<?php
namespace Hawk;

if(ini_get('display_errors')) {
    $errorHandler = App::errorHandler();

    set_error_handler(array($errorHandler, 'error'), error_reporting());
    register_shutdown_function(array($errorHandler, 'fatalError'));

    set_exception_handler(array($errorHandler, 'exception'));
}

