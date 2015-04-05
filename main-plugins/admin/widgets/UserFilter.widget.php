<?php

class UserFilterWidget extends Widget{
	public function getFilter(){
		if(isset($_GET['user_filter_display'])){
			$display = $_GET['user_filter_display'];
			setcookie('user_filter_display', $display);
		}
		elseif(isset($_COOKIE['user_filter_display'])){
			$display = $_COOKIE['user_filter_display'];
		}
		else{
			$display = 'all';
		}

		return $display;
	}

	public function display(){
		$form = new Form(array(
			'id' => 'user-filter-form',
			'fieldsets' => array(
				'form' => array(
					new RadioInput(array(
						'name' => 'display',
						'labelWidth' => 'auto',
						'layout' => 'vertical',
						'value' => $this->getFilter(),
						'options' => array(
							'all' => Lang::get('admin.user-filter-display-all'),
							'active' => Lang::get('admin.user-filter-display-active'),
							'inactive' => Lang::get('admin.user-filter-display-inactive')
						),
					))
				)
			)

		));

		return View::make(ThemeManager::getSelected()->getView("box.tpl"), array(
			'content' => $form,
			'title' => Lang::get('admin.user-filter-display-label'),
			'icon' => 'filter',
		));
	}
	
}