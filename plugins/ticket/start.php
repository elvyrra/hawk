<?php

namespace Hawk\Plugins\Ticket;

App::router()->setProperties(
    array(
        'namespace' => 'Hawk\Plugins\Ticket',
        'prefix' => '/ticket'
    ),
    function(){
        App::router()->auth(App::session()->isConnected(), function(){
        	// Tickets
            App::router()->get('ticket-index', '/index', array('action' => 'TicketController.index'));

			App::router()->get('ticket-list', '/tickets', array('action' => 'TicketController.getlistTicket'));

			App::router()->any('ticket-editTicket', '/tickets/{ticketId}', array('where' => array('ticketId' => '\d+'), 'action' => 'TicketController.editTicket'));

			App::router()->get('ticket-delete', '/tickets/{ticketId}/remove', array('where' => array('ticketId' => '\-?\d+'), 'action' => 'TicketController.removeTicket'));

			//Comments
			App::router()->get('ticket-history', '/tickets/{ticketId}/history', array('where' => array('ticketId' => '\d+'), 'action' => 'TicketController.history'));

			App::router()->any('ticket-editComment', '/tickets//{ticketId}/comment/{commentId}', array('where' => array('commentId' => '\d+', 'ticketId' => '\d+'), 'action' => 'TicketController.editComment'));

			// Projects
			App::router()->get('ticket-project-index', '/projects', array('action' => 'ProjetController.index'));

			App::router()->get('ticket-project-list', '/projects/list', array('action' => 'ProjetController.getlistProject'));

			App::router()->any('ticket-editProject', '/projects/{projectId}', array('where' => array('projectId' => '\d+'), 'action' => 'ProjetController.editProject'));

			App::router()->get('ticket-project-delete', '/projects/{projectId}/remove', array('where' => array('projectId' => '\-?\d+'), 'action' => 'ProjetController.removeProject'));

			// Settings
			App::router()->get('ticket-settings-display', '/plugin-settings', array('action' => 'TicketController.settings')); 
        });
    }
);