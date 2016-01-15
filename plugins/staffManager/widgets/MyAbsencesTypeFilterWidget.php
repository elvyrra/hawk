<?php

namespace Hawk\Plugins\StaffManager;
/**
 * TicketFilterWidget.class.php
 * @author Elvyrra SAS
 */



/**
 * This Widget is used to filter the users list by status or role
 */
class MyAbsencesTypeFilterWidget extends Widget{
	public static $filters = array('type');

	public function getFilters(){

		$result = App::request()->getCookies('staffManager-myAbsences-type-filter') ? json_decode(App::request()->getCookies('staffManager-myAbsences-type-filter'), true) : array();
		foreach($result as $name => $values){
			$result[$name] = array_filter($result[$name]);
		}
		
		return($result);
	}


	public function display(){
		$filters = $this->getFilters();

		$form = new Form(array(
			'id' => 'myAbsences-filter-type-form',			
			'fieldsets' => array(
				'form' => array_map(function($type) use($filters){
					return new CheckboxInput(array(
						'name' => 'type[' . $type . ']',
						'value' => isset($filters['type'][$type]),
						'label' => $type,
						'beforeLabel' => true,
						'labelWidth' => 'auto',
					));
				}, json_decode(Option::get('staffManager.typeAbsence'), true)),
			)
		));

		return View::make(Theme::getSelected()->getView("box.tpl"), array(
			'content' => $form,
			'title' => Lang::get('staffManager.filter-type-title-legend'),
			'icon' => 'filter',
		));
	}
	
}