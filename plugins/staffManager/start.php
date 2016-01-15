<?php

namespace Hawk\Plugins\StaffManager;

App::router()->setProperties(
    array(
        'namespace' => __NAMESPACE__,
        'prefix' => '/staffManager'
    ),
    function(){
    	App::router()->auth(App::session()->isConnected(), function(){
       		App::router()->get('staffManager-index', '/index', array('action' => 'StaffManagerController.index'));

       		App::router()->get('staffManager-my-absence', '/myAbsence', array('action' => 'StaffManagerController.myAbsences'));

          App::router()->get('staffManager-get-my-absence', '/myAbsence-', array('action' => 'StaffManagerController.getListMyAbsences'));

       		App::router()->get('staffManager-calendar-absence', '/team-calendar', array('action' => 'StaffManagerController.getTeamCalendar'));

       		App::router()->any('staffManager-new-absence', '/new', array('action' => 'StaffManagerController.add'));

       		// Ajout admin
       		App::router()->get('staffManager-team-absence-manager', '/tean/manager', array('action' => 'StaffManagerController.teamManager'));
       	});
    }
);
