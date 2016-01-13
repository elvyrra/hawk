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

		$result = App::request()->getCookies('ticket-filter') ? json_decode(App::request()->getCookies('ticket-filter'), true) : array();
		foreach($result as $name => $values){
			$result[$name] = array_filter($result[$name]);
		}
		
		return($result);
	}


	public function display(){
		$filters = $this->getFilters();

		$form = new Form(array(
			'id' => 'ticket-filter-form',			
			'fieldsets' => array(
				'form' => array_map(function($status) use($filters){
					return new CheckboxInput(array(
						'name' => 'status[' . $status . ']',
						'value' => isset($filters['status'][$status]),
						'label' => $status,
						'beforeLabel' => true,
						'labelWidth' => 'auto',
					));
				}, json_decode(Option::get('ticket.status'), true))
			)
		));

		return View::make(Theme::getSelected()->getView("box.tpl"), array(
			'content' => $form,
			'title' => Lang::get('ticket.filter-title-legend'),
			'icon' => 'filter',
		));
	}
	
}