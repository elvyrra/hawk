<?php

namespace Hawk\Plugins\Ticket;

Router::setProperties(
    array(
        'namespace' => 'Hawk\Plugins\Ticket',
        'prefix' => '/ticket'
    ),
    function(){
        Router::auth(Session::isConnected(), function(){
        	// Tickets
            Router::get('ticket-index', '/index', array('action' => 'TicketController.index'));

			Router::get('ticket-list', '/tickets', array('action' => 'TicketController.getlistTicket'));

			Router::any('ticket-editTicket', '/tickets/{ticketId}', array('where' => array('ticketId' => '\d+'), 'action' => 'TicketController.editTicket'));

			Router::get('ticket-delete', '/tickets/{ticketId}/remove', array('where' => array('ticketId' => '\-?\d+'), 'action' => 'TicketController.removeTicket'));

			//Comments
			Router::get('ticket-history', '/tickets/{ticketId}/history', array('where' => array('ticketId' => '\d+'), 'action' => 'TicketController.history'));

			Router::any('ticket-editComment', '/tickets//{ticketId}/comment/{commentId}', array('where' => array('commentId' => '\d+', 'ticketId' => '\d+'), 'action' => 'TicketController.editComment'));

			// Projects
			Router::get('ticket-project-index', '/projects', array('action' => 'ProjetController.index'));

			Router::get('ticket-project-list', '/projects/list', array('action' => 'ProjetController.getlistProject'));

			Router::any('ticket-editProject', '/projects/{projectId}', array('where' => array('projectId' => '\d+'), 'action' => 'ProjetController.editProject'));

			Router::get('ticket-project-delete', '/projects/{projectId]/remove', array('where' => array('projectId' => '\-?\d+'), 'action' => 'ProjetController.removeProject'));

			// Settings
			Router::get('ticket-settings-display', '/plugin-settings', array('action' => 'TicketController.settings')); 
        });
    }
);