<?php
/**
 * Tabs.class.php
 * @author Elvyrra SAS
 */

namespace Hawk\View\Plugins;

/**
 * This class is used to display a tab system in a view
 * @package View\Plugins
 */
class Tabs extends \Hawk\ViewPlugin{
	/**
	 * The 'id' attribute of the accordion
	 * @var string
	 */
	public $id,

	/**
	 * The defaultly selected panel
	 * @var string
	 */
	$selected,

	/**
	 * The tabs set
	 * @var array
	 */
	$tabs = array();

	/**
	 * Display the tabs
	 * @return string The displayed HTML
	 */
	public function display(){
		if(!$this->id){
			$this->id = uniqid();
		}
		foreach($this->tabs as &$tab){
			if(empty($tab['id'])){
				$tab['id'] = uniqid();
			}
		}
		if(!$this->selected){
			$this->selected = array_keys($this->tabs)[0];
		}

		return \Hawk\View::make(\Hawk\Theme::getSelected()->getView('tabs.tpl'), array(
			'id' => $this->id,
			'tabs' => $this->tabs,
			'selected' => $this->selected
		));

	}
}