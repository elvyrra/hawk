<?php

namespace Hawk\Plugins\Ticket;
/**
 * TicketFilterWidget.class.php
 * @author Elvyrra SAS
 */



/**
 * This Widget is used to filter the users list by status or role
 */
class TicketFilterWidget extends Widget{
	public static $filters = array('status');

	public function getFilters(){

		//$result = !empty($_COOKIE['ticket-filter']) ? json_decode($_COOKIE['ticket-filter'], true) : array();

		foreach(self::$filters as $name){
			if(isset($_GET[$name])){
				$result[$name] = $_GET[$name];
			}

			if(empty($result[$name])){
				$result[$name] = 0;
			}
		}
		//setcookie('ticket-filter', json_encode($result));
		
		return $result;
	}


	public function display(){

		$result = TicketOption::getByExample(new DBExample(array('plugin' => 'ticket')));

		$form = new Form(array(
			'id' => 'ticket-filter-form',
			'model' => '\Hawk\Plugins\Ticket\Ticket',
			'fieldsets' => array(
				'form' => array(
					new SelectInput(array(
						'name' => 'status',
						'options' => $options,
						'value' => $filters['status'],
						'label' => Lang::get('ticket.filter-status-label')
					)),		
				)
			)

		));

		return View::make(Theme::getSelected()->getView("box.tpl"), array(
			'content' => $form,
			'title' => Lang::get('ticket.filter-title-legend'),
			'icon' => 'filter',
		));
	}
	
}