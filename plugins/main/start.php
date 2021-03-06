<?php
/**
 * Initialise the plugin main
 *
 * @author  Elvyrra SAS
 * @license http://rem.mit-license.org/ MIT
 */
namespace Hawk\Plugins\Main;

App::router()->get('index', '/', array('action' => 'MainController.main'));
App::router()->get('new-tab', '/newtab', array('action' => 'MainController.newTab'));

/**
 * Pages available for logged in users
 */
App::router()->auth(App::session()->isLogged(), function () {
    // Edit the logged user's profile
    App::router()->any('edit-profile', '/profile/edit/{userId}', array(
        'where' => array(
            'userId' => '\d+'
        ),
        'default' => array(
            'userId' => App::session()->getUser()->id
        ),
        'action' => 'UserProfileController.edit',
        'auth' => function ($route) {
            return !$route->getData('userId') || $route->getData('userId') == App::session()->getUser()->id;
        }
    ));

    // Change the password
    App::router()->any('change-password', '/profile/change-password', array(
        'action' => 'UserProfileController.changePassword'
    ));

    // Logout
    App::router()->get('logout', '/logout', array('action' => 'LoginController.logout'));
});


// Hack to open login form by javascript when a session expired. The route must be accessible from anyone
App::router()->get('login-form', '/login', array(
    'action' => 'LoginController.login'
));

/**
 * The pages available only if not logged
 */
App::router()->auth(!App::session()->isLogged(), function () {
    //Login
    App::router()->define('login', '/login', array(
        'methods' => array(
            'get',
            'post'
        ),
        'action' => 'LoginController.login'
    ));

    // Register
    App::router()->auth(Option::get('main.open-register'), function () {
        App::router()->any('register', '/register', array('action' => 'RegisterController.register'));


        App::router()->get('validate-registration', '/validate-registration/{token}', array(
            'where' => array(
                'token' => '[^\s]+'
            ),
            'action' => 'RegisterController.validateRegistration'
        ));
    });

    // Validate the registration that has been done by an admin
    App::router()->define('validate-third-registration', '/validate-admin-registration/{token}', array(
        'methods' => array('get', 'post'),
        'where' => array(
            'token' => '[^\s]+'
        ),
        'action' => 'RegisterController.validateAdminRegistration'
    ));

    // Ask for a new password
    App::router()->any('forgotten-password', '/forgotten-password', array('action' => 'LoginController.forgottenPassword'));

    // Reset the forgotten password
    App::router()->any('reset-password', '/reset-password', array('action' => 'LoginController.resetPassword'));
});

// Validate of the new email address, that has been modified in the profile edition page
App::router()->get('validate-new-email', '/profile/change-email/{token}', array(
    'where' => array(
        'token' => '[\w\=]+'
    ),
    'action' => 'UserProfileController.validateNewEmail'
));

// The terms of service
App::router()->get('terms', '/terms-of-application', array('action' => 'MainController.terms'));

// Reload the menu
App::router()->get('refresh-menu', '/main-menu', array('action' => 'MainController.getMainMenu'));

// Load the JavaScript configuration
App::router()->get('js-conf', '/conf.js', array('action' => 'MainController.jsConf'));

// Clear the cache
App::router()->auth(DEV_MODE, function () {
    App::router()->get('clear-cache', '/clear-cache', array('action' => 'MainController.clearCache'));
});


// Customize a list
App::router()->any('customize-list', '/customize-list/{id}', array(
    'where' => array(
        'id' => '[\w\-]+'
    ),
    'action' => 'CustomListController.customize'
));


// Redirect to index when the registration is OK
App::getInstance()->on('main.LoginController.validateRegister.after', function(\Hawk\Event $event) {
    $result = $event->getData('result');

    App::session()->setData('notification', $result);

    App::response()->redirectToRoute('index');
});

