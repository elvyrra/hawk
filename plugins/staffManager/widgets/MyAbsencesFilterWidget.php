<?php

namespace Hawk\Plugins\StaffManager;
/**
 * TicketFilterWidget.class.php
 * @author Elvyrra SAS
 */



/**
 * This Widget is used to filter the users list by status or role
 */
class MyAbsencesFilterWidget extends Widget{
	public static $filters = array('status');

	public function getFilters(){

		$result = App::request()->getCookies('staffManager-myAbsences-filter') ? json_decode(App::request()->getCookies('staffManager-myAbsences-filter'), true) : array();
		foreach($result as $name => $values){
			$result[$name] = array_filter($result[$name]);
		}
		
		return($result);
	}


	public function display(){
		$filters = $this->getFilters();

		$form = new Form(array(
			'id' => 'myAbsences-filter-form',			
			'fieldsets' => array(
				'form' => array_map(function($status) use($filters){
					return new CheckboxInput(array(
						'name' => 'status[' . $status . ']',
						'value' => isset($filters['status'][$status]),
						'label' => $status,
						'beforeLabel' => true,
						'labelWidth' => 'auto',
					));
				}, json_decode(Option::get('staffManager.status'), true)),
			)
		));

		return View::make(Theme::getSelected()->getView("box.tpl"), array(
			'content' => $form,
			'title' => Lang::get('staffManager.filter-status-title-legend'),
			'icon' => 'filter',
		));
	}
	
}